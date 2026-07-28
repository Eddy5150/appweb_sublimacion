<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductoController extends Controller
{
    public function index(Request $request): Response
    {
        $activos = Producto::forCurrentCompany()->where('activo', true);

        $productos = Producto::forCurrentCompany()
            ->when($request->search, function ($query, $search) {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('categoria', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $stockBajo = (clone $activos)->where('stock', '>', 0)->where('stock', '<=', 10)->count();
        $agotados = (clone $activos)->where('stock', 0)->count();

        return Inertia::render('productos/Index', [
            'productos' => $productos,
            'filters' => $request->only('search'),
            'invStats' => [
                'total' => Producto::forCurrentCompany()->count(),
                'activos' => (clone $activos)->count(),
                'stockBajo' => $stockBajo,
                'agotados' => $agotados,
                'valor' => (float) (clone $activos)
                    ->selectRaw('COALESCE(SUM(stock * precio), 0) as total')
                    ->value('total'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('productos/Create', [
            'categorias' => Producto::forCurrentCompany()
                ->whereNotNull('categoria')
                ->distinct()
                ->orderBy('categoria')
                ->pluck('categoria')
                ->toArray(),
        ]);
    }

    public function store(StoreProductoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        Producto::create($data);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function show(Producto $producto): Response
    {
        return Inertia::render('productos/Show', [
            'producto' => $producto,
        ]);
    }

    public function edit(Producto $producto): Response
    {
        return Inertia::render('productos/Edit', [
            'producto' => $producto,
            'categorias' => Producto::forCurrentCompany()
                ->whereNotNull('categoria')
                ->distinct()
                ->orderBy('categoria')
                ->pluck('categoria')
                ->toArray(),
        ]);
    }

    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto->update($data);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
