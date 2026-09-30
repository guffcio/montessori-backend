<?php

use App\Jobs\Absence\SendParentReportedAbsenceNotificationJob;
use App\Models\User;
use App\Notifications\Absence\ParentReportedAbsenceNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

test('admin can create absence', function (): void {
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

test('parent can create own child absence', function (): void {
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

test('parent cannot create other parent child absence', function (): void {
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

test('guest cannot create absence', function (): void {
    $parent = $this->createParent();
    $child = $this->createChild();

    $response = $this->postJson('/api/absences', [
        'child_id' => $child->id,
        'reported_by_user_id' => $parent->user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(401);
});

test('required fields are validated', function (): void {
    $this->actingAsAdmin();

    $response = $this->postJson('/api/absences', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'child_id',
        'reported_by_user_id',
        'absent_at',
    ]);
});

test('child must exist', function (): void {
    $user = $this->actingAsAdmin();

    $response = $this->postJson('/api/absences', [
        'child_id' => 999,
        'reported_by_user_id' => $user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['child_id']);
});

test('user who reported absence must exist', function (): void {
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

test('should charge catering when absence is today and was created after 8:00AM or later', function (): void {
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

test('should not charge catering when absence is today and was created before 8:00AM', function (): void {
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

test('should not charge catering when absence is in future', function (): void {
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

test('data send properly to database', function (): void {
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

test('sends parent reported absence notification to admins', function (): void {
    Notification::fake();

    $parent = $this->actingAsParent();
    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $response = $this->postJson('/api/absences', [
        'child_id' => $child->id,
        'reported_by_user_id' => $parent->user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(201);

    User::admins()
        ->each(function (User $admin): void {
            Notification::assertSentTo($admin, ParentReportedAbsenceNotification::class);
        });
});

test('dispatches send parent reported absence notification job', function (): void {
    Queue::fake();
    $parent = $this->actingAsParent();
    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $response = $this->postJson('/api/absences', [
        'child_id' => $child->id,
        'reported_by_user_id' => $parent->user->id,
        'absent_at' => today()->nextWeekday()->toDateString(),
    ]);

    $response->assertStatus(201);

    Queue::assertPushed(SendParentReportedAbsenceNotificationJob::class);
});
