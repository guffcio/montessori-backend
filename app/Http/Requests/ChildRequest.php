<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChildRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'zone_id' => ['required', 'integer', Rule::exists('zones', 'id')],
            'pesel' => ['required', 'string', 'digits:11', Rule::unique('children', 'pesel')->ignore($this->child)],
            'started_at' => ['required', 'date'],
            'preschool_started_at' => ['nullable', 'date'],
        ];
    }
}
