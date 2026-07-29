<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id', 'first_name', 'last_name', 'street', 'house_number', 'apartment_number', 'postal_code', 'city'])]
class ParentUser extends Model
{
    protected $table = 'parents';

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Child::class, 'parent_child', 'parent_id', 'child_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
