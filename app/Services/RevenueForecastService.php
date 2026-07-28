<?php

namespace App\Services;

use App\Models\CrmAiPrediction;
use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RevenueForecastService
{
    public function forecast7Days(): array
    {
        $dailyRevenue = $this->dailyRevenueLast30Days();

        if ($dailyRevenue->isEmpty()) {
            return [
                'estimated_7d' => 0,
                'estimated_monthly' => 0,
                'confidence' => 'baja',
                'daily_breakdown' => [],
            ];
        }

        $avgDaily = round($dailyRevenue->avg(), 2);
        $trend = $this->calculateTrend($dailyRevenue);

        $trendFactor = $trend === 'up' ? 1.1 : ($trend === 'down' ? 0.9 : 1.0);
        $estimated7d = round($avgDaily * 7 * $trendFactor, 2);
        $estimatedMonthly = round($avgDaily * 30 * $trendFactor, 2);

        $dailyBreakdown = [];
        for ($i = 1; $i <= 7; $i++) {
            $dayFactor = $this->dayOfWeekFactor(now()->addDays($i)->dayOfWeek);
            $dailyBreakdown[] = [
                'date' => now()->addDays($i)->format('Y-m-d'),
                'day_name' => now()->addDays($i)->dayName,
                'estimated' => round($avgDaily * $dayFactor * $trendFactor, 2),
            ];
        }

        $confidence = $dailyRevenue->count() >= 15 ? 'alta' : ($dailyRevenue->count() >= 7 ? 'media' : 'baja');

        return [
            'estimated_7d' => $estimated7d,
            'estimated_monthly' => $estimatedMonthly,
            'avg_daily' => $avgDaily,
            'trend' => $trend,
            'confidence' => $confidence,
            'daily_breakdown' => $dailyBreakdown,
            'days_analyzed' => $dailyRevenue->count(),
        ];
    }

    public function topFutureClients(int $limit = 10): Collection
    {
        return CrmAiPrediction::with('cliente')
            ->whereHas('cliente')
            ->orderByDesc('purchase_probability')
            ->orderByDesc('ltv_estimated')
            ->take($limit)
            ->get()
            ->map(fn (CrmAiPrediction $p) => [
                'cliente_id' => $p->cliente_id,
                'cliente_nombre' => $p->cliente?->nombre,
                'ai_score' => $p->ai_score,
                'purchase_probability' => $p->purchase_probability,
                'ltv_estimated' => $p->ltv_estimated,
                'estimated_next_purchase' => $p->estimated_next_purchase?->format('Y-m-d'),
                'emotional_state' => $p->emotional_state,
                'churn_risk' => $p->churn_risk,
            ]);
    }

    private function dailyRevenueLast30Days(): Collection
    {
        return Pedido::query()
            ->join('detalle_pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->where('pedidos.created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(pedidos.created_at) as date, SUM(detalle_pedidos.precio_unitario * detalle_pedidos.cantidad) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('revenue')
            ->map(fn ($v) => (float) $v);
    }

    private function calculateTrend(Collection $dailyRevenue): string
    {
        if ($dailyRevenue->count() < 7) {
            return 'stable';
        }

        $firstHalf = $dailyRevenue->take(floor($dailyRevenue->count() / 2))->avg();
        $secondHalf = $dailyRevenue->skip(floor($dailyRevenue->count() / 2))->avg();

        if ($secondHalf > $firstHalf * 1.1) {
            return 'up';
        }

        if ($secondHalf < $firstHalf * 0.9) {
            return 'down';
        }

        return 'stable';
    }

    private function dayOfWeekFactor(int $dayOfWeek): float
    {
        $factors = [
            Carbon::MONDAY => 0.9,
            Carbon::TUESDAY => 0.85,
            Carbon::WEDNESDAY => 0.9,
            Carbon::THURSDAY => 0.95,
            Carbon::FRIDAY => 1.1,
            Carbon::SATURDAY => 1.2,
            Carbon::SUNDAY => 1.0,
        ];

        return $factors[$dayOfWeek] ?? 1.0;
    }
}
