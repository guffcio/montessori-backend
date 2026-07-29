<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }
}
