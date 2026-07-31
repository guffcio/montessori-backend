<?php

use App\Models\Absence;
use App\Models\Child;
use App\Models\ParentUser;

test('admin can list all absences', function () {
    $this->actingAsAdmin();

    Absence::factory()->count(10)->create();

    $response = $this->getJson('/api/absences');

    $response->assertStatus(200);
    expect($response['data'])->toHaveLength(10);

});

test('parent can list only own children absences', function () {
    $parent = $this->actingAsParent();
    $otherParent = ParentUser::factory()->create();

    $ownChildren = Child::factory()->count(3)->create();
    $ownChildrenIds = $ownChildren->pluck('id');

    $otherChildren = Child::factory()->count(2)->create();
    $otherChildrenIds = $otherChildren->pluck('id');

    $parent->children()->sync($ownChildrenIds);
    $otherParent->children()->sync($otherChildrenIds);

    $ownChildrenAbsences = $ownChildrenIds->map(
        fn ($childId) => $this->createAbsence([
            'child_id' => $childId,
            'reported_by_user_id' => $parent->user->id,
        ])
    );

    $otherChildrenAbsences = $otherChildrenIds->map(
        fn ($childId) => $this->createAbsence([
            'child_id' => $childId,
            'reported_by_user_id' => $otherParent->user->id,
        ])
    );

    $response = $this->getJson('/api/absences');

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');
    $response->assertJsonPath('data.*.id', $ownChildrenAbsences->pluck('id')->all());

});

test('guest receives 401', function () {
    $response = $this->getJson('/api/absences');

    $response->assertStatus(401);
});
