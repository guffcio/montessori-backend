<?php

use App\Models\ParentUser;

test('admin can list parents', function () {
    $this->actingAsAdmin();

    ParentUser::factory()->count(10)->create();

    $response = $this->getJson('/api/parents');

    expect($response['data'])->toHaveLength(10);
    $response->assertStatus(200);
});

test('parent cannot list all parents', function () {
    $this->actingAsParent();

    $response = $this->getJson('/api/parents');

    $response->assertStatus(403);
});

test('guest receives 401', function () {
    $response = $this->getJson('/api/parents');

    $response->assertStatus(401);
});
