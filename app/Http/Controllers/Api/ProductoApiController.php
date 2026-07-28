<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoApiController extends Controller
{
    public function __construct(
        protected ActivityLogger $logger,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $productos = Producto::forCurrentCompany()
            ->with(['variantes', 'imagenes'])
            ->when($request->search, fn ($q, $s) => $q->where('nombre', 'like', "%{$s}%"))
            ->when($request->categoria, fn ($q, $c) => $q->where('categoria', $c))
            ->when($request->activo !== null, fn ($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy($request->sort ?? 'nombre', $request->dir ?? 'asc')
            ->paginate($request->per_page ?? 20);

        return response()->json($productos);
    }

    public function show(Producto $producto): JsonResponse
    {
        $producto->load(['variantes', 'imagenes']);

        return response()->json($producto);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imagen' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        $producto = Producto::forCurrentCompany()->create($validated);

        $this->logger->created('productos', 'Producto creado via API', $producto);

        return response()->json($producto, 201);
    }

    public function update(Request $request, Producto $producto): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'imagen' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        $producto->update($validated);

        $this->logger->updated('productos', 'Producto actualizado via API', $producto);

        return response()->json($producto);
    }

    public function destroy(Producto $producto): JsonResponse
    {
        $producto->delete();

        $this->logger->deleted('productos', 'Producto eliminado via API', $producto);

        return response()->json(null, 204);
    }

    public function variantes(Producto $producto): JsonResponse
    {
        return response()->json($producto->variantes);
    }

    public function imagenes(Producto $producto): JsonResponse
    {
        return response()->json($producto->imagenes);
    }
}
