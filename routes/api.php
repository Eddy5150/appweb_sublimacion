<?php

use App\Http\Controllers\Api\CajaApiController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\PedidoApiController;
use App\Http\Controllers\Api\ProductoApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:60,1', 'tenant'])->prefix('v1')->name('api.')->group(function () {
    Route::get('dashboard/stats', [DashboardApiController::class, 'stats'])->name('dashboard.stats');
    Route::get('dashboard/ventas-mes', [DashboardApiController::class, 'ventasPorMes'])->name('dashboard.ventas-mes');
    Route::get('dashboard/top-productos', [DashboardApiController::class, 'topProductos'])->name('dashboard.top-productos');

    Route::apiResource('productos', ProductoApiController::class);
    Route::get('productos/{producto}/variantes', [ProductoApiController::class, 'variantes'])->name('productos.variantes');
    Route::get('productos/{producto}/imagenes', [ProductoApiController::class, 'imagenes'])->name('productos.imagenes');

    Route::apiResource('clientes', ClienteApiController::class);

    Route::apiResource('pedidos', PedidoApiController::class);
    Route::patch('pedidos/{pedido}/estado', [PedidoApiController::class, 'updateEstado'])->name('pedidos.estado');

    Route::get('caja/actual', [CajaApiController::class, 'actual'])->name('caja.actual');
    Route::post('caja/abrir', [CajaApiController::class, 'abrir'])->name('caja.abrir');
    Route::post('caja/cerrar', [CajaApiController::class, 'cerrar'])->name('caja.cerrar');
    Route::get('caja/movimientos', [CajaApiController::class, 'movimientos'])->name('caja.movimientos');
});
