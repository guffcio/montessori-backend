<?php

use App\Models\Child;
use App\Models\ParentUser;
use App\Models\Zone;

test('admin can create child', function () {
    $this->actingAsAdmin();

    $zone = Zone::factory()->create();

    $response = $this->postJson('/api/children', [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'data' => [
            'first_name' => 'Leo',
            'last_name' => 'Smith',
            'birth_date' => '2023-05-24',
            'zone_id' => $zone->id,
            'pesel' => '11111111111',
            'started_at' => '2026-07-30',
        ],
    ]);
});

test('parent cannot create child', function () {
    $this->actingAsParent();

    $zone = Zone::factory()->create();

    $response = $this->postJson('/api/children', [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(403);
});

test('guest cannot create child', function () {
    $zone = Zone::factory()->create();

    $response = $this->postJson('/api/children', [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(401);
});

test('required fields are validated', function () {
    $this->actingAsAdmin();

    $response = $this->postJson('/api/children', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'first_name',
        'last_name',
        'birth_date',
        'zone_id',
        'pesel',
        'started_at',
    ]);
});

test('pesel must be unique', function () {
    $this->actingAsAdmin();

    $zone = Zone::factory()->create();

    $otherChildren = Child::factory()->create();

    $response = $this->postJson('/api/children', [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $zone->id,
        'pesel' => $otherChildren->pesel,
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['pesel']);
});

test('zone must exist', function () {
    $this->actingAsAdmin();

    $response = $this->postJson('/api/children', [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => 999,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['zone_id']);
});

test('pesel must have exactly 11 digits', function () {
    $this->actingAsAdmin();

    $zone = Zone::factory()->create();

    $response = $this->postJson('/api/children', [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $zone->id,
        'pesel' => '123',
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['pesel']);
});

test('admin can assign existing parents when creating child', function () {

    $this->actingAsAdmin();

    $parents = ParentUser::factory()->count(5)->create();
    $parentIds = $parents->pluck('id');

    $zone = Zone::factory()->create();

    $response = $this->postJson('/api/children', [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
        'parents' => $parentIds,
    ]);

    $response->assertStatus(201);

    foreach ($parentIds as $parentId) {
        $this->assertDatabaseHas('parent_child', [
            'child_id' => $response->json('data.id'),
            'parent_id' => $parentId,
        ]);
    }

    $this->assertDatabaseCount('parent_child', 5);

});

test('admin cannot assign non existing parents when creating child', function () {
    $this->actingAsAdmin();

    $parentIds = [1, 2, 3, 4, 5];

    $zone = Zone::factory()->create();

    $response = $this->postJson('/api/children', [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $zone->id,
        'pesel' => '123',
        'started_at' => '2026-07-30',
        'parents' => $parentIds,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('parents.0');
    $this->assertDatabaseCount('parent_child', 0);
});
