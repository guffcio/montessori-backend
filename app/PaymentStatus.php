<?php

namespace App;

enum PaymentStatus: string
{
    case NEW = 'NEW';
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';
    case CANCELED = 'CANCELED';
    case REJECTED = 'REJECTED';
}
