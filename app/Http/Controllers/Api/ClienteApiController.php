<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteApiController extends Controller
{
    public function __construct(
        protected ActivityLogger $logger,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $clientes = Cliente::forCurrentCompany()
            ->when($request->search, fn ($q, $s) => $q->where('nombre', 'like', "%{$s}%"))
            ->when($request->estado, fn ($q, $e) => $q->where('estado', $e))
            ->orderBy($request->sort ?? 'nombre')
            ->paginate($request->per_page ?? 20);

        return response()->json($clientes);
    }

    public function show(Cliente $cliente): JsonResponse
    {
        $cliente->load(['pedidos' => fn ($q) => $q->latest()->take(10)]);

        return response()->json([
            ...$cliente->toArray(),
            'total_gastado' => $cliente->total_gastado,
            'pedidos_count' => $cliente->pedidos()->count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
        ]);

        $cliente = Cliente::forCurrentCompany()->create($validated);

        $this->logger->created('clientes', 'Cliente creado via API', $cliente);

        return response()->json($cliente, 201);
    }

    public function update(Request $request, Cliente $cliente): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'sometimes|in:nuevo,frecuente,vip,inactivo,en_riesgo',
        ]);

        $cliente->update($validated);

        $this->logger->updated('clientes', 'Cliente actualizado via API', $cliente);

        return response()->json($cliente);
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        $cliente->delete();

        $this->logger->deleted('clientes', 'Cliente eliminado via API', $cliente);

        return response()->json(null, 204);
    }
}
