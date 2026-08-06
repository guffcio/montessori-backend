<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\MessageNotification;
use App\Models\MessageRecipient;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_user_id' => User::factory(['role' => UserRole::ADMIN]),
            'title' => fake()->sentence(),
            'content' => fake()->paragraph(),
        ];
    }

    public function withRecipients(
        int $count = 1,
        bool $withNotifications = false
    ): static {
        return $this->afterCreating(function (Message $message) use ($count, $withNotifications) {

            $recipients = MessageRecipient::factory()
                ->count($count)
                ->for($message)
                ->create();

            if ($withNotifications) {
                $recipients->each(function (MessageRecipient $recipient) {
                    MessageNotification::factory()
                        ->for($recipient, 'recipient')
                        ->create();
                });
            }
        });
    }
}
