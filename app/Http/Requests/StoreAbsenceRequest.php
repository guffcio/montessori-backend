<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbsenceRequest extends FormRequest
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
            'child_id' => ['required', 'integer', Rule::exists('children', 'id')],
            'reported_by_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'absent_at' => ['required', 'date', Rule::date()->afterOrEqual(today())],
        ];
    }
}
