<?php

namespace Tests;

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
