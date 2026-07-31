<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsenceResource extends JsonResource
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
            'reported_by_user_id' => $this->reported_by_user_id,
            'absent_at' => $this->absent_at->toDateString(),
            'charge_catering' => $this->charge_catering,
        ];
    }
}
