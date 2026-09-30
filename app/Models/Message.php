<?php

namespace App\Models;

use App\Policies\MessagePolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['author_user_id', 'title', 'content'])]
#[UsePolicy(MessagePolicy::class)]
class Message extends Model
{
    use HasFactory, SoftDeletes;

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MessageRecipient::class);
    }

    public function getFrontendUrl(): string
    {
        return config('app.frontend_url').'/messages/'.$this->id;
    }

    public function scopeForRecipient(Builder $query, User $user): Builder
    {
        return $query->whereHas('recipients', function ($query) use ($user): void {
            $query->where('user_id', $user->id);
        });
    }
}
