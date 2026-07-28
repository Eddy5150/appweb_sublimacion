<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Company;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CatalogoController extends Controller
{
    protected array $imagenesPorCategoria = [
        'Poleras estampadas' => ['/imagen/Poleras estampadas.jpg', '/imagen/Poleras estampadas2.jpg', '/imagen/Poleras estampadas4.jpg', '/imagen/Poleras estampadas5.jpg', '/imagen/sublimacion.jpg'],
        'Poleras sublimadas deportivas' => ['/imagen/sublimacion5.jpg', '/imagen/sublimacion8.jpg', '/imagen/sublimacion9.jpg'],
        'Shorts deportivos sublimados' => ['/imagen/Shorts deportivos1.jpg', '/imagen/Shorts deportivos2.jpg', '/imagen/Shorts deportivos3.jpg', '/imagen/Shorts deportivos4.jpg', '/imagen/sublimacion6.jpg', '/imagen/sublimacion7.jpg'],
    ];

    protected string $imagenDefecto = '/imagen/sublimacion5.jpg';

    protected array $catInfo = [
        'Poleras estampadas' => ['icon' => '🎨', 'tag' => 'ESTAMPADO', 'color' => 'violet', 'desc' => 'Diseños exclusivos estampados en algodón premium'],
        'Poleras sublimadas deportivas' => ['icon' => '👕', 'tag' => 'POLERA', 'color' => 'cyan', 'desc' => 'Rendimiento profesional con sublimación total'],
        'Shorts deportivos sublimados' => ['icon' => '🩳', 'tag' => 'SHORT', 'color' => 'orange', 'desc' => 'Shorts deportivos con tecnología dry-fit'],
    ];

    public function index(): Response
    {
        $companyId = auth()->id()
            ? auth()->user()->company_id
            : optional(Company::first())->id;

        $productos = Producto::when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where('activo', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->map(function (Producto $producto) {
                $cat = $producto->categoria;

                $opciones = $this->imagenesPorCategoria[$cat] ?? [$this->imagenDefecto];
                $imagen = Arr::random($opciones);

                $info = $this->catInfo[$cat] ?? null;

                $tags = [];
                if ($producto->stock <= 10 && $producto->stock > 0) {
                    $tags[] = 'ÚLTIMOS';
                }
                if ($producto->stock === 0) {
                    $tags[] = 'AGOTADO';
                }
                if ($producto->precio > 140) {
                    $tags[] = 'PREMIUM';
                }

                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'categoria' => $cat,
                    'descripcion' => $producto->descripcion,
                    'precio' => (float) $producto->precio,
                    'stock' => $producto->stock,
                    'imagen' => $imagen,
                    'badge' => $info['tag'] ?? '',
                    'color' => $info['color'] ?? 'gray',
                    'tags' => $tags,
                ];
            });

        $stats = DB::table('productos')
            ->where('activo', true)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->selectRaw('categoria, COUNT(*) as total')
            ->groupBy('categoria')
            ->orderBy('categoria')
            ->get()
            ->keyBy('categoria')
            ->map(fn ($r) => $r->total);

        return Inertia::render('catalogo/Index', [
            'productos' => $productos,
            'stats' => $stats,
            'catInfo' => $this->catInfo,
            'pedidoCreado' => session('pedido_creado'),
        ]);
    }

    public function store(Request $request, StockService $stock): RedirectResponse
    {
        $companyId = auth()->id()
            ? auth()->user()->company_id
            : optional(Company::first())->id;

        $validated = $request->validate([
            'cliente_nombre' => 'required|string|max:255',
            'cliente_telefono' => 'nullable|string|max:20',
            'cliente_direccion' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer|exists:productos,id',
            'items.*.nombre' => 'required|string|max:255',
            'items.*.precio' => 'required|numeric|min:0',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        $pedido = DB::transaction(function () use ($validated, $companyId) {
            $clienteId = null;
            if ($telefono = $validated['cliente_telefono']) {
                $cliente = Cliente::firstOrCreate(
                    ['telefono' => $telefono, 'company_id' => $companyId],
                    ['nombre' => $validated['cliente_nombre'], 'estado' => 'nuevo', 'company_id' => $companyId],
                );
                $clienteId = $cliente->id;
            }

            $total = 0;
            $detallesData = [];
            foreach ($validated['items'] as $item) {
                $subtotal = $item['precio'] * $item['cantidad'];
                $total += $subtotal;
                $detallesData[] = [
                    'producto_id' => $item['producto_id'],
                    'producto_nombre' => $item['nombre'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $subtotal,
                ];
            }

            $pedido = Pedido::create([
                'company_id' => $companyId,
                'cliente_id' => $clienteId,
                'cliente_nombre' => $validated['cliente_nombre'],
                'cliente_telefono' => $validated['cliente_telefono'] ?? null,
                'cliente_direccion' => $validated['cliente_direccion'] ?? null,
                'total' => $total,
                'estado' => 'pendiente',
            ]);

            foreach ($detallesData as $data) {
                $data['pedido_id'] = $pedido->id;
                DetallePedido::create($data);

                $producto = Producto::forCompany($companyId)->find($data['producto_id']);
                if ($producto) {
                    $stock->salida($producto, (int) $data['cantidad'], $pedido);
                }
            }

            return $pedido;
        });

        return redirect()->route('catalogo')->with([
            'success' => "Pedido #{$pedido->id} confirmado — Bs ".number_format((float) $pedido->total, 2),
            'pedido_creado' => [
                'id' => $pedido->id,
                'total' => (float) $pedido->total,
                'cliente_nombre' => $pedido->cliente_nombre,
                'created_at' => $pedido->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }
}
