<?php

namespace Database\Factories;

use App\MessageNotificationChannel;
use App\MessageNotificationStatus;
use App\Models\MessageNotification;
use App\Models\MessageRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageNotification>
 */
class MessageNotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_recipient_id' => MessageRecipient::factory(),
            'channel' => MessageNotificationChannel::EMAIL,
            'status' => MessageNotificationStatus::INIT,
            'sent_at' => null,
        ];
    }
}
