<?php

test('admin can delete menu', function (): void {
    $this->actingAsAdmin();

    $menu = $this->createMenu();

    $response = $this->deleteJson("/api/menus/{$menu->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('menus', [
        'id' => $menu->id,
    ]);
});

test('parent cannot delete menu', function (): void {
    $this->actingAsParent();

    $menu = $this->createMenu();

    $response = $this->deleteJson("/api/menus/{$menu->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
    ]);
});

test('guest receives 401', function (): void {
    $menu = $this->createMenu();
    $response = $this->deleteJson("/api/menus/{$menu->id}");
    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});
