<?php

namespace App\Services;

use App\Models\Cliente;

class AiPredictionEngine
{
    /**
     * Calcular score RFM (SaaS CRM PRO)
     */
    public function calculate(Cliente $cliente): array
    {
        // 🧠 FEATURE EXTRACTION (RFM MODEL)
        $recency = $this->getRecency($cliente);      // días sin comprar
        $frequency = $this->getFrequency($cliente);   // número de pedidos
        $monetary = $this->getMonetary($cliente);     // total gastado

        // 📊 NORMALIZACIÓN
        $recencyScore = max(0, 100 - ($recency * 2));
        $frequencyScore = min(100, $frequency * 10);
        $monetaryScore = min(100, $monetary / 10);

        // 🧮 SCORE FINAL
        $score = (
            ($recencyScore * 0.3) +
            ($frequencyScore * 0.3) +
            ($monetaryScore * 0.4)
        );

        // ❤️ ESTADO EMOCIONAL CLIENTE
        $state = $this->getCustomerState($recency, $frequency);

        // ⚠️ RIESGO DE CHURN
        $churnRisk = $this->getChurnRisk($recency, $frequency);

        // 💰 LTV (Lifetime Value estimado)
        $ltv = $monetary * (1 + ($frequency * 0.1));

        return [
            'score' => round($score, 2),
            'recency' => $recency,
            'frequency' => $frequency,
            'monetary' => $monetary,
            'state' => $state,
            'churn_risk' => $churnRisk,
            'ltv' => round($ltv, 2),
        ];
    }

    /**
     * Días desde última compra
     */
    private function getRecency(Cliente $cliente): int
    {
        if (! $cliente->last_order_date) {
            return 999;
        }

        return now()->diffInDays($cliente->last_order_date);
    }

    /**
     * Frecuencia de compras
     */
    private function getFrequency(Cliente $cliente): int
    {
        return $cliente->pedidos()->count();
    }

    /**
     * Total monetario gastado
     */
    private function getMonetary(Cliente $cliente): float
    {
        return $cliente->pedidos()
            ->with('detalles')
            ->get()
            ->sum(function ($pedido) {
                return $pedido->detalles->sum(function ($d) {
                    return $d->precio_unitario * $d->cantidad;
                });
            });
    }

    /**
     * Estado del cliente (CRM)
     */
    private function getCustomerState(int $recency, int $frequency): string
    {
        if ($recency <= 7 && $frequency > 3) {
            return 'feliz';
        }

        if ($recency <= 30) {
            return 'activo';
        }

        return 'en_riesgo';
    }

    /**
     * Riesgo de abandono
     */
    private function getChurnRisk(int $recency, int $frequency): string
    {
        if ($recency > 60 || $frequency < 2) {
            return 'alto';
        }

        if ($recency > 30) {
            return 'medio';
        }

        return 'bajo';
    }
}
