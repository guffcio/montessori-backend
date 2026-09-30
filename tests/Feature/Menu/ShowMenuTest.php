<?php

use App\Models\Menu;

test('admin can view menu', function (): void {
    $this->actingAsAdmin();

    $menu = $this->createMenu();

    $response = $this->getJson("/api/menus/{$menu->id}");
    $response->assertStatus(200);

});

test('parent can view menu', function (): void {
    $this->actingAsParent();

    $menu = $this->createMenu();

    $response = $this->getJson("/api/menus/{$menu->id}");
    $response->assertStatus(200);

});

test('guest receives 401', function (): void {
    $response = $this->getJson('/api/menus/1');
    $response->assertStatus(401);
});

test('return 404 for missing menu', function (): void {
    $this->actingAsAdmin();

    $missingId = Menu::max('id') + 1;

    $response = $this->getJson("/api/menus/{$missingId}");
    $response->assertStatus(404);
});
