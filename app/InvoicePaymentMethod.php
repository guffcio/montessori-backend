<?php

namespace App;

enum InvoicePaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case BANK_TRANSFER = 'bank_transfer';
    case ONLINE = 'online';
}
