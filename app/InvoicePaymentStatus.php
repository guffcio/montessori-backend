<?php

namespace App;

enum InvoicePaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case PENDING = 'pending';

    public function canBeDeleted(): bool
    {
        return $this === self::UNPAID;
    }

    public function canBeReissued(): bool
    {
        return $this === self::UNPAID;
    }
}
