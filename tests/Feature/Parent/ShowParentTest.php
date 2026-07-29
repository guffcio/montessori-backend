<?php

use App\Models\ParentUser;
use App\Models\User;

test('admin can view parent', function () {
    $user = User::factory()->create([
        'password' => 'password123@',
        'role' => 'admin',
    ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123@',
    ]);

    $token = $loginResponse['access_token'];

    $parent = ParentUser::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/parents/{$parent->id}");
    $response->assertStatus(200);

});
test('owner can view own parent', function () {
    $user = User::factory()->create([
        'password' => 'parent123@',
    ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'parent123@',
    ]);

    $token = $loginResponse['access_token'];

    $parent = ParentUser::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/parents/{$parent->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'user_id' => $user->id,
            'id' => $parent->id,
        ],
    ]);

});
test('parent cannot view another parent', function () {
    $user = User::factory()->create([
        'password' => 'parent123@',
    ]);

    $secondUser = User::factory()->create();

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'parent123@',
    ]);

    $token = $loginResponse['access_token'];

    $parent = ParentUser::factory()->create([
        'user_id' => $secondUser->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/parents/{$parent->id}");

    $response->assertStatus(403);
});
test('guest receives 401', function () {
    $response = $this->getJson('/api/parents/1');
    $response->assertStatus(401);
});
test('return 404 for missing parent', function () {
    $user = User::factory()->create([
        'password' => 'password123@',
        'role' => 'admin',
    ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123@',
    ]);

    $token = $loginResponse['access_token'];
    $missingId = ParentUser::max('id') + 1;

    $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson("/api/parents/{$missingId}");
    $response->assertStatus(404);
});
