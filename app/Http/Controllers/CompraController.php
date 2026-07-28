<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CompraController extends Controller
{
    public function index(Request $request): Response
    {
        $compras = Compra::forCurrentCompany()
            ->with(['detalles', 'proveedor'])
            ->search($request->search)
            ->estado($request->estado)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $statsQuery = Compra::forCurrentCompany()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes")
            ->selectRaw("SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas")
            ->selectRaw("SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas")
            ->first();

        $totalGastado = (float) Compra::forCurrentCompany()
            ->whereIn('estado', ['completada', 'pendiente'])
            ->sum('total');

        $stats = [
            'total' => (int) ($statsQuery?->total ?? 0),
            'pendientes' => (int) ($statsQuery?->pendientes ?? 0),
            'completadas' => (int) ($statsQuery?->completadas ?? 0),
            'canceladas' => (int) ($statsQuery?->canceladas ?? 0),
            'totalGastado' => $totalGastado,
        ];

        return Inertia::render('compras/Index', [
            'compras' => $compras,
            'stats' => $stats,
            'filters' => $request->only(['search', 'estado']),
        ]);
    }

    public function create(): Response
    {
        $proveedores = Proveedor::forCurrentCompany()->where('activo', true)->orderBy('nombre')->get();
        $productos = Producto::forCurrentCompany()->where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('compras/Create', [
            'proveedores' => $proveedores,
            'productos' => $productos,
        ]);
    }

    public function store(Request $request, StockService $stock): RedirectResponse
    {
        $validated = $request->validate([
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'proveedor_nombre' => 'required_without:proveedor_id|string|max:255',
            'tipo_pago' => 'nullable|string|max:255',
            'factura' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'fecha_compra' => 'required|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'nullable|exists:productos,id',
            'detalles.*.producto_nombre' => 'required_without:detalles.*.producto_id|string|max:255',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.costo_unitario' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $stock) {
            $total = 0;
            foreach ($validated['detalles'] as $item) {
                $total += $item['cantidad'] * $item['costo_unitario'];
            }

            $compra = Compra::create([
                'company_id' => auth()->user()->company_id,
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'proveedor_nombre' => $validated['proveedor_nombre'],
                'total' => $total,
                'estado' => 'pendiente',
                'tipo_pago' => $validated['tipo_pago'] ?? null,
                'factura' => $validated['factura'] ?? null,
                'notas' => $validated['notas'] ?? null,
                'fecha_compra' => $validated['fecha_compra'],
            ]);

            foreach ($validated['detalles'] as $item) {
                $subtotal = $item['cantidad'] * $item['costo_unitario'];

                CompraDetalle::create([
                    'company_id' => auth()->user()->company_id,
                    'compra_id' => $compra->id,
                    'producto_id' => $item['producto_id'] ?? null,
                    'producto_nombre' => $item['producto_nombre'],
                    'cantidad' => $item['cantidad'],
                    'costo_unitario' => $item['costo_unitario'],
                    'subtotal' => $subtotal,
                ]);

                if (! empty($item['producto_id'])) {
                    $producto = Producto::find($item['producto_id']);
                    if ($producto) {
                        $stock->entrada($producto, (int) $item['cantidad'], $compra);
                    }
                }
            }
        });

        return redirect()->route('compras.index')
            ->with('success', 'Compra registrada exitosamente.');
    }

    public function show(Compra $compra): Response
    {
        $compra->load(['detalles.producto', 'proveedor']);

        return Inertia::render('compras/Show', [
            'compra' => $compra,
        ]);
    }

    public function edit(Compra $compra): Response
    {
        $compra->load('detalles');

        $proveedores = Proveedor::forCurrentCompany()->where('activo', true)->orderBy('nombre')->get();
        $productos = Producto::forCurrentCompany()->where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('compras/Edit', [
            'compra' => $compra,
            'proveedores' => $proveedores,
            'productos' => $productos,
        ]);
    }

    public function update(Request $request, Compra $compra, StockService $stock): RedirectResponse
    {
        if (in_array($compra->estado, ['completada', 'cancelada'])) {
            throw ValidationException::withMessages([
                'compra' => 'No se puede modificar una compra '.$compra->estado.'.',
            ]);
        }

        $validated = $request->validate([
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'proveedor_nombre' => 'required_without:proveedor_id|string|max:255',
            'tipo_pago' => 'nullable|string|max:255',
            'factura' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'fecha_compra' => 'required|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'nullable|exists:productos,id',
            'detalles.*.producto_nombre' => 'required_without:detalles.*.producto_id|string|max:255',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.costo_unitario' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($compra, $validated, $stock) {
            $stock->restaurarProductosCompra($compra);
            $compra->detalles()->delete();

            $total = 0;
            foreach ($validated['detalles'] as $item) {
                $total += $item['cantidad'] * $item['costo_unitario'];
            }

            $compra->update([
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'proveedor_nombre' => $validated['proveedor_nombre'],
                'total' => $total,
                'tipo_pago' => $validated['tipo_pago'] ?? null,
                'factura' => $validated['factura'] ?? null,
                'notas' => $validated['notas'] ?? null,
                'fecha_compra' => $validated['fecha_compra'],
            ]);

            foreach ($validated['detalles'] as $item) {
                $subtotal = $item['cantidad'] * $item['costo_unitario'];

                CompraDetalle::create([
                    'company_id' => auth()->user()->company_id,
                    'compra_id' => $compra->id,
                    'producto_id' => $item['producto_id'] ?? null,
                    'producto_nombre' => $item['producto_nombre'],
                    'cantidad' => $item['cantidad'],
                    'costo_unitario' => $item['costo_unitario'],
                    'subtotal' => $subtotal,
                ]);

                if (! empty($item['producto_id'])) {
                    $producto = Producto::find($item['producto_id']);
                    if ($producto) {
                        $stock->entrada($producto, (int) $item['cantidad'], $compra);
                    }
                }
            }
        });

        return redirect()->route('compras.index')
            ->with('success', 'Compra actualizada exitosamente.');
    }

    public function updateEstado(Request $request, Compra $compra, StockService $stock): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,completada,cancelada',
        ]);

        $estadoAnterior = $compra->estado;

        if ($validated['estado'] === 'completada' && $estadoAnterior !== 'completada') {
            $compra->update(['estado' => 'completada']);
        } elseif ($validated['estado'] === 'cancelada' && $estadoAnterior !== 'cancelada') {
            DB::transaction(function () use ($compra, $stock) {
                $stock->restaurarProductosCompra($compra);
                $compra->update([
                    'estado' => 'cancelada',
                    'cancelado_en' => now(),
                    'cancelado_por' => auth()->user()?->name ?? 'Sistema',
                ]);
            });
        } else {
            $compra->update(['estado' => $validated['estado']]);
        }

        return redirect()->route('compras.index')
            ->with('success', "Compra #{$compra->id} marcada como {$validated['estado']}.");
    }

    /**
     * @throws ValidationException
     */
    public function destroy(Compra $compra, StockService $stock): RedirectResponse
    {
        if (in_array($compra->estado, ['completada', 'cancelada'])) {
            throw ValidationException::withMessages([
                'compra' => 'No se puede eliminar una compra '.$compra->estado.'.',
            ]);
        }

        DB::transaction(function () use ($compra, $stock) {
            $stock->restaurarProductosCompra($compra);
            $compra->delete();
        });

        return redirect()->route('compras.index')
            ->with('success', 'Compra eliminada exitosamente.');
    }
}
