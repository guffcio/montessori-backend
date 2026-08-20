<?php

namespace App\Http\Requests;

use App\InvoicePaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkAsPaidInvoiceRequest extends FormRequest
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
            'payment_method' => ['required', Rule::enum(InvoicePaymentMethod::class)->only([
                InvoicePaymentMethod::CASH,
                InvoicePaymentMethod::CARD,
                InvoicePaymentMethod::BANK_TRANSFER,
            ])],
        ];
    }
}
