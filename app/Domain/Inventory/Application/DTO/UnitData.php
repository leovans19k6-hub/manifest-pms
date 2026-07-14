<?php

namespace Domain\Inventory\Application\DTO;

use Domain\Inventory\Enums\UnitStatus;
use Domain\Inventory\Enums\UnitType;

final readonly class UnitData
{
    public function __construct(
        public string $code,
        public string $name,
        public string $slug,
        public UnitType $type,
        public UnitStatus $status,
        public int $capacityAdults,
        public int $capacityChildren,
        public int $bedrooms,
        public int $bathrooms,
        public int $baseOccupancy,
        public int $maxOccupancy,
        public int $sortOrder,
        public ?string $description = null,
        public ?array $metadata = null,
    ) {}

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'capacity_adults' => $this->capacityAdults,
            'capacity_children' => $this->capacityChildren,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'base_occupancy' => $this->baseOccupancy,
            'max_occupancy' => $this->maxOccupancy,
            'sort_order' => $this->sortOrder,
            'description' => $this->description,
            'metadata' => $this->metadata,
        ];
    }
}
