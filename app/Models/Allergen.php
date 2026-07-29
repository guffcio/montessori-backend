<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Allergen extends Model
{
    //

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Child::class, 'allergen_child', 'allergen_id', 'child_id');
    }
}
