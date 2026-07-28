<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    public function index(): Response
    {
        $ventasHoy = $this->ventasData(now()->startOfDay(), now()->endOfDay());
        $ventasMes = $this->ventasData(now()->startOfMonth(), now()->endOfMonth());
        $ventasAnual = $this->ventasData(now()->startOfYear(), now()->endOfYear());

        $topProductos = $this->productosMasVendidosData();
        $topClientes = $this->clientesMasRentables();
        $ventasPorDia = $this->ventasPorDia();
        $ventasPorMes = $this->ventasPorMes();

        $ingresosEstimados = (float) DetallePedido::forCurrentCompany()
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->whereIn('pedidos.estado', ['entregado', 'listo'])
            ->sum('detalle_pedidos.subtotal');

        $costosEstimados = $ingresosEstimados * 0.4;
        $gananciaEstimada = $ingresosEstimados - $costosEstimados;

        return Inertia::render('reportes/Index', [
            'resumen' => [
                'ventasHoy' => $ventasHoy['total'],
                'ventasMes' => $ventasMes['total'],
                'ventasAnual' => $ventasAnual['total'],
                'pedidosHoy' => $ventasHoy['cantidad'],
                'pedidosMes' => $ventasMes['cantidad'],
                'pedidosAnual' => $ventasAnual['cantidad'],
                'gananciaEstimada' => round($gananciaEstimada, 2),
                'ingresosEstimados' => round($ingresosEstimados, 2),
            ],
            'topProductos' => $topProductos,
            'topClientes' => $topClientes,
            'ventasPorDia' => $ventasPorDia,
            'ventasPorMes' => $ventasPorMes,
        ]);
    }

    public function ventasPorFecha(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => 'nullable|in:pendiente,en_proceso,listo,entregado,cancelado',
        ]);

        $query = Pedido::forCurrentCompany()
            ->whereBetween('created_at', [$validated['fecha_inicio'], $validated['fecha_fin']]);

        if (! empty($validated['estado'])) {
            $query->where('estado', $validated['estado']);
        }

        $ventas = $query->orderBy('created_at', 'desc')->get();

        return response()->json($ventas);
    }

    public function productosMasVendidos(): JsonResponse
    {
        return response()->json($this->productosMasVendidosData());
    }

    public function pedidosPorEstado(): JsonResponse
    {
        $estados = Pedido::forCurrentCompany()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->orderBy('estado')
            ->get();

        return response()->json($estados);
    }

    public function exportarPdf(Request $request): \Illuminate\Http\Response
    {
        $tipo = $request->get('tipo', 'ventas');
        $desde = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->format('Y-m-d'));

        $ventas = Pedido::forCurrentCompany()->with('detalles')
            ->whereBetween('created_at', [$desde, $hasta])
            ->orderBy('created_at', 'desc')
            ->get();

        $total = (float) DetallePedido::forCurrentCompany()
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->whereBetween('pedidos.created_at', [$desde, $hasta])
            ->sum('detalle_pedidos.subtotal');

        $pdf = Pdf::loadView('reportes.ventas', [
            'ventas' => $ventas,
            'total' => $total,
            'desde' => $desde,
            'hasta' => $hasta,
            'fecha' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download("reporte-ventas-{$desde}-{$hasta}.pdf");
    }

    public function exportarExcel(Request $request): JsonResponse
    {
        $desde = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->format('Y-m-d'));

        $ventas = Pedido::forCurrentCompany()->with('detalles')
            ->whereBetween('created_at', [$desde, $hasta])
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = "Fecha,Cliente,Total,Estado\n";
        foreach ($ventas as $v) {
            $csv .= "{$v->created_at->format('d/m/Y')},{$v->cliente_nombre},".number_format((float) $v->total, 2).",{$v->estado}\n";
        }

        return response()->json([
            'csv' => $csv,
            'filename' => "reporte-ventas-{$desde}-{$hasta}.csv",
            'message' => 'Exportación CSV generada. Próximamente: Excel nativo.',
        ]);
    }

    private function ventasData($desde, $hasta): array
    {
        $query = DetallePedido::forCurrentCompany()
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->whereBetween('pedidos.created_at', [$desde, $hasta])
            ->whereIn('pedidos.estado', ['entregado', 'listo']);

        return [
            'total' => round((float) (clone $query)->sum('detalle_pedidos.subtotal'), 2),
            'cantidad' => (clone $query)->count('detalle_pedidos.id'),
        ];
    }

    private function productosMasVendidosData(): array
    {
        return DetallePedido::forCurrentCompany()
            ->selectRaw('
                detalle_pedidos.producto_nombre,
                SUM(detalle_pedidos.cantidad) as total_cantidad,
                SUM(detalle_pedidos.subtotal) as total_ingresos
            ')
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->whereIn('pedidos.estado', ['entregado', 'listo'])
            ->groupBy('detalle_pedidos.producto_nombre')
            ->orderByDesc('total_cantidad')
            ->take(10)
            ->get()
            ->toArray();
    }

    private function clientesMasRentables(): array
    {
        return Cliente::forCurrentCompany()
            ->select('clientes.nombre')
            ->selectSub(
                DetallePedido::query()
                    ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
                    ->whereColumn('pedidos.cliente_id', 'clientes.id')
                    ->whereIn('pedidos.estado', ['entregado', 'listo'])
                    ->selectRaw('COALESCE(SUM(detalle_pedidos.subtotal), 0)'),
                'total_gastado'
            )
            ->whereHas('pedidos')
            ->orderByDesc('total_gastado')
            ->take(5)
            ->get()
            ->toArray();
    }

    private function ventasPorDia(): array
    {
        return DetallePedido::forCurrentCompany()
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->whereBetween('pedidos.created_at', [now()->subDays(30), now()])
            ->whereIn('pedidos.estado', ['entregado', 'listo'])
            ->selectRaw('DATE(pedidos.created_at) as fecha, SUM(detalle_pedidos.subtotal) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->toArray();
    }

    private function ventasPorMes(): array
    {
        return DetallePedido::forCurrentCompany()
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->whereIn('pedidos.estado', ['entregado', 'listo'])
            ->selectRaw('DATE_FORMAT(pedidos.created_at, "%Y-%m") as mes, SUM(detalle_pedidos.subtotal) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->take(12)
            ->get()
            ->toArray();
    }
}
