<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\CrmAutomationRule;

class CrmRulesEngine
{
    public function evaluarCliente(Cliente $cliente): array
    {
        $reglas = CrmAutomationRule::where('activa', true)
            ->orderBy('prioridad', 'desc')
            ->get();

        $disparadas = [];

        foreach ($reglas as $regla) {
            if ($this->evaluarCondicion($regla, $cliente)) {
                $disparadas[] = $regla;
            }
        }

        return $disparadas;
    }

    public function evaluarCondicion(CrmAutomationRule $regla, Cliente $cliente): bool
    {
        $tipo = $regla->condicion_tipo;
        $valor = $regla->condicion_valor;
        $extra = $regla->condicion_extra ?? [];

        $totalGastado = (float) ($cliente->total_gastado ?? 0);
        $totalPedidos = $cliente->pedidos_count ?? 0;
        $diasUltima = $cliente->dias_ultima_compra;
        $estado = $cliente->estado;

        return match ($tipo) {
            'total_gastado_gt' => $totalGastado > (float) $valor,
            'total_gastado_gte' => $totalGastado >= (float) $valor,
            'total_gastado_lt' => $totalGastado < (float) $valor,

            'pedidos_count_gte' => $totalPedidos >= (int) $valor,
            'pedidos_count_eq' => $totalPedidos === (int) $valor,
            'pedidos_count_gt' => $totalPedidos > (int) $valor,

            'sin_compras_dias_gt' => $diasUltima !== null && $diasUltima > (int) $valor,
            'sin_compras_dias_gte' => $diasUltima !== null && $diasUltima >= (int) $valor,
            'sin_compras_dias_eq' => $diasUltima !== null && $diasUltima === (int) $valor,

            'estado_eq' => $estado === $valor,
            'estado_in' => in_array($estado, explode(',', $valor)),

            'tendencia_eq' => ($cliente->tendencia ?? 'estable') === $valor,

            'frecuencia_promedio_lte' => $cliente->frecuencia_promedio !== null
                && $cliente->frecuencia_promedio <= (float) $valor,

            'primera_compra_dias_gte' => $cliente->primera_compra !== null
                && now()->diffInDays($cliente->primera_compra) >= (int) $valor,

            'compro_ultimos_dias' => $diasUltima !== null && $diasUltima <= (int) $valor,

            default => false,
        };
    }

    public function condicionTipoHumano(string $tipo): string
    {
        return match ($tipo) {
            'total_gastado_gt' => 'Total gastado >',
            'total_gastado_gte' => 'Total gastado ≥',
            'total_gastado_lt' => 'Total gastado <',
            'pedidos_count_gte' => 'Pedidos ≥',
            'pedidos_count_eq' => 'Pedidos =',
            'pedidos_count_gt' => 'Pedidos >',
            'sin_compras_dias_gt' => 'Sin compras >',
            'sin_compras_dias_gte' => 'Sin compras ≥',
            'sin_compras_dias_eq' => 'Sin compras =',
            'estado_eq' => 'Estado =',
            'estado_in' => 'Estado en',
            'tendencia_eq' => 'Tendencia =',
            'frecuencia_promedio_lte' => 'Frecuencia promedio ≤',
            'primera_compra_dias_gte' => 'Cliente desde ≥',
            'compro_ultimos_dias' => 'Compró últimos',
            default => $tipo,
        };
    }

    public function accionTipoHumano(string $tipo): string
    {
        return match ($tipo) {
            'marcar_estado' => 'Marcar como',
            'generar_alerta' => 'Generar alerta',
            'enviar_mensaje' => 'Enviar mensaje',
            default => $tipo,
        };
    }
}
