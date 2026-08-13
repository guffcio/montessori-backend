<?php

namespace App;

enum PayuPaymentStatus: string
{
    case NEW = 'NEW';
    case PENDING = 'PENDING';
    case WAITING_FOR_CONFIRMATION = 'WAITING_FOR_CONFIRMATION';
    case COMPLETED = 'COMPLETED';
    case CANCELED = 'CANCELED';
    case REJECTED = 'REJECTED';
}
