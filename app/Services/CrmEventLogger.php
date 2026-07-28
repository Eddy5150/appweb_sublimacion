<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\CrmEventLog;
use Illuminate\Database\Eloquent\Model;

class CrmEventLogger
{
    public function log(string $tipo, ?Model $causable = null, ?Cliente $cliente = null, ?array $data = null): CrmEventLog
    {
        return CrmEventLog::create([
            'tipo' => $tipo,
            'causable_type' => $causable ? $causable::class : null,
            'causable_id' => $causable ? $causable->id : null,
            'cliente_id' => $cliente?->id,
            'data' => $data,
        ]);
    }

    public function clienteCreado(Cliente $cliente): void
    {
        $this->log('cliente_created', $cliente, $cliente, [
            'nombre' => $cliente->nombre,
            'estado_inicial' => $cliente->estado,
        ]);
    }

    public function pedidoCreado(Cliente $cliente, Model $pedido): void
    {
        $this->log('pedido_created', $pedido, $cliente, [
            'pedido_id' => $pedido->id,
            'total' => (float) ($pedido->total ?? 0),
            'nuevo_total_gastado' => (float) $cliente->total_gastado,
        ]);
    }

    public function segmentoCambiado(Cliente $cliente, string $estadoAnterior, string $estadoNuevo): void
    {
        $this->log('segmento_cambiado', $cliente, $cliente, [
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'total_gastado' => (float) $cliente->total_gastado,
            'pedidos_count' => $cliente->pedidos_count ?? 0,
        ]);
    }

    public function vipDetectado(Cliente $cliente): void
    {
        $this->log('cliente_vip_detected', $cliente, $cliente, [
            'nombre' => $cliente->nombre,
            'total_gastado' => (float) $cliente->total_gastado,
        ]);
    }

    public function inactivoDetectado(Cliente $cliente): void
    {
        $this->log('cliente_inactivo_detected', $cliente, $cliente, [
            'nombre' => $cliente->nombre,
            'dias_ultima_compra' => $cliente->dias_ultima_compra,
        ]);
    }

    public function riesgoDetectado(Cliente $cliente): void
    {
        $this->log('cliente_en_riesgo_detected', $cliente, $cliente, [
            'nombre' => $cliente->nombre,
            'dias_ultima_compra' => $cliente->dias_ultima_compra,
        ]);
    }

    public function alertaGenerada(Cliente $cliente, string $tipoAlerta): void
    {
        $this->log('alerta_generada', null, $cliente, [
            'tipo_alerta' => $tipoAlerta,
        ]);
    }
}
