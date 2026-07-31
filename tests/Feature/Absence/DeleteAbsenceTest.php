<?php

test('admin can delete absence', function () {
    $user = $this->actingAsAdmin();

    $absence = $this->createAbsence([
        'reported_by_user_id' => $user->id,
    ]);

    $response = $this->deleteJson("/api/absences/{$absence->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('absences', [
        'id' => $absence->id,
    ]);
});

test('parent can delete absence', function () {
    $parent = $this->actingAsParent();

    $child = $this->createChild();
    $child->parents()->sync($parent->id);

    $absence = $this->createAbsence([
        'reported_by_user_id' => $parent->user->id,
        'child_id' => $child->id,
        'absent_at' => today()->nextWeekday(),
    ]);

    $response = $this->deleteJson("/api/absences/{$absence->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('absences', [
        'id' => $absence->id,
    ]);
});

test('parent cannot delete another parent child absence', function () {
    $this->actingAsParent();
    $otherParent = $this->createParent();

    $otherChild = $this->createChild();

    $otherChild->parents()->sync($otherParent);

    $otherChildAbsence = $this->createAbsence([
        'reported_by_user_id' => $otherParent->user->id,
        'child_id' => $otherChild->id,
    ]);

    $response = $this->deleteJson("/api/children/{$otherChildAbsence->id}");
    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('parent cannot delete todays absence after 8:00 AM', function () {
    $this->travelTo(now()->setDate(2026, 7, 31)->setTime(9, 0));
    $parent = $this->actingAsParent();

    $child = $this->createChild();
    $child->parents()->sync($parent->id);

    $absence = $this->createAbsence([
        'reported_by_user_id' => $parent->user->id,
        'child_id' => $child->id,
        'absent_at' => today()->toDateString(),
    ]);

    $response = $this->deleteJson("/api/absences/{$absence->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('absences', [
        'id' => $absence->id,
    ]);
});

test('parent cannot delete past absence', function () {
    $parent = $this->actingAsParent();

    $child = $this->createChild();
    $child->parents()->sync($parent->id);

    $absence = $this->createAbsence([
        'reported_by_user_id' => $parent->user->id,
        'child_id' => $child->id,
        'absent_at' => today()->previousWeekday(),
    ]);

    $response = $this->deleteJson("/api/absences/{$absence->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('absences', [
        'id' => $absence->id,
    ]);
});

test('admin can delete todays absence after 8:00 AM', function () {
    $this->travelTo(now()->setDate(2026, 7, 31)->setTime(9, 0));
    $user = $this->actingAsAdmin();

    $absence = $this->createAbsence([
        'reported_by_user_id' => $user->id,
        'absent_at' => today()->toDateString(),
    ]);

    $response = $this->deleteJson("/api/absences/{$absence->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('absences', [
        'id' => $absence->id,
    ]);
});

test('admin can delete past absence', function () {
    $user = $this->actingAsAdmin();

    $absence = $this->createAbsence([
        'reported_by_user_id' => $user->id,
        'absent_at' => today()->previousWeekday(),
    ]);

    $response = $this->deleteJson("/api/absences/{$absence->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('absences', [
        'id' => $absence->id,
    ]);
});

test('guest receives 401', function () {
    $absence = $this->createAbsence();
    $response = $this->deleteJson("/api/absences/{$absence->id}");
    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});
