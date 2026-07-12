<?php

namespace Domain\Property\Application\DTO;

use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;

final readonly class PropertyData
{
    public function __construct(
        public string $code,
        public string $name,
        public string $slug,
        public PropertyType $type,
        public PropertyStatus $status,
        public string $timezone,
        public string $currency,
        public ?string $address = null,
        public array $metadata = [],
    ) {}

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'status' => $this->status,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'address' => $this->address,
            'metadata' => $this->metadata,
        ];
    }
}
