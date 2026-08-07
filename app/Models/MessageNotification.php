<?php

namespace App\Models;

use App\MessageNotificationChannel;
use App\MessageNotificationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['message_recipient_id', 'type', 'status', 'sent_at'])]
class MessageNotification extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'channel' => MessageNotificationChannel::class,
            'status' => MessageNotificationStatus::class,
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(MessageRecipient::class, 'message_recipient_id');
    }
}
