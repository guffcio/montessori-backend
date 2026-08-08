<?php

namespace App\Http\Requests;

use App\Models\Child;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * @property Child $child
 */
class UpdateChildRequest extends FormRequest
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
            'birth_date' => ['sometimes', 'date'],
            'zone_id' => Rule::when(Auth::user()->isParent(), ['missing'], ['sometimes', 'integer', Rule::exists('zones', 'id')]),
            'pesel' => ['sometimes', 'string', 'digits:11', Rule::unique('children', 'pesel')->ignore($this->child)],
            'started_at' => Rule::when(Auth::user()->isParent(), ['missing'], ['sometimes', 'date']),
            'preschool_started_at' => Rule::when(Auth::user()->isParent(), ['missing'], ['nullable', 'date']),
            'parents' => Rule::when(Auth::user()->isParent(), ['missing'], ['sometimes', 'array', 'distinct']),
            'parents.*' => ['integer', Rule::exists('parents', 'id')],
            'allergens' => ['sometimes', 'array', 'distinct'],
            'allergens.*' => ['integer', Rule::exists('allergens', 'id')],
        ];
    }
}
