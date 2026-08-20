<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;

class InvoicePaymentPolicy
{
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
    public function view(User $user, InvoicePayment $invoicePayment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Invoice $invoice): bool
    {
        return $invoice->child
            ->parents()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InvoicePayment $invoicePayment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InvoicePayment $invoicePayment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InvoicePayment $invoicePayment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InvoicePayment $invoicePayment): bool
    {
        return false;
    }
}
