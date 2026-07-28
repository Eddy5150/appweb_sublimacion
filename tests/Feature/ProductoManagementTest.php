<?php

use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create products', function () {
    $this->seed();

    $admin = User::query()->where('email', 'saenzhenry150@gmail.com')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('productos.store'), [
            'nombre' => 'Taza mágica',
            'descripcion' => 'Taza con cambio de color',
            'precio' => 45.50,
            'categoria' => 'Tazas',
            'activo' => true,
            'stock' => 10,
        ])
        ->assertRedirect(route('productos.index'));

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Taza mágica',
        'categoria' => 'Tazas',
    ]);
});

test('cajera cannot access product management', function () {
    $this->seed();

    $cajera = User::query()->where('email', 'cajera@gmail.com')->firstOrFail();

    Producto::factory()->create();

    $this->actingAs($cajera)
        ->getJson(route('productos.index'))
        ->assertOk();

    $this->actingAs($cajera)
        ->postJson(route('productos.store'), [
            'nombre' => 'Producto no permitido',
            'precio' => 10,
            'activo' => true,
        ])
        ->assertForbidden();
});
