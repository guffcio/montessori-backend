<?php

namespace App\Policies;

use App\Models\ParentUser;
use App\Models\User;
use App\UserRole;

class ParentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        return null;
    }

    public function ownsParent(User $user, ParentUser $parentUser): bool
    {
        return $user->id === $parentUser->user_id;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ParentUser $parentUser): bool
    {
        return $this->ownsParent($user, $parentUser);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ParentUser $parentUser): bool
    {
        return $this->ownsParent($user, $parentUser);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ParentUser $parentUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ParentUser $parentUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ParentUser $parentUser): bool
    {
        return false;
    }
}
