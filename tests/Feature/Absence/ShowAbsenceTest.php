<?php

use App\Models\Absence;

test('admin can view absence', function (): void {
    $this->actingAsAdmin();

    $absence = $this->createAbsence();

    $response = $this->getJson("/api/absences/{$absence->id}");
    $response->assertStatus(200);

});

test('parent can view own children absence', function (): void {
    $parent = $this->actingAsParent();
    $child = $this->createChild();

    $child->parents()->sync($parent->id);

    $absence = $this->createAbsence([
        'child_id' => $child->id,
        'reported_by_user_id' => $parent->user->id,
    ]);

    $response = $this->getJson("/api/absences/{$absence->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'id' => $absence->id,
        ],
    ]);

});

test('parent cannot view another parent child absence', function (): void {
    $this->actingAsParent();

    $secondParent = $this->createParent();

    $otherChild = $this->createChild();

    $otherChild->parents()->sync($secondParent);

    $otherChildAbsence = $this->createAbsence([
        'child_id' => $otherChild->id,
        'reported_by_user_id' => $secondParent->user->id,
    ]);

    $response = $this->getJson("/api/absences/{$otherChildAbsence->id}");

    $response->assertStatus(403);
});

test('guest receives 401', function (): void {
    $response = $this->getJson('/api/absences/1');
    $response->assertStatus(401);
});

test('return 404 for missing absence', function (): void {
    $this->actingAsAdmin();

    $missingId = Absence::max('id') + 1;

    $response = $this->getJson("/api/absences/{$missingId}");
    $response->assertStatus(404);
});
