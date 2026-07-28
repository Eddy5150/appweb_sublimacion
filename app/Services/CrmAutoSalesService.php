<?php

namespace App\Services;

use App\Models\CrmActionsQueue;
use App\Models\CrmAiMessage;
use App\Models\CrmAiPrediction;
use Illuminate\Support\Collection;

class CrmAutoSalesService
{
    public function __construct(
        private CrmEventLogger $logger,
        private CrmAiAgentService $agent,
    ) {}

    public function queueFromAgent(CrmAiMessage $message, ?string $scheduledAt = null): CrmActionsQueue
    {
        $actionType = $this->resolveActionType($message->accion_recomendada);

        return CrmActionsQueue::create([
            'cliente_id' => $message->cliente_id,
            'crm_ai_message_id' => $message->id,
            'action_type' => $actionType,
            'payload' => [
                'mensaje' => $message->mensaje,
                'accion_recomendada' => $message->accion_recomendada,
                'tipo_mensaje' => $message->tipo,
                'contexto' => $message->contexto,
            ],
            'status' => 'pending',
            'scheduled_at' => $scheduledAt,
        ]);
    }

    public function processAllFromAgent(): int
    {
        $contador = 0;

        CrmAiMessage::where('estado', 'pending')
            ->where('mensaje', '!=', '')
            ->chunk(50, function (Collection $messages) use (&$contador) {
                foreach ($messages as $message) {
                    $this->queueFromAgent($message);
                    $contador++;
                }
            });

        return $contador;
    }

    public function processAllTriggers(): int
    {
        $contador = 0;

        CrmAiPrediction::with('cliente')
            ->whereHas('cliente')
            ->chunk(100, function (Collection $predictions) use (&$contador) {
                foreach ($predictions as $pred) {
                    $action = $this->evaluateTrigger($pred);
                    if ($action) {
                        $contador++;
                    }
                }
            });

        return $contador;
    }

    public function evaluateTrigger(CrmAiPrediction $pred): ?CrmActionsQueue
    {
        $cliente = $pred->cliente;
        if (! $cliente) {
            return null;
        }

        $score = $pred->ai_score ?? 0;
        $ltv = (float) ($pred->ltv_estimated ?? 0);
        $churn = $pred->churn_risk ?? 'baja';
        $emocional = $pred->emotional_state ?? 'neutro';
        $recency = $cliente->dias_ultima_compra ?? 999;

        if ($ltv >= 1000 && $recency >= 15) {
            return CrmActionsQueue::create([
                'cliente_id' => $cliente->id,
                'action_type' => 'discount_offer',
                'payload' => [
                    'trigger' => 'high_ltv_inactive',
                    'ltv' => $ltv,
                    'recency' => $recency,
                    'mensaje' => "Oferta especial para {$cliente->nombre}: cliente VIP con alto valor.",
                ],
                'status' => 'pending',
                'scheduled_at' => now()->addHours(4),
            ]);
        }

        if ($score >= 85 || ($pred->purchase_probability ?? 0) >= 90) {
            return CrmActionsQueue::create([
                'cliente_id' => $cliente->id,
                'action_type' => 'whatsapp',
                'payload' => [
                    'trigger' => 'hot_lead',
                    'score' => $score,
                    'probability' => $pred->purchase_probability,
                    'mensaje' => "Contacto inmediato: {$cliente->nombre} con score {$score} y alta probabilidad de compra.",
                ],
                'status' => 'pending',
                'scheduled_at' => now()->addHour(),
            ]);
        }

        if ($churn === 'alta' || $emocional === 'en_riesgo') {
            return CrmActionsQueue::create([
                'cliente_id' => $cliente->id,
                'action_type' => 'follow_up',
                'payload' => [
                    'trigger' => 'churn_risk',
                    'churn' => $churn,
                    'emocional' => $emocional,
                    'mensaje' => "Retención: {$cliente->nombre} en riesgo de abandono (churn: {$churn}).",
                ],
                'status' => 'pending',
                'scheduled_at' => now()->addDay(),
            ]);
        }

        if (in_array($cliente->estado, ['vip', 'frecuente']) && $recency >= 7 && $recency < 15) {
            return CrmActionsQueue::create([
                'cliente_id' => $cliente->id,
                'action_type' => 'loyalty_reward',
                'payload' => [
                    'trigger' => 'vip_loyalty',
                    'segmento' => $cliente->estado,
                    'recency' => $recency,
                    'mensaje' => "Recompensa fidelidad: {$cliente->nombre} es {$cliente->estado} y necesita atención.",
                ],
                'status' => 'pending',
                'scheduled_at' => now()->addHours(8),
            ]);
        }

        return null;
    }

    public function autoPipeline(): array
    {
        $queued = $this->processAllFromAgent();
        $triggered = $this->processAllTriggers();

        return [
            'from_agent' => $queued,
            'from_triggers' => $triggered,
            'total' => $queued + $triggered,
        ];
    }

    public function approveAction(CrmActionsQueue $action): void
    {
        $action->update(['status' => 'approved']);
    }

    public function executeAction(CrmActionsQueue $action): void
    {
        if ($action->status === 'cancelled') {
            return;
        }

        $action->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->logger->log('sales_action_executed', $action, $action->cliente, [
            'action_type' => $action->action_type,
            'action_id' => $action->id,
        ]);
    }

    public function cancelAction(CrmActionsQueue $action): void
    {
        $action->update(['status' => 'cancelled']);
    }

    public function failAction(CrmActionsQueue $action, string $notas = ''): void
    {
        $action->update([
            'status' => 'failed',
            'notas' => $notas,
        ]);
    }

    public function getPendingActions(int $limit = 20): Collection
    {
        return CrmActionsQueue::with('cliente')
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('scheduled_at')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(fn (CrmActionsQueue $a) => $this->formatAction($a));
    }

    public function getActionHistory(int $limit = 20): Collection
    {
        return CrmActionsQueue::with('cliente')
            ->whereIn('status', ['sent', 'failed', 'cancelled'])
            ->orderBy('updated_at', 'desc')
            ->take($limit)
            ->get()
            ->map(fn (CrmActionsQueue $a) => $this->formatAction($a));
    }

    public function getSalesStats(): array
    {
        $pendingCount = CrmActionsQueue::whereIn('status', ['pending', 'approved'])->count();
        $sentCount = CrmActionsQueue::where('status', 'sent')->count();
        $totalCount = CrmActionsQueue::count();

        $byType = CrmActionsQueue::selectRaw('action_type, COUNT(*) as total, SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent')
            ->groupBy('action_type')
            ->get()
            ->keyBy('action_type')
            ->toArray();

        $totalRevenue = (float) CrmActionsQueue::where('status', 'sent')
            ->sum('revenue_impact');

        $autoApproved = CrmActionsQueue::where('status', 'approved')->count();
        $autoMode = config('crm.auto_sales_mode', false);

        return [
            'pending_count' => $pendingCount,
            'sent_count' => $sentCount,
            'total_count' => $totalCount,
            'by_type' => $byType,
            'total_revenue_impact' => $totalRevenue,
            'auto_mode' => $autoMode,
            'waiting_approval' => $autoMode ? 0 : $pendingCount,
        ];
    }

    private function formatAction(CrmActionsQueue $a): array
    {
        return [
            'id' => $a->id,
            'cliente_id' => $a->cliente_id,
            'cliente_nombre' => $a->cliente?->nombre,
            'cliente_telefono' => $a->cliente?->telefono,
            'cliente_estado' => $a->cliente?->estado,
            'action_type' => $a->action_type,
            'payload' => $a->payload,
            'status' => $a->status,
            'scheduled_at' => $a->scheduled_at?->format('Y-m-d H:i:s'),
            'sent_at' => $a->sent_at?->format('Y-m-d H:i:s'),
            'revenue_impact' => (float) ($a->revenue_impact ?? 0),
            'notas' => $a->notas,
            'created_at' => $a->created_at->format('Y-m-d H:i:s'),
        ];
    }

    private function resolveActionType(?string $accionRecomendada): string
    {
        return match ($accionRecomendada) {
            'reactivate' => 'discount_offer',
            'contact_now' => 'whatsapp',
            'send_promotion' => 'email',
            'follow_up' => 'follow_up',
            default => 'follow_up',
        };
    }
}
