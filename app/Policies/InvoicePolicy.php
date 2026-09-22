<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function before(User $user, string $ability): ?bool
    {

        if ($ability === 'pay') {
            return null;
        }

        return $user->isAdmin() ? true : null;
    }

    private function ownsInvoice(User $user, Invoice $invoice): bool
    {
        return $invoice->child
            ->parents()
            ->whereKey($user->parent->id)
            ->exists();
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isParent();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $this->ownsInvoice($user, $invoice);
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
    public function update(User $user, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create payments of model.
     */
    public function pay(User $user, Invoice $invoice): bool
    {

        if ($user->isAdmin()) {
            return false;
        }

        return $invoice->child
            ->parents()
            ->where('user_id', $user->id)
            ->exists();
    }
}
