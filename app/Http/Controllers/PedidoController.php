<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\CrmEventLogger;
use App\Services\CrmService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PedidoController extends Controller
{
    public function index(Request $request): Response
    {
        $pedidos = Pedido::forCurrentCompany()
            ->with(['detalles.producto', 'cliente'])
            ->addSelect([
                'total_calculado' => DetallePedido::query()
                    ->selectRaw('COALESCE(SUM(precio_unitario * cantidad), 0)')
                    ->whereColumn('pedido_id', 'pedidos.id'),
            ])
            ->search($request->search)
            ->estado($request->estado)
            ->fechaDesde($request->fecha_desde)
            ->fechaHasta($request->fecha_hasta)
            ->totalMinimo($request->total_min)
            ->totalMaximo($request->total_max)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $statsQuery = Pedido::forCurrentCompany()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes")
            ->selectRaw("SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso")
            ->selectRaw("SUM(CASE WHEN estado = 'listo' THEN 1 ELSE 0 END) as listos")
            ->selectRaw("SUM(CASE WHEN estado = 'entregado' THEN 1 ELSE 0 END) as entregados")
            ->first();

        $ingresos = (float) Pedido::forCurrentCompany()
            ->join('detalle_pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->whereIn('pedidos.estado', ['entregado', 'listo'])
            ->sum(DB::raw('detalle_pedidos.precio_unitario * detalle_pedidos.cantidad'));

        $stats = [
            'total' => (int) ($statsQuery?->total ?? 0),
            'pendientes' => (int) ($statsQuery?->pendientes ?? 0),
            'en_proceso' => (int) ($statsQuery?->en_proceso ?? 0),
            'listos' => (int) ($statsQuery?->listos ?? 0),
            'entregados' => (int) ($statsQuery?->entregados ?? 0),
            'ingresos' => $ingresos,
        ];

        return Inertia::render('pedidos/Index', [
            'pedidos' => $pedidos,
            'stats' => $stats,
            'filters' => $request->only(['search', 'estado', 'fecha_desde', 'fecha_hasta', 'total_min', 'total_max']),
        ]);
    }

    public function create(): Response
    {
        $clientes = Cliente::forCurrentCompany()->orderBy('nombre')->get();
        $productos = Producto::forCurrentCompany()->where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('pedidos/Create', [
            'clientes' => $clientes,
            'productos' => $productos,
        ]);
    }

    public function store(Request $request, CrmEventLogger $logger, CrmService $crm, StockService $stock): RedirectResponse
    {
        $validated = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_nombre' => 'required_without:cliente_id|string|max:255',
            'cliente_telefono' => 'nullable|string|max:20',
            'cliente_direccion' => 'nullable|string|max:500',
            'estado' => 'required|in:pendiente,en_proceso,listo,entregado',
            'fecha_entrega' => 'nullable|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'nullable|exists:productos,id',
            'detalles.*.producto_nombre' => 'required_without:detalles.*.producto_id|string|max:255',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.detalles_diseno' => 'nullable|string',
        ]);

        $total = 0;
        foreach ($validated['detalles'] as $item) {
            $total += $item['cantidad'] * $item['precio_unitario'];
        }
        $validated['total'] = $total;

        $detalles = $validated['detalles'] ?? [];
        unset($validated['detalles']);

        $pedido = Pedido::create(array_merge($validated, ['company_id' => auth()->user()->company_id]));

        foreach ($detalles as $item) {
            $item['pedido_id'] = $pedido->id;
            $item['subtotal'] = $item['cantidad'] * $item['precio_unitario'];
            DetallePedido::create($item);

            if (! empty($item['producto_id'])) {
                $producto = Producto::find($item['producto_id']);
                if ($producto) {
                    $stock->salida($producto, (int) $item['cantidad'], $pedido);
                }
            }
        }

        if ($pedido->cliente_id) {
            $cliente = $pedido->cliente;
            $logger->pedidoCreado($cliente, $pedido);
            $crm->clasificarCliente($cliente);
        }

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido creado exitosamente.');
    }

    public function show(Pedido $pedido): Response
    {
        $pedido->load(['detalles.producto', 'cliente']);

        return Inertia::render('pedidos/Show', [
            'pedido' => $pedido,
        ]);
    }

    public function edit(Pedido $pedido): Response
    {
        $pedido->load(['detalles.producto', 'cliente']);

        $clientes = Cliente::forCurrentCompany()->orderBy('nombre')->get();
        $productos = Producto::forCurrentCompany()->where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('pedidos/Edit', [
            'pedido' => $pedido,
            'clientes' => $clientes,
            'productos' => $productos,
        ]);
    }

    public function update(Request $request, Pedido $pedido, StockService $stock): RedirectResponse
    {
        $validated = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_nombre' => 'required_without:cliente_id|string|max:255',
            'cliente_telefono' => 'nullable|string|max:20',
            'cliente_direccion' => 'nullable|string|max:500',
            'estado' => 'required|in:pendiente,en_proceso,listo,entregado',
            'fecha_entrega' => 'nullable|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'nullable|exists:productos,id',
            'detalles.*.producto_nombre' => 'required_without:detalles.*.producto_id|string|max:255',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.detalles_diseno' => 'nullable|string',
        ]);

        $detalles = $validated['detalles'] ?? [];
        unset($validated['detalles']);

        $total = 0;
        foreach ($detalles as $item) {
            $total += $item['cantidad'] * $item['precio_unitario'];
        }
        $validated['total'] = $total;

        DB::transaction(function () use ($pedido, $validated, $detalles, $stock) {
            $stock->restaurarProductosVenta($pedido);

            $pedido->detalles()->delete();

            $pedido->update($validated);

            foreach ($detalles as $item) {
                $item['pedido_id'] = $pedido->id;
                $item['subtotal'] = $item['cantidad'] * $item['precio_unitario'];
                DetallePedido::create($item);

                if (! empty($item['producto_id'])) {
                    $producto = Producto::find($item['producto_id']);
                    if ($producto) {
                        $stock->salida($producto, (int) $item['cantidad'], $pedido);
                    }
                }
            }
        });

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido actualizado exitosamente.');
    }

    public function updateEstado(Request $request, Pedido $pedido, CrmEventLogger $logger, CrmService $crm, StockService $stock): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,en_proceso,listo,entregado,cancelado',
        ]);

        $estadoAnterior = $pedido->estado;

        if ($validated['estado'] === 'cancelado' && $estadoAnterior !== 'cancelado') {
            DB::transaction(function () use ($pedido, $stock) {
                $stock->restaurarProductosVenta($pedido);
                $pedido->update([
                    'estado' => 'cancelado',
                    'cancelado_en' => now(),
                    'cancelado_por' => auth()->user()?->name ?? 'Sistema',
                ]);
            });
        } else {
            $pedido->update($validated);
        }

        $pedido->loadSum(['detalles as total_calculado' => DB::raw('COALESCE(precio_unitario * cantidad, 0)')]);
        $pedido->update(['total' => $pedido->total_calculado ?? $pedido->total]);

        if ($pedido->cliente_id && $estadoAnterior !== $validated['estado']) {
            $cliente = $pedido->cliente;
            $logger->log('pedido_estado_cambiado', $pedido, $cliente, [
                'pedido_id' => $pedido->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $validated['estado'],
                'total' => (float) $pedido->total,
            ]);
            $crm->clasificarCliente($cliente);
        }

        return redirect()->route('pedidos.index')
            ->with('success', "Pedido #{$pedido->id} marcado como {$validated['estado']}.");
    }

    /**
     * @throws ValidationException
     */
    public function destroy(Pedido $pedido, StockService $stock): RedirectResponse
    {
        if (in_array($pedido->estado, ['entregado', 'cancelado'])) {
            throw ValidationException::withMessages([
                'pedido' => 'No se puede eliminar un pedido '.$pedido->estado.'.',
            ]);
        }

        DB::transaction(function () use ($pedido, $stock) {
            $stock->restaurarProductosVenta($pedido);
            $pedido->delete();
        });

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido eliminado exitosamente.');
    }
}
