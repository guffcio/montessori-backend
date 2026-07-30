<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin can create parent', function () {
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

test('parent cannot create parent', function () {
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

test('guest cannot create parent', function () {
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

test('validation email required', function () {
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
    $response->assertJsonFragment(['The email field is required.']);
});

test('validation email unique', function () {
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
    $response->assertJsonFragment(['The email has already been taken.']);
});

test('password is hashed', function () {
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

test('user and parent are created', function () {
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
