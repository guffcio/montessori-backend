<?php

namespace App\Http\Requests;

use App\MessageNotificationChannel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property Message $message
 */
class MessageNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'notification_channels' => ['required', 'array', 'distinct'],
            'notification_channels.*' => ['string', Rule::enum(MessageNotificationChannel::class)],
        ];
    }
}
