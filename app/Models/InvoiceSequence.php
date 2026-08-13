<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('year', 'month', 'last_sequence')]
class InvoiceSequence extends Model
{
    //
}
