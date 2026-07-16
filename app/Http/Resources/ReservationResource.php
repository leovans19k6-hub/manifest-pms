<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'organization_id' => $this->organization_id,
            'property_id' => $this->property_id,
            'unit_id' => $this->unit_id,

            'code' => $this->code,

            'status' => $this->status->value,
            'source' => $this->source->value,

            'guest_name' => $this->guest_name,
            'guest_phone' => $this->guest_phone,
            'guest_email' => $this->guest_email,

            'adults' => $this->adults,
            'children' => $this->children,

            'check_in' => $this->check_in?->toISOString(),
            'check_out' => $this->check_out?->toISOString(),

            'notes' => $this->notes,
            'metadata' => $this->metadata,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
