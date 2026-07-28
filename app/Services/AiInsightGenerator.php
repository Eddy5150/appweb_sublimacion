<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\CrmAiPrediction;

class AiInsightGenerator
{
    public function generarInsight(Cliente $cliente): string
    {
        $prediction = CrmAiPrediction::where('cliente_id', $cliente->id)->first();
        $diasUltima = $cliente->dias_ultima_compra;
        $cuentaPedidos = $cliente->pedidos_count ?? 0;
        $totalGastado = (float) ($cliente->total_gastado ?? 0);

        $insights = [];

        if ($prediction) {
            if ($prediction->purchase_probability >= 70) {
                $insights[] = "Alta probabilidad de compra en los próximos días ({$prediction->purchase_probability}%)";
            }

            if ($prediction->churn_risk === 'alta') {
                $insights[] = 'Alto riesgo de pérdida. Requiere acción inmediata.';
            }

            if ($prediction->emotional_state === 'feliz') {
                $insights[] = 'Cliente satisfecho y comprometido.';
            } elseif ($prediction->emotional_state === 'en_riesgo') {
                $insights[] = 'Cliente en riesgo emocional. Ofrecer incentivos.';
            }

            if ($prediction->future_trend === 'sube') {
                $insights[] = 'Tendencia de compra al alza. Cliente en crecimiento.';
            } elseif ($prediction->future_trend === 'baja') {
                $insights[] = 'Tendencia de compra a la baja. Monitorear.';
            }

            if ($prediction->estimated_next_purchase) {
                $diasRestantes = now()->diffInDays($prediction->estimated_next_purchase, false);
                if ($diasRestantes > 0 && $diasRestantes <= 7) {
                    $insights[] = "Posible compra en los próximos {$diasRestantes} días.";
                } elseif ($diasRestantes <= 0) {
                    $insights[] = 'Fecha estimada de compra vencida. Contactar.';
                }
            }
        }

        if ($diasUltima !== null) {
            if ($diasUltima >= 60) {
                $insights[] = "No compra hace {$diasUltima} días. Riesgo de abandono.";
            } elseif ($diasUltima >= 30) {
                $insights[] = "No compra hace {$diasUltima} días. Perdiendo interés.";
            } elseif ($diasUltima <= 7) {
                $insights[] = "Compró hace {$diasUltima} días. Cliente activo.";
            }
        }

        if ($cuentaPedidos === 0) {
            $insights[] = 'Cliente sin compras. Enviar oferta de bienvenida.';
        } elseif ($cuentaPedidos === 1) {
            $insights[] = 'Cliente nuevo con 1 compra. Fidelizar para retener.';
        } elseif ($cuentaPedidos >= 5 && $totalGastado > 1000) {
            $insights[] = "Cliente frecuente de alto valor ({$totalGastado} Bs en {$cuentaPedidos} compras).";
        }

        if (empty($insights)) {
            $insights[] = 'Cliente sin actividad reciente. Monitorear evolución.';
        }

        return implode(' ', $insights);
    }

    public function insightCorto(Cliente $cliente): string
    {
        $prediction = CrmAiPrediction::where('cliente_id', $cliente->id)->first();
        $diasUltima = $cliente->dias_ultima_compra;

        if ($prediction && $prediction->purchase_probability >= 70) {
            return 'Alta probabilidad de compra en 48h';
        }

        if ($prediction && $prediction->churn_risk === 'alta') {
            return 'En riesgo de abandono';
        }

        if ($diasUltima !== null && $diasUltima <= 7) {
            return 'Compra reciente · Cliente activo';
        }

        if ($diasUltima !== null && $diasUltima > 20) {
            return "Cliente en riesgo, no compra hace {$diasUltima} días";
        }

        if ($prediction && $prediction->future_trend === 'sube') {
            return 'Cliente en crecimiento constante';
        }

        $cuentaPedidos = $cliente->pedidos_count ?? 0;
        if ($cuentaPedidos >= 3) {
            return 'Cliente frecuente estable';
        }

        return 'Monitorear evolución del cliente';
    }
}
