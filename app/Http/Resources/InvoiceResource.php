<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'child_id' => $this->child_id,

            'invoice_sequence' => $this->invoice_sequence,
            'invoice_month' => $this->invoice_month,
            'invoice_year' => $this->invoice_year,

            'billing_date' => $this->billing_date?->format('Y-m-d'),
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),

            'child_first_name' => $this->child_first_name,
            'child_last_name' => $this->child_last_name,
            'child_pesel' => $this->child_pesel,

            'total_amount' => $this->total_amount,

            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,

            'pdf_path' => $this->pdf_path,

            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
