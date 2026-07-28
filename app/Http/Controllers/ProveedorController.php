<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProveedorController extends Controller
{
    public function index(Request $request): Response
    {
        $proveedores = Proveedor::forCurrentCompany()
            ->search($request->search)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Proveedor::forCurrentCompany()->count(),
            'activos' => Proveedor::forCurrentCompany()->where('activo', true)->count(),
            'nacionales' => Proveedor::forCurrentCompany()->where('tipo', 'nacional')->count(),
            'internacionales' => Proveedor::forCurrentCompany()->where('tipo', 'internacional')->count(),
        ];

        return Inertia::render('proveedores/Index', [
            'proveedores' => $proveedores,
            'stats' => $stats,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('proveedores/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'nit' => 'nullable|string|max:50',
            'tipo' => 'required|in:nacional,internacional',
            'notas' => 'nullable|string',
        ]);

        Proveedor::create($validated);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    public function show(Proveedor $proveedor): Response
    {
        $proveedor->load(['compras' => fn ($q) => $q->latest()->take(10)]);

        return Inertia::render('proveedores/Show', [
            'proveedor' => $proveedor,
            'compras' => $proveedor->compras,
            'totalCompras' => $proveedor->compras()->count(),
            'totalGastado' => (float) $proveedor->compras()->sum('total'),
        ]);
    }

    public function edit(Proveedor $proveedor): Response
    {
        return Inertia::render('proveedores/Edit', [
            'proveedor' => $proveedor,
        ]);
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'nit' => 'nullable|string|max:50',
            'tipo' => 'required|in:nacional,internacional',
            'notas' => 'nullable|string',
        ]);

        $proveedor->update($validated);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function toggleActivo(Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update(['activo' => ! $proveedor->activo]);

        return redirect()->route('proveedores.index')
            ->with('success', $proveedor->activo ? 'Proveedor activado.' : 'Proveedor desactivado.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        if ($proveedor->compras()->exists()) {
            return redirect()->route('proveedores.index')
                ->with('error', 'No se puede eliminar un proveedor con compras registradas.');
        }

        $proveedor->delete();

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado exitosamente.');
    }
}
