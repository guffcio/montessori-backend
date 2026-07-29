<?php

use App\Models\User;

test('authenticated user receives own data', function () {
    $user = User::factory()->create([
        'phone' => '+48123456789',
        'password' => 'password123!',
    ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123!',
    ]);

    $token = $loginResponse['access_token'];

    $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/me');

    $response->assertStatus(200);
    $response->assertJson([
        'email' => $user->email,
        'phone' => $user->phone,
        'role' => 'parent',
    ]);
});

test('guest receives 401', function () {
    $response = $this->getJson('/api/me');

    $response->assertStatus(401);
});
