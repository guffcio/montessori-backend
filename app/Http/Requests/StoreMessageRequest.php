<?php

namespace App\Http\Requests;

use App\MessageNotificationChannel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
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
            'author_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'recipients' => ['required', 'array', 'distinct'],
            'recipients.*' => ['integer', Rule::exists('users', 'id')],
            'notification_channels' => ['sometimes', 'array', 'distinct'],
            'notification_channels.*' => ['string', Rule::enum(MessageNotificationChannel::class)],
        ];
    }
}
