<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get(route('panel'))->assertRedirect(route('login'));
});

test('authenticated admin users can visit the panel', function () {
    $this->seed();

    $user = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

    $this->actingAs($user)
        ->get(route('panel'))
        ->assertOk();
});
