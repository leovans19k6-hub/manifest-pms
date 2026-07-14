<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'property_id' => $this->property_id,
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'capacity_adults' => $this->capacity_adults,
            'capacity_children' => $this->capacity_children,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'base_occupancy' => $this->base_occupancy,
            'max_occupancy' => $this->max_occupancy,
            'sort_order' => $this->sort_order,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
