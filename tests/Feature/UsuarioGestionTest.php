<?php

use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesSeeder::class);
    $this->seed(PermissionsSeeder::class);
});

function crearUsuarioConRol(string $rol): User
{
    $user = User::factory()->create([
        'role' => $rol,
        'email_verified_at' => now(),
    ]);

    $user->assignRole($rol);

    return $user;
}

test('admin puede acceder a la gestion de usuarios', function () {
    $admin = crearUsuarioConRol('admin');

    $this->actingAs($admin)
        ->get('/usuarios')
        ->assertOk();
});

test('cajera no puede acceder a la gestion de usuarios', function () {
    $cajera = crearUsuarioConRol('cajera');

    $this->actingAs($cajera)
        ->get('/usuarios')
        ->assertForbidden();
});
