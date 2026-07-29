<?php

namespace App;

enum InvoiceItemType: string
{
    case ADVANCE = 'advance';
    case CUSTOM = 'custom';
    case DISCOUNT = 'discount';
}
