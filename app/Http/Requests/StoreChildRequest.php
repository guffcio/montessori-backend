<?php

namespace App\Http\Requests;

use App\Models\Child;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property Child $child
 */
class StoreChildRequest extends FormRequest
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
            'pesel' => ['required', 'string', 'digits:11', Rule::unique('children', 'pesel')],
            'started_at' => ['required', 'date'],
            'preschool_started_at' => ['nullable', 'date'],
            'parents' => ['sometimes', 'array'],
            'parents.*' => ['integer', Rule::exists('parents', 'id')],
            'allergens' => ['sometimes', 'array'],
            'allergens.*' => ['integer', Rule::exists('allergens', 'id')],
        ];
    }
}
