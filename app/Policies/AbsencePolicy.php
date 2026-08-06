<?php

namespace App\Policies;

use App\Models\Absence;
use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Access\Response;

class AbsencePolicy
{
    public function before(User $user, string $ability): ?bool
    {

        return $user->isAdmin() ? true : null;
    }

    public function ownsChild(User $user, int $child_id): bool
    {
        return $user->parent
            ->children()
            ->whereKey($child_id)
            ->exists();
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::PARENT;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Absence $absence): bool
    {
        return $this->ownsChild($user, $absence->child_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, int $child_id): bool
    {
        return $this->ownsChild($user, $child_id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Absence $absence): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Absence $absence): Response|bool
    {
        if (! $this->ownsChild($user, $absence->child_id)) {
            return false;
        }

        if (
            $absence->absent_at->isPast()
            || (
                $absence->absent_at->isToday()
                && now()->isAfter(today()->setTime(8, 0))
            )
        ) {
            return Response::deny(
                'An absence cannot be cancelled after 8:00 AM on the day of the absence or after the absence date has passed.'
            );
        }

        return true;
    }
}
