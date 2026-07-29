<?php

namespace App;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case PAID_BY_CARD = 'paid_by_card';
    case PAYU_PENDING = 'payu_pending';
    case CANCLED = 'canceled';
}
