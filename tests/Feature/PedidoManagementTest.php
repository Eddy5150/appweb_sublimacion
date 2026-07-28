<?php

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public users can create orders', function () {
    $producto = Producto::factory()->create([
        'precio' => 30.00,
        'activo' => true,
    ]);

    $this->post(route('pedidos.store'), [
        'cliente_nombre' => 'María López',
        'cliente_telefono' => '71234567',
        'cliente_direccion' => 'Villa Tunari',
        'estado' => 'pendiente',
        'detalles' => [
            [
                'producto_id' => $producto->id,
                'producto_nombre' => $producto->nombre,
                'cantidad' => 2,
                'precio_unitario' => 30.00,
                'detalles_diseno' => 'Nombre y foto frontal',
            ],
        ],
    ])->assertRedirect(route('pedidos.index'));

    $this->assertDatabaseHas('pedidos', [
        'cliente_nombre' => 'María López',
        'total' => 60.00,
        'estado' => 'pendiente',
    ]);

    $this->assertDatabaseHas('detalle_pedidos', [
        'producto_id' => $producto->id,
        'cantidad' => 2,
        'subtotal' => 60.00,
    ]);
});

test('cajera can update order status', function () {
    $this->seed();

    $cajera = User::query()->where('email', 'cajera@gmail.com')->firstOrFail();

    $producto = Producto::factory()->create(['precio' => 50.00]);
    $pedido = Pedido::create([
        'cliente_nombre' => 'Test',
        'total' => 100.00,
        'estado' => 'pendiente',
    ]);
    $pedido->detalles()->create([
        'producto_id' => $producto->id,
        'producto_nombre' => $producto->nombre,
        'cantidad' => 2,
        'precio_unitario' => 50.00,
        'subtotal' => 100.00,
    ]);

    $this->actingAs($cajera)
        ->put(route('pedidos.update', $pedido), [
            'cliente_nombre' => $pedido->cliente_nombre,
            'estado' => 'listo',
            'detalles' => $pedido->detalles->map(fn ($d) => [
                'producto_id' => $d->producto_id,
                'producto_nombre' => $d->producto_nombre,
                'cantidad' => $d->cantidad,
                'precio_unitario' => $d->precio_unitario,
                'detalles_diseno' => $d->detalles_diseno,
            ])->toArray(),
        ])
        ->assertRedirect(route('pedidos.index'));

    $this->assertDatabaseHas('pedidos', [
        'id' => $pedido->id,
        'estado' => 'listo',
    ]);
});
