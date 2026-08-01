<?php

namespace App\Models;

use App\Policies\MenuPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['menu_date', 'content'])]
#[UsePolicy(MenuPolicy::class)]
class Menu extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'menu_date' => 'date',
        ];
    }
}
