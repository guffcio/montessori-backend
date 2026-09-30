<?php

namespace App\Models;

use App\Policies\ParentPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'first_name', 'last_name', 'street', 'house_number', 'apartment_number', 'postal_code', 'city'])]
#[UsePolicy(ParentPolicy::class)]
class ParentUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parents';

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Child::class, 'parent_child', 'parent_id', 'child_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function absences()
    {
        return Absence::query()->whereHas('child.parents', function ($query): void {
            $query->whereKey($this->id);
        });
    }
}
