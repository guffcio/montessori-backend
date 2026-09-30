<?php

use App\Models\Allergen;
use App\Models\Child;
use App\Models\ParentUser;
use App\Models\Zone;

test('admin can create child', function (): void {
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

test('parent cannot create child', function (): void {
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

test('guest cannot create child', function (): void {
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

test('required fields are validated', function (): void {
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

test('pesel must be unique', function (): void {
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

test('zone must exist', function (): void {
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

test('pesel must have exactly 11 digits', function (): void {
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

test('admin can assign existing parents when creating child', function (): void {

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

test('admin cannot assign non existing parents when creating child', function (): void {
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

test('admin can assign existing allergens when creating child', function (): void {

    $this->actingAsAdmin();

    $allergens = Allergen::factory()->count(5)->create();
    $allergenIds = $allergens->pluck('id');

    $zone = Zone::factory()->create();

    $response = $this->postJson('/api/children', [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
        'allergens' => $allergenIds,
    ]);

    $response->assertStatus(201);

    foreach ($allergenIds as $allergenId) {
        $this->assertDatabaseHas('allergen_child', [
            'child_id' => $response->json('data.id'),
            'allergen_id' => $allergenId,
        ]);
    }

    $this->assertDatabaseCount('allergen_child', 5);

});

test('admin cannot assign non existing allergens when creating child', function (): void {
    $this->actingAsAdmin();

    $allergenIds = [1, 2, 3, 4, 5];

    $zone = Zone::factory()->create();

    $response = $this->postJson('/api/children', [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $zone->id,
        'pesel' => '123',
        'started_at' => '2026-07-30',
        'allergens' => $allergenIds,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('allergens.0');
    $this->assertDatabaseCount('allergen_child', 0);
});
