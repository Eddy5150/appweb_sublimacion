<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompanyBillingController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\CrmAiController;
use App\Http\Controllers\CrmAutomationController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaasController;
use App\Http\Controllers\UsuarioController;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');
Route::redirect('/dashboard', '/panel');

Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');
Route::post('/carrito/confirmar', [CatalogoController::class, 'store'])->name('carrito.confirmar');
Route::get('/pedidos/create', [PedidoController::class, 'create'])->name('pedidos.create');
Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store');

Route::middleware(['auth', 'verified', 'role:admin,cajera,vendedor,operador'])->group(function () {
    Route::get('/panel', function () {
        $companyId = auth()->user()->company_id;
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();
        $inicioHoy = now()->startOfDay();
        $finHoy = now()->endOfDay();

        $productosActivos = Producto::forCurrentCompany()->where('activo', true);
        $umbralStockBajo = 10;

        $pedidosQuery = Pedido::forCurrentCompany()->join('detalle_pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id');

        $ventasHoy = (float) (clone $pedidosQuery)
            ->whereBetween('pedidos.created_at', [$inicioHoy, $finHoy])
            ->whereIn('pedidos.estado', ['entregado', 'listo'])
            ->sum(DB::raw('detalle_pedidos.precio_unitario * detalle_pedidos.cantidad'));

        $ventasMes = (float) (clone $pedidosQuery)
            ->whereBetween('pedidos.created_at', [$inicioMes, $finMes])
            ->whereIn('pedidos.estado', ['entregado', 'listo'])
            ->sum(DB::raw('detalle_pedidos.precio_unitario * detalle_pedidos.cantidad'));

        $topProductos = (clone $pedidosQuery)
            ->selectRaw('detalle_pedidos.producto_nombre, SUM(detalle_pedidos.cantidad) as total, SUM(detalle_pedidos.precio_unitario * detalle_pedidos.cantidad) as ingresos')
            ->groupBy('detalle_pedidos.producto_nombre')
            ->orderByDesc('total')
            ->whereBetween('pedidos.created_at', [$inicioMes, $finMes])
            ->take(5)
            ->get();

        $pedidosRecientes = Pedido::forCurrentCompany()->with('cliente')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'cliente_nombre' => $p->cliente_nombre,
                'total' => (float) $p->total,
                'estado' => $p->estado,
                'created_at' => $p->created_at->format('d/m/Y H:i'),
            ]);

        $totalComprasPendientes = Compra::forCurrentCompany()->where('estado', 'pendiente')->count();
        $totalProveedores = Proveedor::forCurrentCompany()->count();

        return Inertia::render('panel/Index', [
            'stats' => [
                'totalProductos' => Producto::forCurrentCompany()->count(),
                'productosActivos' => $productosActivos->count(),
                'pedidosPendientes' => Pedido::forCurrentCompany()->where('estado', 'pendiente')->count(),
                'ventasHoy' => $ventasHoy,
                'ventasMes' => $ventasMes,
                'totalClientes' => Cliente::forCurrentCompany()->count(),
                'stockBajo' => (clone $productosActivos)->where('stock', '>', 0)->where('stock', '<=', $umbralStockBajo)->count(),
                'stockAgotado' => (clone $productosActivos)->where('stock', 0)->count(),
                'valorInventario' => (float) (clone $productosActivos)
                    ->selectRaw('SUM(stock * precio) as total')
                    ->value('total') ?? 0,
                'comprasPendientes' => $totalComprasPendientes,
                'totalProveedores' => $totalProveedores,
            ],
            'topProductos' => $topProductos,
            'pedidosRecientes' => $pedidosRecientes,
        ]);
    })->middleware('permission:panel.ver')->name('panel');

    Route::get('/pedidos', [PedidoController::class, 'index'])
        ->middleware('permission:pedidos.ver')
        ->name('pedidos.index');
    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])
        ->middleware('permission:pedidos.ver')
        ->name('pedidos.show');
    Route::get('/pedidos/{pedido}/edit', [PedidoController::class, 'edit'])
        ->middleware('permission:pedidos.gestionar')
        ->name('pedidos.edit');
    Route::match(['put', 'patch'], '/pedidos/{pedido}', [PedidoController::class, 'update'])
        ->middleware('permission:pedidos.gestionar')
        ->name('pedidos.update');
    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'updateEstado'])
        ->middleware('permission:pedidos.gestionar')
        ->name('pedidos.estado');
    Route::delete('/pedidos/{pedido}', [PedidoController::class, 'destroy'])
        ->middleware(['permission:pedidos.gestionar', 'role:admin'])
        ->name('pedidos.destroy');

    Route::get('/productos', [ProductoController::class, 'index'])
        ->middleware('permission:productos.ver')
        ->name('productos.index');
    Route::get('/productos/create', [ProductoController::class, 'create'])
        ->middleware('permission:productos.gestionar')
        ->name('productos.create');
    Route::post('/productos', [ProductoController::class, 'store'])
        ->middleware('permission:productos.gestionar')
        ->name('productos.store');
    Route::get('/productos/{producto}', [ProductoController::class, 'show'])
        ->middleware('permission:productos.ver')
        ->name('productos.show');
    Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit'])
        ->middleware('permission:productos.gestionar')
        ->name('productos.edit');
    Route::match(['put', 'patch'], '/productos/{producto}', [ProductoController::class, 'update'])
        ->middleware('permission:productos.gestionar')
        ->name('productos.update');
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])
        ->middleware('permission:productos.gestionar')
        ->name('productos.destroy');

    Route::get('/reportes', [ReporteController::class, 'index'])
        ->middleware('permission:reportes.ver')
        ->name('reportes');
    Route::get('/reportes/ventas-fecha', [ReporteController::class, 'ventasPorFecha'])
        ->middleware('permission:reportes.ver')
        ->name('reportes.ventas-fecha');
    Route::get('/reportes/productos-top', [ReporteController::class, 'productosMasVendidos'])
        ->middleware('permission:reportes.ver')
        ->name('reportes.productos-top');
    Route::get('/reportes/estados', [ReporteController::class, 'pedidosPorEstado'])
        ->middleware('permission:reportes.ver')
        ->name('reportes.estados');

    Route::get('/reportes/exportar-pdf', [ReporteController::class, 'exportarPdf'])
        ->middleware('permission:reportes.ver')
        ->name('reportes.exportar-pdf');
    Route::get('/reportes/exportar-excel', [ReporteController::class, 'exportarExcel'])
        ->middleware('permission:reportes.ver')
        ->name('reportes.exportar-excel');

    Route::resource('roles', RoleController::class)
        ->middleware('permission:usuarios.gestionar');

    Route::get('/crm', [ClienteController::class, 'crm'])
        ->name('crm');
    Route::post('/clientes/clasificar', [ClienteController::class, 'clasificar'])
        ->name('clientes.clasificar');

    Route::get('/crm/automations', [CrmAutomationController::class, 'reglasIndex'])->name('crm.automations');
    Route::post('/crm/automations', [CrmAutomationController::class, 'reglasStore'])->name('crm.automations.store');
    Route::put('/crm/automations/{regla}', [CrmAutomationController::class, 'reglasUpdate'])->name('crm.automations.update');
    Route::delete('/crm/automations/{regla}', [CrmAutomationController::class, 'reglasDestroy'])->name('crm.automations.destroy');
    Route::post('/crm/automations/{regla}/toggle', [CrmAutomationController::class, 'reglasToggle'])->name('crm.automations.toggle');

    Route::get('/crm/alerts', [CrmAutomationController::class, 'alertsIndex'])->name('crm.alerts');
    Route::post('/crm/alerts/{alert}/read', [CrmAutomationController::class, 'alertsMarkRead'])->name('crm.alerts.read');
    Route::post('/crm/alerts/read-all', [CrmAutomationController::class, 'alertsMarkAllRead'])->name('crm.alerts.read-all');

    Route::get('/crm/events', [CrmAutomationController::class, 'eventsIndex'])->name('crm.events');
    Route::post('/crm/automation/run', [ClienteController::class, 'runAutomation'])->name('crm.automation.run');

    Route::get('/crm/comunicaciones', [CrmAutomationController::class, 'comunicacionesIndex'])->name('crm.comunicaciones');

    Route::get('/crm/sales/actions', [CrmAutomationController::class, 'salesActions'])->name('crm.sales.actions');
    Route::post('/crm/sales/actions/{action}/approve', [CrmAutomationController::class, 'salesApprove'])->name('crm.sales.approve');
    Route::post('/crm/sales/actions/{action}/execute', [CrmAutomationController::class, 'salesExecute'])->name('crm.sales.execute');
    Route::post('/crm/sales/pipeline', [CrmAutomationController::class, 'salesPipeline'])->name('crm.sales.pipeline');

    Route::get('/crm/ai', [CrmAiController::class, 'index'])->name('crm.ai');
    Route::post('/crm/ai/procesar', [CrmAiController::class, 'procesar'])->name('crm.ai.procesar');
    Route::post('/crm/ai/cliente/{cliente}', [CrmAiController::class, 'procesarCliente'])->name('crm.ai.cliente');
    Route::post('/crm/ai/cliente/{cliente}/recomendaciones', [CrmAiController::class, 'recomendaciones'])->name('crm.ai.recomendaciones');
    Route::get('/crm/ai/contactar-hoy', [CrmAiController::class, 'contactarHoy'])->name('crm.ai.contactar');
    Route::get('/crm/ai/cliente/{cliente}/insight', [CrmAiController::class, 'insightCliente'])->name('crm.ai.insight');

    Route::post('/crm/ai/agent/run', [ClienteController::class, 'runAgent'])->name('crm.ai.agent.run');
    Route::get('/crm/ai/agent/recommendations', [ClienteController::class, 'agentRecommendations'])->name('crm.ai.agent.recommendations');

    Route::resource('clientes', ClienteController::class);

    Route::patch('usuarios/{usuario}/toggle-estado', [UsuarioController::class, 'toggleEstado'])
        ->name('usuarios.toggle-estado')
        ->middleware('permission:usuarios.gestionar');

    Route::resource('usuarios', UsuarioController::class)
        ->except(['show'])
        ->middleware('permission:usuarios.gestionar');

    Route::get('/proveedores', [ProveedorController::class, 'index'])
        ->name('proveedores.index');
    Route::get('/proveedores/create', [ProveedorController::class, 'create'])
        ->name('proveedores.create');
    Route::post('/proveedores', [ProveedorController::class, 'store'])
        ->name('proveedores.store');
    Route::get('/proveedores/{proveedor}', [ProveedorController::class, 'show'])
        ->name('proveedores.show');
    Route::get('/proveedores/{proveedor}/edit', [ProveedorController::class, 'edit'])
        ->name('proveedores.edit');
    Route::match(['put', 'patch'], '/proveedores/{proveedor}', [ProveedorController::class, 'update'])
        ->name('proveedores.update');
    Route::post('/proveedores/{proveedor}/toggle', [ProveedorController::class, 'toggleActivo'])
        ->name('proveedores.toggle');
    Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])
        ->name('proveedores.destroy');

    Route::get('/compras', [CompraController::class, 'index'])
        ->name('compras.index');
    Route::get('/compras/create', [CompraController::class, 'create'])
        ->name('compras.create');
    Route::post('/compras', [CompraController::class, 'store'])
        ->name('compras.store');
    Route::get('/compras/{compra}', [CompraController::class, 'show'])
        ->name('compras.show');
    Route::get('/compras/{compra}/edit', [CompraController::class, 'edit'])
        ->name('compras.edit');
    Route::match(['put', 'patch'], '/compras/{compra}', [CompraController::class, 'update'])
        ->name('compras.update');
    Route::patch('/compras/{compra}/estado', [CompraController::class, 'updateEstado'])
        ->name('compras.estado');
    Route::delete('/compras/{compra}', [CompraController::class, 'destroy'])
        ->name('compras.destroy');

    Route::get('/configuracion/empresa', [CompanySettingsController::class, 'edit'])
        ->name('company.settings');
    Route::put('/configuracion/empresa', [CompanySettingsController::class, 'update'])
        ->name('company.settings.update');

    Route::get('/configuracion/facturacion', [CompanyBillingController::class, 'index'])
        ->name('company.billing');
    Route::post('/configuracion/plan/upgrade', [CompanyBillingController::class, 'upgrade'])
        ->name('company.subscription.upgrade');
    Route::post('/configuracion/plan/cancel', [CompanyBillingController::class, 'cancel'])
        ->name('company.subscription.cancel');

    Route::get('/caja', [CajaController::class, 'index'])
        ->middleware('permission:caja.ver')
        ->name('caja.index');
    Route::post('/caja/abrir', [CajaController::class, 'abrir'])
        ->middleware('permission:caja.gestionar')
        ->name('caja.abrir');
    Route::post('/caja/cerrar', [CajaController::class, 'cerrar'])
        ->middleware('permission:caja.gestionar')
        ->name('caja.cerrar');
    Route::get('/caja/{cajaApertura}/movimientos', [CajaController::class, 'movimientos'])
        ->middleware('permission:caja.ver')
        ->name('caja.movimientos');

    Route::get('/actividad', [ActivityLogController::class, 'index'])
        ->middleware('permission:auditoria.ver')
        ->name('activity.index');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('saas')->name('saas.')->group(function () {
    Route::get('/', [SaasController::class, 'dashboard'])->name('dashboard');
    Route::get('/empresas', [SaasController::class, 'companies'])->name('companies');
    Route::patch('/empresas/{company}', [SaasController::class, 'updateCompany'])->name('companies.update');
    Route::post('/empresas/{company}/toggle', [SaasController::class, 'toggleCompany'])->name('companies.toggle');
    Route::delete('/empresas/{company}', [SaasController::class, 'deleteCompany'])->name('companies.destroy');
    Route::get('/planes', [SaasController::class, 'plans'])->name('plans');
    Route::post('/planes', [SaasController::class, 'storePlan'])->name('plans.store');
    Route::put('/planes/{plan}', [SaasController::class, 'updatePlan'])->name('plans.update');
    Route::post('/planes/{plan}/toggle', [SaasController::class, 'togglePlan'])->name('plans.toggle');
    Route::get('/suscripciones', [SaasController::class, 'subscriptions'])->name('subscriptions');
    Route::post('/suscripciones', [SaasController::class, 'storeSubscription'])->name('subscriptions.store');
});

require __DIR__.'/settings.php';
