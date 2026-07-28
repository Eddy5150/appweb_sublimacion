<?php

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it synchronizes legacy roles into the pivot table', function () {
    $this->seed(RolesSeeder::class);

    $user = User::factory()->create([
        'role_legacy' => 'cajera',
    ]);

    $this->artisan('isinuta:sync-roles')
        ->assertSuccessful();

    expect($user->fresh()->hasRole('cajera'))->toBeTrue();
    expect($user->fresh()->roles()->where('nombre', 'cajera')->exists())->toBeTrue();
});

test('it can run in dry run mode without persisting changes', function () {
    $this->seed(RolesSeeder::class);

    $user = User::factory()->create([
        'role_legacy' => 'admin',
    ]);

    $this->artisan('isinuta:sync-roles --dry-run')
        ->assertSuccessful();

    expect($user->fresh()->roles()->exists())->toBeFalse();
});
