<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CajaMovimiento;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    public function stats(): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $now = now();
        $inicioMes = $now->copy()->startOfMonth();
        $inicioHoy = $now->copy()->startOfDay();

        $pedidosQuery = Pedido::forCurrentCompany()->join('detalle_pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id');

        $ventasHoy = (float) (clone $pedidosQuery)
            ->whereBetween('pedidos.created_at', [$inicioHoy, $now])
            ->whereIn('pedidos.estado', ['entregado', 'listo'])
            ->sum(DB::raw('detalle_pedidos.precio_unitario * detalle_pedidos.cantidad'));

        $ventasMes = (float) (clone $pedidosQuery)
            ->whereBetween('pedidos.created_at', [$inicioMes, $now])
            ->whereIn('pedidos.estado', ['entregado', 'listo'])
            ->sum(DB::raw('detalle_pedidos.precio_unitario * detalle_pedidos.cantidad'));

        return response()->json([
            'productos_activos' => Producto::forCurrentCompany()->where('activo', true)->count(),
            'pedidos_pendientes' => Pedido::forCurrentCompany()->where('estado', 'pendiente')->count(),
            'ventas_hoy' => $ventasHoy,
            'ventas_mes' => $ventasMes,
            'clientes_totales' => Cliente::forCurrentCompany()->count(),
            'caja_hoy' => (float) CajaMovimiento::forCurrentCompany()
                ->whereDate('created_at', today())
                ->where('tipo', 'ingreso')
                ->sum('monto'),
        ]);
    }

    public function ventasPorMes(): JsonResponse
    {
        $ventas = Pedido::forCurrentCompany()
            ->join('detalle_pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->whereIn('pedidos.estado', ['entregado', 'listo'])
            ->selectRaw('YEAR(pedidos.created_at) as year, MONTH(pedidos.created_at) as month, SUM(detalle_pedidos.precio_unitario * detalle_pedidos.cantidad) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->take(12)
            ->get();

        return response()->json($ventas);
    }

    public function topProductos(): JsonResponse
    {
        $top = Pedido::forCurrentCompany()
            ->join('detalle_pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->whereIn('pedidos.estado', ['entregado', 'listo'])
            ->selectRaw('detalle_pedidos.producto_nombre, SUM(detalle_pedidos.cantidad) as total, SUM(detalle_pedidos.precio_unitario * detalle_pedidos.cantidad) as ingresos')
            ->groupBy('detalle_pedidos.producto_nombre')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return response()->json($top);
    }
}
