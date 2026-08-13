<?php

namespace App;

enum InvoiceItemType: string
{
    case ADVANCE = 'advance';
    case CHARGE = 'charge';
    case DISCOUNT = 'discount';
}
