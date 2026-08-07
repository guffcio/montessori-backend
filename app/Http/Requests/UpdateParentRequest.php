<?php

namespace App\Http\Requests;

use App\Models\ParentUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * @property ParentUser $parentUser
 */
class UpdateParentRequest extends FormRequest
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
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20', 'phone:PL', Rule::unique('users', 'phone')->ignore($this->parentUser->user_id)],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->parentUser->user_id)],
            'password' => ['sometimes', 'string', 'min:8', 'max:255', Password::defaults()],
            'street' => ['sometimes', 'string', 'max:255'],
            'house_number' => ['sometimes', 'string', 'max:10'],
            'apartment_number' => ['sometimes', 'string', 'max:10'],
            'city' => ['sometimes', 'string', 'max:255'],
            'postal_code' => ['sometimes', 'string', 'max:10'],
            'children' => Rule::when(Auth::user()->isParent(), ['missing'], ['sometimes', 'array']),
            'children.*' => ['integer', Rule::exists('children', 'id')],
        ];
    }
}
