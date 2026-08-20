<?php

namespace App;

enum PaymentProvider: string
{
    case MANUAL = 'manual';
    case PAYU = 'payu';
}
