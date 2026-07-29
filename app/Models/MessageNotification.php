<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageNotification extends Model
{
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(MessageRecipient::class);
    }
}
