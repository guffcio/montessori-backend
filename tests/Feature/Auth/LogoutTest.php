<?php

use App\Models\User;

test('authenticated user can logout', function () {
    $user = User::factory()->create([
        'password' => 'password123!',
    ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123!',
    ]);

    $token = $loginResponse['access_token'];

    $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")->post('/api/logout');

    $logoutResponse->assertStatus(200);
});

test('guest cannot logout', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401);
});
