<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'code' => $this->code, 'name' => $this->name, 'slug' => $this->slug, 'type' => $this->type->value, 'status' => $this->status->value,
            'timezone' => $this->timezone, 'currency' => $this->currency, 'address' => $this->address, 'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
