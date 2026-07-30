<?php

test('authenticated user can logout', function () {
    $loginResponse = $this->postJson('/api/login', [
        'email' => $this->createParent()->user->email,
        'password' => $this->defaultPassword,
    ]);

    $token = $loginResponse['access_token'];

    $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")->post('/api/logout');

    $logoutResponse->assertStatus(200);
});

test('guest cannot logout', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401);
});
