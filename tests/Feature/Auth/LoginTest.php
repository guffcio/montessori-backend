<?php

test('user can login', function (): void {

    $response = $this->postJson('/api/login', [
        'email' => $this->createParent()->user->email,
        'password' => $this->defaultPassword,
    ]);

    $response->assertStatus(200);
});

test('login fails with invalid password', function (): void {

    $response = $this->postJson('/api/login', [
        'email' => $this->createParent()->user->email,
        'password' => 'password123!',
    ]);

    $response->assertStatus(401);
});

test('login fails with unknown email', function (): void {

    $response = $this->postJson('/api/login', [
        'email' => 'user-fake@example.com',
        'password' => 'password123!',
    ]);

    $response->assertStatus(401);
});
