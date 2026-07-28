<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\CrmAiPrediction;
use App\Models\CrmProductoRecomendado;
use App\Services\AiInsightGenerator;
use App\Services\AiPredictionEngine;
use App\Services\RecommendationEngine;
use App\Services\RevenueForecastService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CrmAiController extends Controller
{
    public function __construct(
        private AiPredictionEngine $predictionEngine,
        private RevenueForecastService $forecast,
        private RecommendationEngine $recommendations,
        private AiInsightGenerator $insights,
    ) {}

    public function index(): Response
    {
        $forecast = $this->forecast->forecast7Days();
        $topClients = $this->forecast->topFutureClients(10);

        $clientesAI = CrmAiPrediction::forCurrentCompany()->with('cliente')
            ->whereHas('cliente')
            ->orderByDesc('ai_score')
            ->take(20)
            ->get()
            ->map(fn (CrmAiPrediction $p) => [
                'cliente_id' => $p->cliente_id,
                'cliente_nombre' => $p->cliente?->nombre,
                'ai_score' => $p->ai_score,
                'purchase_probability' => $p->purchase_probability,
                'estimated_next_purchase' => $p->estimated_next_purchase?->format('Y-m-d'),
                'emotional_state' => $p->emotional_state,
                'future_trend' => $p->future_trend,
                'ltv_estimated' => $p->ltv_estimated,
                'churn_risk' => $p->churn_risk,
            ]);

        $stats = [
            'total_predictions' => CrmAiPrediction::forCurrentCompany()->count(),
            'avg_ai_score' => round(CrmAiPrediction::forCurrentCompany()->avg('ai_score') ?? 0, 1),
            'avg_probability' => round(CrmAiPrediction::forCurrentCompany()->avg('purchase_probability') ?? 0, 1),
            'high_churn_count' => CrmAiPrediction::forCurrentCompany()->where('churn_risk', 'alta')->count(),
            'happy_count' => CrmAiPrediction::forCurrentCompany()->where('emotional_state', 'feliz')->count(),
            'at_risk_count' => CrmAiPrediction::forCurrentCompany()->where('emotional_state', 'en_riesgo')->count(),
            'trending_up' => CrmAiPrediction::forCurrentCompany()->where('future_trend', 'sube')->count(),
            'total_ltv' => round(CrmAiPrediction::forCurrentCompany()->sum('ltv_estimated'), 2),
        ];

        return Inertia::render('crm/AiDashboard', [
            'forecast' => $forecast,
            'topClients' => $topClients,
            'clientesAI' => $clientesAI,
            'stats' => $stats,
        ]);
    }

    public function procesar(): RedirectResponse
    {
        $count = $this->predictionEngine->predecirTodos();

        return redirect()->route('crm.ai')
            ->with('success', "IA CRM: {$count} clientes procesados con predicciones.");
    }

    public function procesarCliente(Cliente $cliente): RedirectResponse
    {
        $this->predictionEngine->predecirCliente($cliente);

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Predicción IA actualizada para este cliente.');
    }

    public function recomendaciones(Cliente $cliente): RedirectResponse
    {
        $this->recommendations->recomendarProductos($cliente);

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Recomendaciones de productos generadas.');
    }

    public function contactarHoy(): Response
    {
        $paraContactar = $this->recommendations->clientesAContactarHoy(10);

        return Inertia::render('crm/AiContactar', [
            'clientes' => $paraContactar,
        ]);
    }

    public function insightCliente(Cliente $cliente): array
    {
        $prediction = CrmAiPrediction::forCurrentCompany()->where('cliente_id', $cliente->id)->first();
        $productosRecomendados = CrmProductoRecomendado::forCurrentCompany()->with('producto')
            ->where('cliente_id', $cliente->id)
            ->orderByDesc('score')
            ->take(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'producto_id' => $r->producto_id,
                'producto_nombre' => $r->producto?->nombre,
                'precio' => (float) ($r->producto?->precio ?? 0),
                'score' => $r->score,
                'motivo' => $r->motivo,
            ]);

        return [
            'id' => $cliente->id,
            'prediction' => $prediction ? [
                'ai_score' => $prediction->ai_score,
                'purchase_probability' => $prediction->purchase_probability,
                'estimated_next_purchase' => $prediction->estimated_next_purchase?->format('Y-m-d'),
                'emotional_state' => $prediction->emotional_state,
                'future_trend' => $prediction->future_trend,
                'ltv_estimated' => $prediction->ltv_estimated,
                'churn_risk' => $prediction->churn_risk,
            ] : null,
            'insight' => $this->insights->generarInsight($cliente),
            'insight_corto' => $this->insights->insightCorto($cliente),
            'productos_recomendados' => $productosRecomendados,
        ];
    }
}
