<?php

use App\Models\Child;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin can create parent', function (): void {
    $this->actingAsAdmin();

    $response = $this->postJson('/api/parents', [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'password' => 'password123',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(201);
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

test('parent cannot create parent', function (): void {
    $this->actingAsParent();

    $response = $this->postJson('/api/parents', [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'password' => 'password123',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(403);
});

test('guest cannot create parent', function (): void {
    $response = $this->postJson('/api/parents', [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'password' => 'password123',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(401);
});

test('validation email required', function (): void {
    $this->actingAsAdmin();

    $response = $this->postJson('/api/parents', [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'phone' => '+48777666555',
        'password' => 'password123',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

test('validation email unique', function (): void {
    $user = $this->actingAsAdmin();

    $response = $this->postJson('/api/parents', [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => $user->email,
        'phone' => '+48777666555',
        'password' => 'password123',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

test('password is hashed', function (): void {
    $this->actingAsAdmin();

    $this->postJson('/api/parents', [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'password' => 'password123',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $parent = User::where('email', 'parent@example.com')->first();
    expect(Hash::isHashed($parent->password))->toBeTrue();
});

test('user and parent are created', function (): void {
    $this->actingAsAdmin();

    $response = $this->postJson('/api/parents', [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'password' => 'password123',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('users', [
        'email' => 'parent@example.com',
    ]);
    $this->assertDatabaseHas('parents', [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
    ]);
});

test('admin can assign existing children when creating parent', function (): void {
    $this->actingAsAdmin();

    $children = Child::factory()->count(5)->create();
    $childrenIds = $children->pluck('id');

    $response = $this->postJson('/api/parents', [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'password' => 'password123',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
        'children' => $childrenIds,
    ]);

    $response->assertStatus(201);

    foreach ($childrenIds as $childId) {
        $this->assertDatabaseHas('parent_child', [
            'parent_id' => $response['data']['id'],
            'child_id' => $childId,
        ]);
    }

    $this->assertDatabaseCount('parent_child', 5);

});

test('admin cannot assign non existing children when creating parent', function (): void {
    $this->actingAsAdmin();

    $childrenIds = [1, 2, 3, 4, 5];

    $response = $this->postJson('/api/parents', [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'parent@example.com',
        'phone' => '+48777666555',
        'password' => 'password123',
        'street' => 'Polyville Street',
        'house_number' => '18',
        'postal_code' => '20100',
        'city' => 'London',
        'children' => $childrenIds,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('children.0');
    $this->assertDatabaseCount('parent_child', 0);
});
