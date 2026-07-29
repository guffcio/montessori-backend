<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->user->phone,
            'email' => $this->user->email,
            'street' => $this->street,
            'house_number' => $this->house_number,
            'apartment_number' => $this->apartment_number,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'created_at' => $this->created_at,
        ];
    }
}
