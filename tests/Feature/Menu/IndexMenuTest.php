<?php

use App\Models\Menu;

test('admin can list all menus', function (): void {
    $this->actingAsAdmin();

    Menu::factory()->count(10)->create();

    $response = $this->getJson('/api/menus');

    $response->assertStatus(200);
    expect($response['data'])->toHaveLength(10);

});

test('parent can list all menus', function (): void {
    $this->actingAsParent();

    Menu::factory()->count(10)->create();

    $response = $this->getJson('/api/menus');

    $response->assertStatus(200);
    expect($response['data'])->toHaveLength(10);

});

test('guest receives 401', function (): void {
    $response = $this->getJson('/api/menus');

    $response->assertStatus(401);
});
