<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\DetallePedido;
use Carbon\Carbon;

class CrmService
{
    public function __construct(
        private CrmEventLogger $logger,
        private CrmAutomationService $automation,
    ) {}

    const UMBRAL_VIP = 1000;

    const MIN_PEDIDOS_FRECUENTE = 3;

    const DIAS_INACTIVO = 30;

    const DIAS_RIESGO = 60;

    public function clasificarClientes(): int
    {
        $contador = 0;

        $detallesSub = DetallePedido::query()
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->whereColumn('pedidos.cliente_id', 'clientes.id')
            ->selectRaw('COALESCE(SUM(detalle_pedidos.precio_unitario * detalle_pedidos.cantidad), 0)');

        Cliente::query()
            ->select('clientes.*')
            ->selectSub($detallesSub, 'total_detalles_sum')
            ->withCount('pedidos')
            ->withAggregate('pedidos', 'created_at', 'max')
            ->chunk(100, function ($clientes) use (&$contador) {
                foreach ($clientes as $cliente) {
                    if ($this->clasificarCliente($cliente)) {
                        $contador++;
                    }
                }
            });

        return $contador;
    }

    public function clasificarCliente(Cliente $cliente): bool
    {
        $nuevoEstado = $this->determinarEstado($cliente);
        $estadoAnterior = $cliente->estado;

        if ($estadoAnterior !== $nuevoEstado) {
            $cliente->updateQuietly(['estado' => $nuevoEstado]);

            $this->logger->segmentoCambiado($cliente, $estadoAnterior, $nuevoEstado);

            if ($nuevoEstado === 'vip') {
                $this->logger->vipDetectado($cliente);
            }

            if ($nuevoEstado === 'inactivo') {
                $this->logger->inactivoDetectado($cliente);
            }

            if ($nuevoEstado === 'en_riesgo') {
                $this->logger->riesgoDetectado($cliente);
            }

            $this->automation->procesarCliente($cliente);

            return true;
        }

        return false;
    }

    public function determinarEstado(Cliente $cliente): string
    {
        $totalGastado = (float) ($cliente->total_gastado ?? 0);
        $totalPedidos = $cliente->pedidos_count ?? 0;

        $ultimaCompra = $cliente->ultima_compra;
        $diasUltima = $ultimaCompra
            ? (int) now()->diffInDays(Carbon::parse($ultimaCompra))
            : null;

        if ($totalGastado > self::UMBRAL_VIP) {
            return 'vip';
        }

        if ($diasUltima !== null && $diasUltima > self::DIAS_RIESGO && $totalPedidos >= 2) {
            return 'en_riesgo';
        }

        if ($diasUltima !== null && $diasUltima > self::DIAS_INACTIVO) {
            return 'inactivo';
        }

        if ($totalPedidos >= self::MIN_PEDIDOS_FRECUENTE) {
            return 'frecuente';
        }

        if ($totalPedidos >= 1) {
            return 'nuevo';
        }

        return 'nuevo';
    }
}
