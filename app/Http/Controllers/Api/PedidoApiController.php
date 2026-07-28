<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoApiController extends Controller
{
    public function __construct(
        protected ActivityLogger $logger,
        protected StockService $stockService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $pedidos = Pedido::forCurrentCompany()
            ->with('cliente', 'detalles')
            ->when($request->estado, fn ($q, $e) => $q->where('estado', $e))
            ->when($request->search, fn ($q, $s) => $q->search($s))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json($pedidos);
    }

    public function show(Pedido $pedido): JsonResponse
    {
        $pedido->load(['cliente', 'detalles.producto']);

        return response()->json($pedido);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_nombre' => 'required_without:cliente_id|string|max:255',
            'cliente_telefono' => 'nullable|string|max:255',
            'cliente_direccion' => 'nullable|string|max:255',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        $total = collect($validated['detalles'])->sum(fn ($d) => $d['cantidad'] * $d['precio_unitario']);

        $pedido = Pedido::forCurrentCompany()->create([
            'cliente_id' => $validated['cliente_id'] ?? null,
            'cliente_nombre' => $validated['cliente_nombre'],
            'cliente_telefono' => $validated['cliente_telefono'] ?? null,
            'cliente_direccion' => $validated['cliente_direccion'] ?? null,
            'total' => $total,
            'estado' => 'pendiente',
        ]);

        foreach ($validated['detalles'] as $detalle) {
            $pedido->detalles()->create([
                'company_id' => auth()->user()->company_id,
                'producto_id' => $detalle['producto_id'],
                'producto_nombre' => $detalle['producto_nombre'] ?? '',
                'cantidad' => $detalle['cantidad'],
                'precio_unitario' => $detalle['precio_unitario'],
                'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario'],
            ]);
        }

        $this->stockService->procesarProductosVenta($pedido);
        $this->logger->created('pedidos', 'Pedido creado via API', $pedido);

        return response()->json($pedido->load('detalles'), 201);
    }

    public function updateEstado(Request $request, Pedido $pedido): JsonResponse
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,en_proceso,listo,entregado',
        ]);

        $pedido->update(['estado' => $validated['estado']]);

        $this->logger->updated('pedidos', "Estado cambiado a {$validated['estado']}", $pedido);

        return response()->json($pedido);
    }
}
