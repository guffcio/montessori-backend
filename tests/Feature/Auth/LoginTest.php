<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => 'password123!',
    ]);
});

test('user can login', function () {

    $response = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => 'password123!',
    ]);

    $response->assertStatus(200);
});

test('login fails with invalid password', function () {

    $response = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(401);
});

test('login fails with unknown email', function () {

    $response = $this->postJson('/api/login', [
        'email' => 'user-fake@example.com',
        'password' => 'password123!',
    ]);

    $response->assertStatus(401);
});
