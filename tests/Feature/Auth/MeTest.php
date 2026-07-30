<?php

test('authenticated user receives own data', function () {
    $parent = $this->actingAsParent();

    $response = $this->getJson('/api/me');

    $response->assertStatus(200);
    $response->assertJson([
        'email' => $parent->user->email,
        'phone' => $parent->user->phone,
        'role' => 'parent',
    ]);
});

test('guest receives 401', function () {
    $response = $this->getJson('/api/me');

    $response->assertStatus(401);
});
