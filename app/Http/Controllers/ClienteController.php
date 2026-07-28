<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\CrmAiPrediction;
use App\Models\CrmAlert;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Services\CrmAiAgentService;
use App\Services\CrmAutomationService;
use App\Services\CrmAutoSalesService;
use App\Services\CrmEventLogger;
use App\Services\CrmService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    private function detallesSub()
    {
        return DetallePedido::query()
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->whereColumn('pedidos.cliente_id', 'clientes.id')
            ->selectRaw('COALESCE(SUM(detalle_pedidos.precio_unitario * detalle_pedidos.cantidad), 0)');
    }

    public function index(Request $request): Response
    {
        $clientes = Cliente::forCurrentCompany()
            ->select('clientes.*')
            ->selectSub($this->detallesSub(), 'total_detalles_sum')
            ->withCount('pedidos')
            ->withAggregate('pedidos', 'created_at', 'max')
            ->withAggregate('pedidos', 'created_at', 'min')
            ->search($request->search)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Cliente $cliente) => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
                'email' => $cliente->email,
                'estado' => $cliente->estado,
                'pedidos_count' => $cliente->pedidos_count,
                'total_gastado' => $cliente->total_gastado,
                'promedio_compra' => $cliente->promedio_compra,
                'ultima_compra' => $cliente->ultima_compra,
                'dias_ultima_compra' => $cliente->dias_ultima_compra,
                'tendencia' => $cliente->tendencia,
                'insight' => $cliente->insight,
            ]);

        return Inertia::render('clientes/Index', [
            'clientes' => $clientes,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('clientes/Create');
    }

    public function store(Request $request, CrmEventLogger $logger): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'estado' => 'nullable|in:nuevo,frecuente,vip,inactivo,en_riesgo',
        ]);

        $cliente = Cliente::create($validated);

        $logger->clienteCreado($cliente);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Cliente $cliente): Response
    {
        $cliente = Cliente::forCurrentCompany()
            ->where('clientes.id', $cliente->id)
            ->select('clientes.*')
            ->selectSub($this->detallesSub(), 'total_detalles_sum')
            ->withCount('pedidos')
            ->withAggregate('pedidos', 'created_at', 'max')
            ->withAggregate('pedidos', 'created_at', 'min')
            ->with('pedidos.detalles')
            ->firstOrFail();

        return Inertia::render('clientes/Show', [
            'cliente' => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
                'email' => $cliente->email,
                'direccion' => $cliente->direccion,
                'estado' => $cliente->estado,
                'pedidos_count' => $cliente->pedidos_count,
                'total_gastado' => $cliente->total_gastado,
                'promedio_compra' => $cliente->promedio_compra,
                'ultima_compra' => $cliente->ultima_compra,
                'primera_compra' => $cliente->primera_compra,
                'dias_ultima_compra' => $cliente->dias_ultima_compra,
                'frecuencia_promedio' => $cliente->frecuencia_promedio,
                'tendencia' => $cliente->tendencia,
                'insight' => $cliente->insight,
                'riesgo_score' => $cliente->riesgo_score,
                'probabilidad_retorno' => $cliente->probabilidad_retorno,
                'created_at' => $cliente->created_at->format('Y-m-d H:i:s'),
                'pedidos' => $cliente->pedidos->map(fn ($p) => [
                    'id' => $p->id,
                    'total' => $p->total,
                    'estado' => $p->estado,
                    'created_at' => $p->created_at->format('Y-m-d H:i:s'),
                    'detalles' => $p->detalles->map(fn ($d) => [
                        'producto_nombre' => $d->producto_nombre,
                        'cantidad' => $d->cantidad,
                    ]),
                ]),
            ],
        ]);
    }

    public function edit(Cliente $cliente): Response
    {
        return Inertia::render('clientes/Edit', [
            'cliente' => $cliente,
        ]);
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'estado' => 'nullable|in:nuevo,frecuente,vip,inactivo,en_riesgo',
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    public function crm(CrmAutomationService $automation, CrmAiAgentService $agent, CrmAutoSalesService $sales): Response
    {
        $autoResult = $automation->procesarAutomatico();

        $detallesSub = $this->detallesSub();

        $totalVentas = (float) Pedido::forCurrentCompany()
            ->join('detalle_pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->sum(DB::raw('detalle_pedidos.precio_unitario * detalle_pedidos.cantidad'));

        $totalPedidos = Pedido::forCurrentCompany()->count();
        $totalClientes = Cliente::forCurrentCompany()->count();
        $clientesConPedidos = Cliente::forCurrentCompany()->whereHas('pedidos')->count();
        $promedioTicket = $totalPedidos > 0
            ? round($totalVentas / $totalPedidos, 2)
            : 0;

        $ingresosPorSegmento = Cliente::forCurrentCompany()
            ->select('clientes.estado')
            ->selectSub(
                DetallePedido::query()
                    ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
                    ->whereColumn('pedidos.cliente_id', 'clientes.id')
                    ->selectRaw('COALESCE(SUM(detalle_pedidos.precio_unitario * detalle_pedidos.cantidad), 0)'),
                'total'
            )
            ->whereHas('pedidos')
            ->get()
            ->groupBy('estado')
            ->map(fn ($grupo) => round((float) $grupo->sum('total'), 2));

        $topClientes = Cliente::forCurrentCompany()
            ->select('clientes.*')
            ->selectSub($detallesSub, 'total_detalles_sum')
            ->withCount('pedidos')
            ->withAggregate('pedidos', 'created_at', 'max')
            ->withAggregate('pedidos', 'created_at', 'min')
            ->whereHas('pedidos')
            ->orderByDesc('total_detalles_sum')
            ->take(5)
            ->get()
            ->map(fn (Cliente $cliente) => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'total_gastado' => $cliente->total_gastado,
                'pedidos_count' => $cliente->pedidos_count,
                'promedio_compra' => $cliente->promedio_compra,
                'ultima_compra' => $cliente->ultima_compra,
                'estado' => $cliente->estado,
                'tendencia' => $cliente->tendencia,
                'insight' => $cliente->insight,
                'dias_ultima_compra' => $cliente->dias_ultima_compra,
                'probabilidad_retorno' => $cliente->probabilidad_retorno,
                'riesgo_score' => $cliente->riesgo_score,
            ]);

        $clienteMasRentable = $topClientes->first();

        $clientesEnRiesgo = Cliente::forCurrentCompany()
            ->select('clientes.*')
            ->selectSub($detallesSub, 'total_detalles_sum')
            ->withCount('pedidos')
            ->withAggregate('pedidos', 'created_at', 'max')
            ->withAggregate('pedidos', 'created_at', 'min')
            ->where('estado', 'en_riesgo')
            ->orderByDesc('total_detalles_sum')
            ->take(5)
            ->get()
            ->map(fn (Cliente $cliente) => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'total_gastado' => $cliente->total_gastado,
                'pedidos_count' => $cliente->pedidos_count,
                'ultima_compra' => $cliente->ultima_compra,
                'dias_ultima_compra' => $cliente->dias_ultima_compra,
                'riesgo_score' => $cliente->riesgo_score,
                'probabilidad_retorno' => $cliente->probabilidad_retorno,
                'insight' => $cliente->insight,
            ]);

        $clientesEnCrecimiento = Cliente::forCurrentCompany()
            ->select('clientes.*')
            ->selectSub($detallesSub, 'total_detalles_sum')
            ->withCount('pedidos')
            ->withAggregate('pedidos', 'created_at', 'max')
            ->withAggregate('pedidos', 'created_at', 'min')
            ->has('pedidos', '>=', 2)
            ->get()
            ->filter(fn (Cliente $cliente) => $cliente->tendencia === 'sube')
            ->sortByDesc('total_gastado')
            ->take(5)
            ->values()
            ->map(fn (Cliente $cliente) => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'total_gastado' => $cliente->total_gastado,
                'pedidos_count' => $cliente->pedidos_count,
                'tendencia' => $cliente->tendencia,
                'insight' => $cliente->insight,
            ]);

        $segmentos = [
            'vip' => Cliente::forCurrentCompany()->segmento('vip')->count(),
            'frecuente' => Cliente::forCurrentCompany()->segmento('frecuente')->count(),
            'nuevo' => Cliente::forCurrentCompany()->segmento('nuevo')->count(),
            'inactivo' => Cliente::forCurrentCompany()->segmento('inactivo')->count(),
            'en_riesgo' => Cliente::forCurrentCompany()->segmento('en_riesgo')->count(),
        ];

        $activeClients = Cliente::forCurrentCompany()->segmento('vip')->count()
            + Cliente::forCurrentCompany()->segmento('frecuente')->count()
            + Cliente::forCurrentCompany()->segmento('nuevo')->count();

        $inactiveClients = Cliente::forCurrentCompany()->segmento('inactivo')->count()
            + Cliente::forCurrentCompany()->segmento('en_riesgo')->count();

        $ventasPorMes = Pedido::forCurrentCompany()
            ->join('detalle_pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->selectRaw('YEAR(pedidos.created_at) as year, MONTH(pedidos.created_at) as month, SUM(detalle_pedidos.precio_unitario * detalle_pedidos.cantidad) as total')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->take(12)
            ->get()
            ->map(fn ($v) => [
                'periodo' => "{$v->year}-".str_pad($v->month, 2, '0', STR_PAD_LEFT),
                'total' => round((float) $v->total, 2),
            ])
            ->reverse()
            ->values();

        $pedidosPorEstado = Pedido::forCurrentCompany()->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->get()
            ->map(fn ($p) => [
                'estado' => $p->estado,
                'total' => $p->total,
            ]);

        $aiStats = [
            'avg_score' => round(CrmAiPrediction::forCurrentCompany()->avg('ai_score') ?? 0, 1),
            'happy_count' => CrmAiPrediction::forCurrentCompany()->where('emotional_state', 'feliz')->count(),
            'at_risk_count' => CrmAiPrediction::forCurrentCompany()->where('emotional_state', 'en_riesgo')->count(),
            'high_churn' => CrmAiPrediction::forCurrentCompany()->where('churn_risk', 'alta')->count(),
            'total_predicted' => CrmAiPrediction::forCurrentCompany()->count(),
            'total_ltv' => round(CrmAiPrediction::forCurrentCompany()->sum('ltv_estimated'), 2),
        ];

        $alertasUrgentes = CrmAlert::forCurrentCompany()->with('cliente')
            ->where('leida', false)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn (CrmAlert $a) => [
                'id' => $a->id,
                'cliente_id' => $a->cliente_id,
                'cliente_nombre' => $a->cliente?->nombre,
                'tipo' => $a->tipo,
                'titulo' => $a->titulo,
                'mensaje' => $a->mensaje,
                'created_at' => $a->created_at->format('Y-m-d H:i:s'),
            ]);

        $clientesCalientes = CrmAiPrediction::forCurrentCompany()->with('cliente')
            ->orderByDesc('purchase_probability')
            ->take(5)
            ->get()
            ->map(fn (CrmAiPrediction $p) => [
                'cliente_id' => $p->cliente_id,
                'cliente_nombre' => $p->cliente?->nombre,
                'purchase_probability' => $p->purchase_probability,
                'estimated_next_purchase' => $p->estimated_next_purchase?->format('Y-m-d'),
                'emotional_state' => $p->emotional_state,
                'ltv_estimated' => $p->ltv_estimated,
                'ai_score' => $p->ai_score,
            ]);

        $candidatosVip = CrmAiPrediction::forCurrentCompany()->with('cliente')
            ->whereHas('cliente', fn ($q) => $q->where('estado', '!=', 'vip'))
            ->where('ltv_estimated', '>=', 500)
            ->orderByDesc('ltv_estimated')
            ->take(5)
            ->get()
            ->map(fn (CrmAiPrediction $p) => [
                'cliente_id' => $p->cliente_id,
                'cliente_nombre' => $p->cliente?->nombre,
                'total_gastado' => (float) ($p->cliente?->total_gastado ?? 0),
                'pedidos_count' => $p->cliente?->pedidos_count ?? 0,
                'ltv_estimated' => $p->ltv_estimated,
                'ai_score' => $p->ai_score,
                'churn_risk' => $p->churn_risk,
            ]);

        return Inertia::render('crm/Index', [
            'metrics' => [
                'totalVentas' => round($totalVentas, 2),
                'clientesActivos' => $activeClients,
                'clientesInactivos' => $inactiveClients,
                'totalClientes' => $totalClientes,
                'totalPedidos' => $totalPedidos,
                'tasaActividad' => $totalClientes > 0
                    ? round(($clientesConPedidos / $totalClientes) * 100, 1)
                    : 0,
                'promedioTicket' => $promedioTicket,
            ],
            'topClientes' => $topClientes,
            'clienteMasRentable' => $clienteMasRentable,
            'clientesEnRiesgo' => $clientesEnRiesgo,
            'clientesEnCrecimiento' => $clientesEnCrecimiento,
            'segmentos' => $segmentos,
            'ingresosPorSegmento' => $ingresosPorSegmento,
            'ventasPorMes' => $ventasPorMes,
            'pedidosPorEstado' => $pedidosPorEstado,
            'aiStats' => $aiStats,
            'alertasUrgentes' => $alertasUrgentes,
            'clientesCalientes' => $clientesCalientes,
            'candidatosVip' => $candidatosVip,
            'automationResult' => $autoResult,
            'agentClientsToContact' => $agent->getClientsToContact(5),
            'agentStats' => $agent->getAgentStats(),
            'salesStats' => $sales->getSalesStats(),
            'salesPending' => $sales->getPendingActions(5),
        ]);
    }

    public function clasificar(Request $request, CrmService $crm): RedirectResponse
    {
        $actualizados = $crm->clasificarClientes();

        return redirect()->route('crm')
            ->with('success', "Segmentación completada: {$actualizados} clientes actualizados.");
    }

    public function runAutomation(CrmAutomationService $automation): RedirectResponse
    {
        $resultados = $automation->procesarAutomatico();

        $total = array_sum($resultados);

        return redirect()->route('crm')
            ->with('success', "Automatización ejecutada: {$total} acciones ({$resultados['reenganche']} re-enganche, {$resultados['warnings']} warnings, {$resultados['vip_upgrades']} VIP, {$resultados['caidas']} caídas).");
    }

    public function runAgent(CrmAiAgentService $agent): RedirectResponse
    {
        $count = $agent->processAllClients();

        return redirect()->route('crm')
            ->with('success', "Agente IA ejecutado: {$count} clientes analizados con recomendaciones generadas.");
    }

    public function agentRecommendations(CrmAiAgentService $agent): array
    {
        return [
            'recommendations' => $agent->getAgentRecommendations(10),
            'stats' => $agent->getAgentStats(),
        ];
    }
}
