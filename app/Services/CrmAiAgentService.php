<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\CrmAiFeedback;
use App\Models\CrmAiMessage;
use App\Models\CrmAiPrediction;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CrmAiAgentService
{
    public function __construct(
        private CrmEventLogger $logger,
    ) {}

    public function processAllClients(): int
    {
        $contador = 0;

        $detallesSub = DetallePedido::query()
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->whereColumn('pedidos.cliente_id', 'clientes.id')
            ->selectRaw('COALESCE(SUM(detalle_pedidos.precio_unitario * detalle_pedidos.cantidad), 0)');

        $maxDateSub = Pedido::query()
            ->whereColumn('cliente_id', 'clientes.id')
            ->selectRaw('MAX(created_at)');

        $minDateSub = Pedido::query()
            ->whereColumn('cliente_id', 'clientes.id')
            ->selectRaw('MIN(created_at)');

        Cliente::query()
            ->select('clientes.*')
            ->selectSub($detallesSub, 'total_detalles_sum')
            ->selectSub($maxDateSub, 'pedidos_created_at_max')
            ->selectSub($minDateSub, 'pedidos_created_at_min')
            ->withCount('pedidos')
            ->chunk(100, function (Collection $clientes) use (&$contador) {
                foreach ($clientes as $cliente) {
                    $this->processClient($cliente);
                    $contador++;
                }
            });

        return $contador;
    }

    public function processClient(Cliente $cliente): array
    {
        $analysis = $this->analyzeClient($cliente);
        $action = $this->decideNextBestAction($analysis);
        $mensaje = $this->generateMessage($cliente, $action['action'], $analysis);

        $msg = CrmAiMessage::create([
            'cliente_id' => $cliente->id,
            'mensaje' => $mensaje,
            'tipo' => 'whatsapp',
            'estado' => 'pending',
            'accion_recomendada' => $action['action'],
            'contexto' => [
                'analysis' => $analysis,
                'action_reason' => $action['reason'],
                'action_priority' => $action['priority'],
            ],
        ]);

        $this->logger->log('ai_agent_action', $cliente, $cliente, [
            'action' => $action['action'],
            'reason' => $action['reason'],
            'ai_score' => $analysis['ai_score'] ?? null,
            'recency' => $analysis['recency_dias'],
        ]);

        return [
            'message_id' => $msg->id,
            'action' => $action,
            'mensaje' => $mensaje,
            'analysis' => $analysis,
        ];
    }

    public function analyzeClient(Cliente $cliente): array
    {
        $prediction = CrmAiPrediction::where('cliente_id', $cliente->id)->first();

        $cuentaPedidos = $cliente->pedidos_count ?? 0;
        $totalGastado = (float) ($cliente->total_gastado ?? 0);
        $ultimaCompra = $cliente->ultima_compra;
        $primeraCompra = $cliente->primera_compra;
        $recency = $cliente->dias_ultima_compra;
        $tendencia = $cliente->tendencia ?? 'estable';
        $frecuencia = $cliente->frecuencia_promedio;

        $avgOrderValue = $cuentaPedidos > 0 ? round($totalGastado / $cuentaPedidos, 2) : 0;

        $avgGap = null;
        if ($primeraCompra && $ultimaCompra && $cuentaPedidos >= 2) {
            $diasRango = (int) Carbon::parse($ultimaCompra)->diffInDays(Carbon::parse($primeraCompra));
            $avgGap = $cuentaPedidos > 1 ? round($diasRango / ($cuentaPedidos - 1)) : null;
        }

        return [
            'cliente_id' => $cliente->id,
            'cliente_nombre' => $cliente->nombre,
            'recency_dias' => $recency,
            'frequency' => $cuentaPedidos,
            'monetary' => $totalGastado,
            'avg_order_value' => $avgOrderValue,
            'avg_dias_entre_compras' => $avgGap,
            'tendencia' => $tendencia,
            'frecuencia_promedio' => $frecuencia,
            'segmento_actual' => $cliente->estado,
            'ai_score' => $prediction?->ai_score,
            'purchase_probability' => $prediction?->purchase_probability,
            'emotional_state' => $prediction?->emotional_state,
            'churn_risk' => $prediction?->churn_risk,
            'ltv_estimated' => $prediction?->ltv_estimated,
            'future_trend' => $prediction?->future_trend,
        ];
    }

    public function decideNextBestAction(array $analysis): array
    {
        $recency = $analysis['recency_dias'] ?? 999;
        $frequency = $analysis['frequency'] ?? 0;
        $monetary = $analysis['monetary'] ?? 0;
        $churnRisk = $analysis['churn_risk'] ?? 'baja';
        $emotionalState = $analysis['emotional_state'] ?? 'neutro';
        $purchaseProb = $analysis['purchase_probability'] ?? 0;
        $tendencia = $analysis['tendencia'] ?? 'estable';
        $avgGap = $analysis['avg_dias_entre_compras'];
        $segmento = $analysis['segmento_actual'] ?? 'nuevo';

        if ($recency === null || $frequency === 0) {
            return [
                'action' => 'ignore',
                'reason' => 'Cliente sin pedidos, no requiere acción.',
                'priority' => 0,
            ];
        }

        if ($churnRisk === 'alta' || $recency > 60 || ($avgGap && $recency > $avgGap * 2)) {
            return [
                'action' => 'reactivate',
                'reason' => "Alto riesgo de abandono. {$recency} días sin compra".($avgGap ? " (gap promedio {$avgGap}d)." : '.'),
                'priority' => 5,
            ];
        }

        if (($purchaseProb >= 80 || $emotionalState === 'en_riesgo') && $recency >= 7) {
            return [
                'action' => 'contact_now',
                'reason' => "Probabilidad de compra {$purchaseProb}% con {$recency}d de inactividad. Cliente necesita atención inmediata.",
                'priority' => 4,
            ];
        }

        if ($recency >= 15 && $recency <= 45 && $frequency >= 1 && $segmento !== 'vip') {
            return [
                'action' => 'send_promotion',
                'reason' => "{$recency} días sin comprar con {$frequency} pedidos previos. Cliente receptivo a promociones.",
                'priority' => 3,
            ];
        }

        if ($recency >= 7 && $recency < 20 && $frequency >= 2 && $tendencia !== 'sube') {
            return [
                'action' => 'follow_up',
                'reason' => "Cliente frecuente ({$frequency} pedidos) sin actividad hace {$recency}d. Seguimiento oportuno.",
                'priority' => 2,
            ];
        }

        if ($recency >= 30 && $recency <= 60 && $frequency >= 1) {
            return [
                'action' => 'send_promotion',
                'reason' => "{$recency} días desde último pedido. Cliente con potencial de reactivación mediante oferta.",
                'priority' => 3,
            ];
        }

        return [
            'action' => 'ignore',
            'reason' => 'Cliente estable. Última compra hace '.($recency ?? '?').' días. Sin necesidad de intervención.',
            'priority' => 1,
        ];
    }

    public function generateMessage(Cliente $cliente, string $action, array $analysis): string
    {
        $nombre = $cliente->nombre;
        $recency = $analysis['recency_dias'] ?? 0;
        $frequency = $analysis['frequency'] ?? 0;
        $totalGastado = $analysis['monetary'] ?? 0;
        $segmento = $analysis['segmento_actual'] ?? '';

        return match ($action) {
            'reactivate' => $this->msgReactivate($nombre, $recency, $frequency, $totalGastado),
            'contact_now' => $this->msgContactNow($nombre, $recency, $segmento),
            'send_promotion' => $this->msgPromotion($nombre, $recency, $frequency),
            'follow_up' => $this->msgFollowUp($nombre, $recency, $frequency),
            default => $this->msgIgnore(),
        };
    }

    private function msgReactivate(string $nombre, int $recency, int $frequency, float $total): string
    {
        $templates = [
            "Hola {$nombre}, hemos notado que hace {$recency} días no realizas pedidos. Queremos ofrecerte un descuento especial para que vuelvas a disfrutar de nuestros productos. ¡Te esperamos!",
            "{$nombre}, te extrañamos 🫶 Ha pasado {$recency} días desde tu última compra. Tenemos nuevas colecciones y queremos que seas el primero en conocerlas. Escríbenos para más información.",
            "¡Hola {$nombre}! Vimos que tu última visita fue hace {$recency} días. Como cliente frecuente, queremos darte un trato especial. Contáctanos para conocer nuestras ofertas exclusivas.",
        ];

        return $templates[array_rand($templates)];
    }

    private function msgContactNow(string $nombre, int $recency, string $segmento): string
    {
        if ($segmento === 'vip') {
            return "{$nombre}, queremos asegurarnos de que estés satisfecho con nuestros servicios. Ha pasado {$recency} días desde tu último pedido. ¿Necesitas algo especial? Estamos para servirte.";
        }

        $templates = [
            "Hola {$nombre}, hemos visto que hace {$recency} días no realizas pedidos. Tenemos nuevas ofertas para ti.",
            "¡Hola {$nombre}! Hace {$recency} días que no te vemos por aquí. Queremos compartirte nuestras últimas novedades. ¿Te gustaría echar un vistazo?",
        ];

        return $templates[array_rand($templates)];
    }

    private function msgPromotion(string $nombre, int $recency, int $frequency): string
    {
        $templates = [
            "¡Hola {$nombre}! Hace {$recency} días que no te vemos. Tenemos promociones especiales y queremos que seas el primero en aprovecharlas. 🎉",
            "{$nombre}, tenemos descuentos exclusivos para clientes como tú. Aprovecha para hacer tu pedido, ¡te esperamos!",
            "Oferta especial para ti, {$nombre} 🎁 Hace {$recency} días que no nos visitas y tenemos novedades que te encantarán.",
        ];

        return $templates[array_rand($templates)];
    }

    private function msgFollowUp(string $nombre, int $recency, int $frequency): string
    {
        $templates = [
            "Hola {$nombre}, esperamos que todo esté bien. ¿Cómo van los diseños? Estamos aquí para lo que necesites.",
            "{$nombre}, queremos saber cómo estás. Ha pasado {$recency} días desde tu último pedido y queremos ofrecerte nuestro apoyo.",
            "¡Hola {$nombre}! ¿Necesitas algo? Estamos disponibles para ayudarte con tu próximo pedido.",
        ];

        return $templates[array_rand($templates)];
    }

    private function msgIgnore(): string
    {
        return '';
    }

    public function getAgentRecommendations(int $limit = 10): Collection
    {
        return CrmAiMessage::with('cliente')
            ->where('estado', 'pending')
            ->where('mensaje', '!=', '')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(fn (CrmAiMessage $m) => [
                'id' => $m->id,
                'cliente_id' => $m->cliente_id,
                'cliente_nombre' => $m->cliente?->nombre,
                'cliente_telefono' => $m->cliente?->telefono,
                'cliente_estado' => $m->cliente?->estado,
                'mensaje' => $m->mensaje,
                'tipo' => $m->tipo,
                'estado' => $m->estado,
                'accion_recomendada' => $m->accion_recomendada,
                'contexto' => $m->contexto,
                'created_at' => $m->created_at->format('Y-m-d H:i:s'),
            ]);
    }

    public function getClientsToContact(int $limit = 10): Collection
    {
        return CrmAiMessage::with('cliente')
            ->where('estado', 'pending')
            ->whereIn('accion_recomendada', ['reactivate', 'contact_now'])
            ->where('mensaje', '!=', '')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(fn (CrmAiMessage $m) => [
                'id' => $m->id,
                'cliente_id' => $m->cliente_id,
                'cliente_nombre' => $m->cliente?->nombre,
                'cliente_telefono' => $m->cliente?->telefono,
                'cliente_estado' => $m->cliente?->estado,
                'mensaje' => $m->mensaje,
                'accion_recomendada' => $m->accion_recomendada,
                'created_at' => $m->created_at->format('Y-m-d H:i:s'),
            ]);
    }

    public function getAgentStats(): array
    {
        $pendingCount = CrmAiMessage::where('estado', 'pending')->where('mensaje', '!=', '')->count();
        $sentCount = CrmAiMessage::where('estado', 'sent')->count();
        $totalProcessed = CrmAiMessage::count();

        $actionBreakdown = CrmAiMessage::selectRaw('accion_recomendada, COUNT(*) as total')
            ->where('mensaje', '!=', '')
            ->groupBy('accion_recomendada')
            ->pluck('total', 'accion_recomendada')
            ->toArray();

        $feedbackCount = CrmAiFeedback::count();

        $successRate = null;
        if ($feedbackCount > 0) {
            $successCount = CrmAiFeedback::where('result', 'success')->count();
            $successRate = round(($successCount / $feedbackCount) * 100, 1);
        }

        return [
            'pending_count' => $pendingCount,
            'sent_count' => $sentCount,
            'total_processed' => $totalProcessed,
            'action_breakdown' => $actionBreakdown,
            'feedback_count' => $feedbackCount,
            'success_rate' => $successRate,
        ];
    }

    public function approveMessage(CrmAiMessage $message): void
    {
        $message->update([
            'estado' => 'approved',
        ]);
    }

    public function markSent(CrmAiMessage $message): void
    {
        $message->update([
            'estado' => 'sent',
            'enviado_en' => now(),
        ]);
    }

    public function registerFeedback(Cliente $cliente, string $actionTaken, string $result, ?float $revenueImpact = null, ?string $notas = null): CrmAiFeedback
    {
        return CrmAiFeedback::create([
            'cliente_id' => $cliente->id,
            'action_taken' => $actionTaken,
            'result' => $result,
            'revenue_impact' => $revenueImpact,
            'notas' => $notas,
            'contexto_agente' => [
                'registered_at' => now()->toDateTimeString(),
            ],
        ]);
    }
}
