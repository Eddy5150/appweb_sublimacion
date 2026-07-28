<?php

test('public registration screen is not available', function () {
    $this->getJson('/register')->assertNotFound();
});

test('new users can not register publicly', function () {
    $this->postJson('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();
});
