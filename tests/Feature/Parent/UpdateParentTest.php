<?php

use App\Models\ParentUser;

test('admin can update parent', function () {
    $this->actingAsAdmin();

    $parent = $this->createParent();

    $response = $this->putJson("/api/parents/{$parent->id}", [
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

    $response = $this->putJson("/api/parents/{$parent->id}", [
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

    $response = $this->putJson("/api/parents/{$secondParent->id}", [
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
    $parent = ParentUser::factory()->create();
    $response = $this->putJson("/api/parents/{$parent->id}", [
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

    $response = $this->putJson("/api/parents/{$parent->id}", [
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
    $response->assertJsonFragment(['The email has already been taken.']);
});

test('validation phone unique', function () {
    $parent = $this->actingAsParent();

    $secondParent = $this->createParent();

    $response = $this->putJson("/api/parents/{$parent->id}", [
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
    $response->assertJsonFragment(['The phone has already been taken.']);
});

test('updates user table', function () {
    $parent = $this->actingAsParent();

    $response = $this->putJson("/api/parents/{$parent->id}", [
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

    $response = $this->putJson("/api/parents/{$parent->id}", [
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
