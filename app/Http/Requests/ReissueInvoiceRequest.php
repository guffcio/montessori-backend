<?php

namespace App\Http\Requests;

use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReissueInvoiceRequest extends FormRequest
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
            'year_month' => ['required', 'date_format:Y-m'],
            'monthly_fee' => ['sometimes', 'numeric', 'min:0', 'max:999999.99'],
            'holiday_days_count' => ['required', 'integer', 'min:0'],
            'items' => ['required', 'array'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'discounts' => ['sometimes', 'array'],
            'discounts.*.name' => ['required', 'string', 'max:255'],
            'discounts.*.quantity' => ['required', 'integer', 'min:1'],
            'discounts.*.price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }

    public function after(CalendarService $calendarService): array
    {
        return [
            function (Validator $validator) use ($calendarService): void {
                /** @var Request $this */
                if (
                    ! $this->filled('year_month') ||
                    ! $this->filled('holiday_days_count')
                ) {
                    return;
                }

                $billingDate = Carbon::createFromFormat('Y-m', $this->input('year_month'));

                $workDays = $calendarService->getWorkdaysCount($billingDate);

                if ($this->input('holiday_days_count') > $workDays) {
                    $validator->errors()->add(
                        'holiday_days_count',
                        "Holiday days count cannot exceed {$workDays} work days"
                    );
                }
            },
        ];
    }
}
