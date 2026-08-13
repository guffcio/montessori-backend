<?php

namespace App\Http\Requests;

use App\InvoicePaymentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoicePaymentStatusRequest extends FormRequest
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
            'payment_status' => ['required', Rule::enum(InvoicePaymentStatus::class)->only([
                InvoicePaymentStatus::PAID,
                InvoicePaymentStatus::PAID_BY_CARD,
            ])],
        ];
    }
}
