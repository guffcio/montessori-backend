<?php

use App\Models\ParentUser;
use App\Models\User;

test('admin can list parents', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'phone' => '+48123456789',
        'password' => 'password123!',
        'role' => 'admin',
    ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123!',
    ]);

    $token = $loginResponse['access_token'];

    ParentUser::factory()->count(10)->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/parents');

    expect($response['data'])->toHaveLength(10);
    $response->assertStatus(200);
});

test('parent cannot list all parents', function () {
    $user = User::factory()->create([
        'email' => 'parent@example.com',
        'phone' => '+48123456789',
        'password' => 'password123!',
    ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123!',
    ]);

    $token = $loginResponse['access_token'];

    $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/parents');

    $response->assertStatus(403);
});

test('guest receives 401', function () {
    $response = $this->getJson('/api/parents');

    $response->assertStatus(401);
});
