<?php

use App\Models\Child;

test('admin can update parent', function () {
    $this->actingAsAdmin();

    $parent = $this->createParent();

    $response = $this->patchJson("/api/parents/{$parent->id}", [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'parent@example.com',
            'phone' => '+48777666555',
            'street' => 'Polyville Street',
            'house_number' => '18',
            'postal_code' => '20100',
            'city' => 'London',
        ],
    ]);
});

test('owner can update own data', function () {
    $parent = $this->actingAsParent();

    $response = $this->patchJson("/api/parents/{$parent->id}", [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'parent@example.com',
            'phone' => '+48777666555',
            'street' => 'Polyville Street',
            'house_number' => '18',
            'postal_code' => '20100',
            'city' => 'London',
        ],
    ]);
});

test('parent cannot update another parent', function () {
    $this->actingAsParent();

    $secondParent = $this->createParent();

    $response = $this->patchJson("/api/parents/{$secondParent->id}", [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function () {
    $parent = $this->createParent();
    $response = $this->patchJson("/api/parents/{$parent->id}", [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('validation email unique', function () {
    $parent = $this->actingAsParent();

    $secondParent = $this->createParent();

    $response = $this->patchJson("/api/parents/{$parent->id}", [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => $secondParent->user->email,
        'phone' => '+48777666555',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

test('validation phone unique', function () {
    $parent = $this->actingAsParent();

    $secondParent = $this->createParent();

    $response = $this->patchJson("/api/parents/{$parent->id}", [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => $parent->user->email,
        'phone' => $secondParent->user->phone,
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('phone');
});

test('updates user table', function () {
    $parent = $this->actingAsParent();

    $response = $this->patchJson("/api/parents/{$parent->id}", [
        'first_name' => $parent->first_name,
        'last_name' => $parent->last_name,
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'street' => $parent->street,
        'house_number' => $parent->house_number,
        'postal_code' => $parent->postal_code,
        'city' => $parent->city,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', [
        'id' => $parent->user_id,
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
    ]);
});

test('updates parent table', function () {
    $parent = $this->actingAsParent();

    $response = $this->patchJson("/api/parents/{$parent->id}", [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => $parent->user->email,
        'phone' => $parent->user->phone,
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('parents', [
        'id' => $parent->id,
        'first_name' => 'John',
        'last_name' => 'Smith',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);
});

test('admin can replace assigned children', function () {
    $this->actingAsAdmin();
    $parent = $this->createParent();

    $children = Child::factory()->count(5)->create();
    $childrenIds = $children->pluck('id');
    $parent->children()->sync($childrenIds);

    $newChildren = Child::factory()->count(3)->create();
    $newChildrenIds = $newChildren->pluck('id');

    $response = $this->patchJson("/api/parents/{$parent->id}", [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
        'children' => $newChildrenIds,
    ]);

    $response->assertStatus(200);

    foreach ($newChildrenIds as $childId) {
        $this->assertDatabaseHas('parent_child', [
            'parent_id' => $parent->id,
            'child_id' => $childId,
        ]);
    }

    foreach ($childrenIds as $childId) {
        $this->assertDatabaseMissing('parent_child', [
            'parent_id' => $parent->id,
            'child_id' => $childId,
        ]);
    }

    $this->assertDatabaseCount('parent_child', 3);

});

test('admin cannot assign non existing children', function () {
    $this->actingAsAdmin();

    $parent = $this->createParent();

    $children = Child::factory()->count(5)->create();
    $childrenIds = $children->pluck('id');
    $parent->children()->sync($childrenIds);

    $newChildrenIds = [999, 1000, 1001];

    $response = $this->patchJson("/api/parents/{$parent->id}", [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
        'children' => $newChildrenIds,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('children.0');
    $this->assertDatabaseCount('parent_child', 5);
});

test('parent cannot assign children', function () {
    $parent = $this->actingAsParent();

    $children = Child::factory()->count(5)->create();
    $childrenIds = $children->pluck('id');

    $response = $this->patchJson("/api/parents/{$parent->id}", [
        'children' => $childrenIds,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('children');
    $this->assertDatabaseCount('parent_child', 0);
});
