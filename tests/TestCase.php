<?php

namespace Tests;

use App\Models\Absence;
use App\Models\Child;
use App\Models\Menu;
use App\Models\Message;
use App\Models\ParentUser;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected string $defaultPassword = 'password123';

    protected function createAdmin(): User
    {
        $user = User::factory()->create([
            'password' => $this->defaultPassword,
            'role' => 'admin',
        ]);

        return $user;
    }

    protected function createParent(): ParentUser
    {
        $parent = ParentUser::factory()->for(
            User::factory()->state([
                'password' => $this->defaultPassword,
            ])
        )->create();

        return $parent;
    }

    protected function createChild(): Child
    {
        $child = Child::factory()->create();

        return $child;
    }

    protected function createAbsence($data = []): Absence
    {

        $absence = Absence::factory()->create($data);

        return $absence;
    }

    protected function createMenu(): Menu
    {
        $menu = Menu::factory()->create();

        return $menu;
    }

    protected function createMessage($data = []): Message
    {
        $message = Message::factory()->withRecipients(3, true)->create($data);

        return $message;
    }

    protected function actingAsAdmin(): User
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        return $admin;
    }

    protected function actingAsParent(): ParentUser
    {
        $parent = $this->createParent();
        $this->actingAs($parent->user);

        return $parent;
    }
}
