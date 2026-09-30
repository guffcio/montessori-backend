<?php

test('authenticated user can refresh token', function (): void {

    $loginResponse = $this->postJson('/api/login', [
        'email' => $this->createParent()->user->email,
        'password' => $this->defaultPassword,
    ]);

    $token = $loginResponse['access_token'];

    $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/refresh');

    $response->assertStatus(200);
});

test('guest receives 401', function (): void {
    $response = $this->getJson('/api/me');

    $response->assertStatus(401);
});
