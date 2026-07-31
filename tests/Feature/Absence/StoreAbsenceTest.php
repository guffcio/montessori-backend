<?php

test('admin can create absence', function () {
    $user = $this->actingAsAdmin();
    $child = $this->createChild();

    $response = $this->postJson('/api/absences', [
        'child_id' => $child->id,
        'reported_by_user_id' => $user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'data' => [
            'child_id' => $child->id,
            'reported_by_user_id' => $user->id,
            'charge_catering' => false,
            'absent_at' => today()->nextWeekday()->toDateString(),
        ],
    ]);
});

test('parent can create own child absence', function () {
    $parent = $this->actingAsParent();
    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $response = $this->postJson('/api/absences', [
        'child_id' => $child->id,
        'reported_by_user_id' => $parent->user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'data' => [
            'child_id' => $child->id,
            'reported_by_user_id' => $parent->user->id,
            'charge_catering' => false,
            'absent_at' => today()->nextWeekday()->toDateString(),
        ],
    ]);
});

test('parent cannot create other parent child absence', function () {
    $parent = $this->actingAsParent();

    $otherParent = $this->createParent();
    $otherChild = $this->createChild();

    $otherParent->children()->sync($otherChild->id);

    $response = $this->postJson('/api/absences', [
        'child_id' => $otherChild->id,
        'reported_by_user_id' => $parent->user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(403);
});

test('guest cannot create absence', function () {
    $parent = $this->createParent();
    $child = $this->createChild();

    $response = $this->postJson('/api/absences', [
        'child_id' => $child->id,
        'reported_by_user_id' => $parent->user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(401);
});

test('required fields are validated', function () {
    $this->actingAsAdmin();

    $response = $this->postJson('/api/absences', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'child_id',
        'reported_by_user_id',
        'absent_at',
    ]);
});

test('child must exist', function () {
    $user = $this->actingAsAdmin();

    $response = $this->postJson('/api/absences', [
        'child_id' => 999,
        'reported_by_user_id' => $user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['child_id']);
});

test('user who reported absence must exist', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $response = $this->postJson('/api/absences', [
        'child_id' => $child->id,
        'reported_by_user_id' => 999,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['reported_by_user_id']);
});

test('should charge catering when absence is today and was created after 8:00AM or later', function () {
    $this->travelTo(now()->setDate(2026, 7, 31)->setTime(9, 0));

    $child = $this->createChild();
    $user = $this->actingAsAdmin();

    $response = $this->postJson('/api/absences', [
        'child_id' => $child->id,
        'reported_by_user_id' => $user->id,
        'absent_at' => today()->toDateString(),
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'data' => [
            'charge_catering' => true,
        ],
    ]);
});

test('should not charge catering when absence is today and was created before 8:00AM', function () {
    $this->travelTo(now()->setDate(2026, 7, 31)->setTime(7, 0));

    $child = $this->createChild();
    $user = $this->actingAsAdmin();

    $response = $this->postJson('/api/absences', [
        'child_id' => $child->id,
        'reported_by_user_id' => $user->id,
        'absent_at' => today()->toDateString(),
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'data' => [
            'charge_catering' => false,
        ],
    ]);
});

test('should not charge catering when absence is in future', function () {
    $child = $this->createChild();
    $user = $this->actingAsAdmin();

    $response = $this->postJson('/api/absences', [
        'child_id' => $child->id,
        'reported_by_user_id' => $user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'data' => [
            'charge_catering' => false,
        ],
    ]);
});

test('data send properly to database', function () {
    $child = $this->createChild();
    $user = $this->actingAsAdmin();

    $response = $this->postJson('/api/absences', [
        'child_id' => $child->id,
        'reported_by_user_id' => $user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('absences', [
        'id' => $response->json('data.id'),
        'child_id' => $child->id,
        'reported_by_user_id' => $user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);
});
