<?php

use App\Models\ParentUser;
use App\Models\Zone;

test('admin can update child', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $response = $this->patchJson("/api/children/{$child->id}", [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $child->zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'first_name' => 'Leo',
            'last_name' => 'Smith',
            'birth_date' => '2023-05-24',
            'zone_id' => $child->zone->id,
            'pesel' => '11111111111',
            'started_at' => '2026-07-30',
        ],
    ]);
});

test('parent can update own child', function () {
    $parent = $this->actingAsParent();
    $child = $this->createChild();

    $child->parents()->sync($parent->id);

    $response = $this->patchJson("/api/children/{$child->id}", [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'pesel' => '11111111111',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'first_name' => 'Leo',
            'last_name' => 'Smith',
            'birth_date' => '2023-05-24',
            'pesel' => '11111111111',
            'zone_id' => $child->zone->id,
            'started_at' => $child->started_at,
        ],
    ]);
});

test('parent cannot update another child', function () {
    $parent = $this->actingAsParent();
    $secondParent = $this->createParent();

    $child = $this->createChild();
    $otherChild = $this->createChild();

    $child->parents()->sync($parent);
    $otherChild->parents()->sync($secondParent);

    $response = $this->patchJson("/api/children/{$otherChild->id}", [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $otherChild->zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function () {
    $child = $this->createChild();
    $response = $this->patchJson("/api/children/{$child->id}", [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $child->zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('updates children table', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $response = $this->patchJson("/api/children/{$child->id}", [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $child->zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('children', [
        'id' => $child->id,
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $child->zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
    ]);
});

test('admin can replace assigned parents', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $parents = ParentUser::factory()->count(5)->create();
    $parentIds = $parents->pluck('id');

    $child->parents()->sync($parentIds);

    $newParents = ParentUser::factory()->count(3)->create();
    $newParentIds = $newParents->pluck('id');

    $response = $this->patchJson("/api/children/{$child->id}", [
        'id' => $child->id,
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $child->zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
        'parents' => $newParentIds,
    ]);

    $response->assertStatus(200);

    foreach ($newParentIds as $parentId) {
        $this->assertDatabaseHas('parent_child', [
            'parent_id' => $parentId,
            'child_id' => $child->id,
        ]);
    }

    foreach ($parentIds as $parentId) {
        $this->assertDatabaseMissing('parent_child', [
            'parent_id' => $parentId,
            'child_id' => $child->id,
        ]);
    }

    $this->assertDatabaseCount('parent_child', 3);

});

test('admin cannot assign non existing parents', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $parents = ParentUser::factory()->count(5)->create();
    $parentIds = $parents->pluck('id');

    $child->parents()->sync($parentIds);

    $newParentIds = [999, 1000, 1001];

    $response = $this->patchJson("/api/children/{$child->id}", [
        'id' => $child->id,
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $child->zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
        'parents' => $newParentIds,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('parents.0');
    $this->assertDatabaseCount('parent_child', 5);
});

test('admin can remove all parents', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $parents = ParentUser::factory()->count(5)->create();
    $parentIds = $parents->pluck('id');

    $child->parents()->sync($parentIds);

    $response = $this->patchJson("/api/children/{$child->id}", [
        'id' => $child->id,
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $child->zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
        'parents' => [],
    ]);

    $response->assertStatus(200);

    foreach ($parentIds as $parentId) {
        $this->assertDatabaseMissing('parent_child', [
            'parent_id' => $parentId,
            'child_id' => $child->id,
        ]);
    }

    $this->assertDatabaseCount('parent_child', 0);
});

test('parent can update only strict fields', function () {
    $parent = $this->actingAsParent();

    $child = $this->createChild();

    $child->parents()->sync($parent->id);

    $zone = Zone::factory()->create();

    $response = $this->patchJson("/api/children/{$child->id}", [
        'id' => $child->id,
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $zone->id,
        'pesel' => '11111111111',
        'started_at' => '2026-07-30',
        'parents' => [],
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('children', [
        'id' => $child->id,
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $child->zone->id,
        'pesel' => '11111111111',
        'started_at' => $child->started_at,
    ]);

    $this->assertDatabaseHas('parent_child', [
        'parent_id' => $parent->id,
        'child_id' => $child->id,
    ]);

    $this->assertDatabaseCount('parent_child', 1);
});

test('child can keep current pesel', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $response = $this->patchJson("/api/children/{$child->id}", [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $child->zone->id,
        'pesel' => $child->pesel,
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(200);
});

test('child cannot use another child pesel', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();
    $otherChild = $this->createChild();

    $response = $this->patchJson("/api/children/{$child->id}", [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => $child->zone->id,
        'pesel' => $otherChild->pesel,
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['pesel']);
});

test('zone must exist', function () {
    $this->actingAsAdmin();
    $child = $this->createChild();

    $response = $this->patchJson("/api/children/{$child->id}", [
        'first_name' => 'Leo',
        'last_name' => 'Smith',
        'birth_date' => '2023-05-24',
        'zone_id' => 999,
        'pesel' => $child->pesel,
        'started_at' => '2026-07-30',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['zone_id']);
});
