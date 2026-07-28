<?php

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('reportes page and endpoints return data for authorized users', function () {
    $this->seed();

    $admin = User::query()->where('email', 'saenzhenry150@gmail.com')->firstOrFail();
    Pedido::factory()->withDetalles(2)->count(3)->create();

    $this->actingAs($admin)
        ->get(route('reportes'))
        ->assertOk();

    $this->actingAs($admin)
        ->getJson(route('reportes.ventas-fecha'))
        ->assertOk()
        ->assertJsonStructure(['labels', 'series', 'total']);

    $this->actingAs($admin)
        ->getJson(route('reportes.productos-top'))
        ->assertOk();

    $this->actingAs($admin)
        ->getJson(route('reportes.estados'))
        ->assertOk();
});
