<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can access user management while cajera cannot', function () {
    $this->seed();

    $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
    $cajera = User::query()->where('email', 'cajera@gmail.com')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('usuarios.index'))
        ->assertOk();

    $this->actingAs($cajera)
        ->getJson(route('usuarios.index'))
        ->assertForbidden();
});

test('cajera can access pedidos and reportes', function () {
    $this->seed();

    $cajera = User::query()->where('email', 'cajera@gmail.com')->firstOrFail();

    $this->actingAs($cajera)
        ->get(route('pedidos.index'))
        ->assertOk();


    $this->actingAs($cajera)
        ->get(route('reportes'))
        ->assertOk();
});
