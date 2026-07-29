<?php

namespace App;

enum InvoiceItemSource: string
{
    case SYSTEM = 'system';
    case MANUAL = 'manual';
}
