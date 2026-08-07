<?php

namespace App\Models;

use App\Policies\AbsencePolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['child_id', 'reported_by_user_id', 'charge_catering', 'absent_at'])]
#[UsePolicy(AbsencePolicy::class)]
class Absence extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'absent_at' => 'date',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    public static function shouldChargeCatering(string $absentAt): bool
    {
        return today()->isSameDay($absentAt) && now()->isAfter(today()->setTime(8, 0));
    }
}
