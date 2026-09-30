<?php

test('admin can update absence', function (): void {
    $this->actingAsAdmin();

    $absence = $this->createAbsence([
        'charge_catering' => true,
    ]);

    $response = $this->patchJson("/api/absences/{$absence->id}", [
        'charge_catering' => false,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'id' => $absence->id,
            'child_id' => $absence->child_id,
            'reported_by_user_id' => $absence->reported_by_user_id,
            'charge_catering' => false,
            'absent_at' => $absence->absent_at->toDateString(),
        ],
    ]);
});

test('parent cannot update own child absence', function (): void {
    $parent = $this->actingAsParent();
    $child = $this->createChild();

    $child->parents()->sync($parent->id);

    $absence = $this->createAbsence([
        'charge_catering' => true,
        'reported_by_user_id' => $parent->user->id,
        'child_id' => $child->id,
    ]);

    $response = $this->patchJson("/api/absences/{$absence->id}", [
        'charge_catering' => false,
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('parent cannot update another child absence', function (): void {
    $this->actingAsParent();
    $secondParent = $this->createParent();

    $otherChild = $this->createChild();
    $otherChild->parents()->sync($secondParent);

    $otherChildAbsence = $this->createAbsence([
        'child_id' => $otherChild->id,
        'reported_by_user_id' => $secondParent->user->id,
        'charge_catering' => true,
    ]);

    $response = $this->patchJson("/api/absences/{$otherChildAbsence->id}", [
        'charge_catering' => false,
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function (): void {
    $absence = $this->createAbsence();
    $response = $this->patchJson("/api/absences/{$absence->id}", [
        'charge_catering' => false,
    ]);

    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('updates absence table', function (): void {
    $this->actingAsAdmin();

    $absence = $this->createAbsence([
        'charge_catering' => true,
    ]);

    $response = $this->patchJson("/api/absences/{$absence->id}", [
        'charge_catering' => false,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('absences', [
        'id' => $absence->id,
        'child_id' => $absence->child_id,
        'reported_by_user_id' => $absence->reported_by_user_id,
        'charge_catering' => false,
        'absent_at' => $absence->absent_at,
    ]);
});

test('admin can update only charge_catering field', function (): void {
    $this->actingAsAdmin();

    $absence = $this->createAbsence([
        'charge_catering' => true,
    ]);

    $newChild = $this->createChild();
    $newParent = $this->createParent();

    $response = $this->patchJson("/api/absences/{$absence->id}", [
        'charge_catering' => false,
        'child_id' => $newChild->id,
        'reported_by_user_id' => $newParent->user->id,
        'absent_at' => today()->toDateString(),
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('absences', [
        'id' => $absence->id,
        'child_id' => $absence->child_id,
        'reported_by_user_id' => $absence->reported_by_user_id,
        'charge_catering' => false,
        'absent_at' => $absence->absent_at,
    ]);

});
