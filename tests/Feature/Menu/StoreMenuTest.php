<?php

test('admin can create menu', function () {
    $this->actingAsAdmin();

    $response = $this->postJson('/api/menus', [
        'menu_date' => today()->toDateString(),
        'content' => 'Test menu',
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'data' => [
            'menu_date' => today()->toDateString(),
            'content' => 'Test menu',
        ],
    ]);
});

test('parent cannot create menu', function () {
    $this->actingAsParent();

    $response = $this->postJson('/api/menus', [
        'menu_date' => today()->toDateString(),
        'content' => 'Test menu',
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
    $this->assertDatabaseMissing('menus', [
        'menu_date' => today()->toDateString(),
        'content' => 'Test menu',
    ]);
});

test('guest cannot create menu', function () {

    $response = $this->postJson('/api/menus', [
        'menu_date' => today()->toDateString(),
        'content' => 'Test menu',
    ]);

    $response->assertStatus(401);
});

test('required fields are validated', function () {
    $this->actingAsAdmin();

    $response = $this->postJson('/api/menus', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'menu_date',
        'content',
    ]);
});

test('cannot add other menu with the same date as exist', function () {
    $this->actingAsAdmin();

    $menu = $this->createMenu();

    $response = $this->postJson('/api/menus', [
        'menu_date' => $menu->menu_date,
        'content' => 'Test menu',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'menu_date',
    ]);
});

test('data send properly to database', function () {
    $this->actingAsAdmin();

    $response = $this->postJson('/api/menus', [
        'menu_date' => today()->toDateString(),
        'content' => 'Test menu',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('menus', [
        'menu_date' => today()->toDateString(),
        'content' => 'Test menu',
    ]);
});
