<?php

namespace App\Services\Invoice;

use App\Models\InvoiceSequence;
use Carbon\Carbon;

class InvoiceSequenceService
{
    public function next(Carbon $issueDate): InvoiceSequence
    {
        $invoiceSequence = InvoiceSequence::where('year', $issueDate->year)
            ->where('month', $issueDate->month)
            ->lockForUpdate()
            ->first();

        if (! $invoiceSequence) {
            $invoiceSequence = InvoiceSequence::create([
                'year' => $issueDate->year,
                'month' => $issueDate->month,
                'last_sequence' => 1,
            ]);
        } else {
            $invoiceSequence->increment('last_sequence');
            $invoiceSequence->refresh();
        }

        return $invoiceSequence;
    }
}
