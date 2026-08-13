<?php

namespace App;

enum InvoicePaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case PAID_BY_CARD = 'paid_by_card';
    case PENDING = 'pending';
    case CANCELED = 'canceled';

    public function isPaid(): bool
    {
        return in_array($this, [
            self::PAID,
            self::PAID_BY_CARD,
        ], true);
    }

    public function canBeDeleted(): bool
    {
        return $this === self::UNPAID;
    }

    public function canBeReissued(): bool
    {
        return $this === self::UNPAID;
    }
}
