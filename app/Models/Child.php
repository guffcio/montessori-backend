<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{
    use HasFactory, SoftDeletes;

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentUser::class, 'parent_child', 'child_id', 'parent_id');
    }

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class, 'allergen_child', 'child_id', 'allergen_id');
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class);
    }

    public function absences(): BelongsToMany
    {
        return $this->belongsToMany(Absence::class);
    }
}
