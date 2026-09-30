<?php

test('admin can update menu', function (): void {
    $this->actingAsAdmin();

    $menu = $this->createMenu();

    $response = $this->patchJson("/api/menus/{$menu->id}", [
        'menu_date' => $menu->menu_date->nextWeekday()->toDateString(),
        'content' => 'Edit menu content',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'menu_date' => $menu->menu_date->nextWeekday()->toDateString(),
            'content' => 'Edit menu content',
        ],
    ]);
});

test('parent cannot update menu', function (): void {
    $this->actingAsParent();

    $menu = $this->createMenu();

    $response = $this->patchJson("/api/menus/{$menu->id}", [
        'menu_date' => $menu->menu_date->nextWeekday(),
        'content' => 'Edit menu content',
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function (): void {
    $menu = $this->createMenu();
    $response = $this->patchJson("/api/menus/{$menu->id}", [
        'menu_date' => $menu->menu_date->nextWeekday(),
        'content' => 'Edit menu content',
    ]);

    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('updates menus table', function (): void {
    $this->actingAsAdmin();

    $menu = $this->createMenu();

    $response = $this->patchJson("/api/menus/{$menu->id}", [
        'menu_date' => $menu->menu_date->nextWeekday(),
        'content' => 'Edit menu content',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'menu_date' => $menu->menu_date->nextWeekday(),
        'content' => 'Edit menu content',
    ]);
});

test('can update menu with the same date as itself', function (): void {
    $this->actingAsAdmin();

    $menu = $this->createMenu();

    $response = $this->patchJson("/api/menus/{$menu->id}", [
        'menu_date' => $menu->menu_date->toDateString(),
        'content' => 'Edit menu content',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'menu_date' => $menu->menu_date->toDateString(),
            'content' => 'Edit menu content',
        ],
    ]);
});

test('cannot update menu with the same date as other', function (): void {
    $this->actingAsAdmin();

    $menu = $this->createMenu();
    $secondMenu = $this->createMenu();

    $response = $this->patchJson("/api/menus/{$menu->id}", [
        'menu_date' => $secondMenu->menu_date->toDateString(),
        'content' => 'Edit menu content',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'menu_date',
    ]);
});
