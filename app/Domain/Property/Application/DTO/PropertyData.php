<?php

namespace Domain\Property\Application\DTO;

use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;

final readonly class PropertyData
{
    public function __construct(
        public ?string $code = null,
        public ?string $name = null,
        public ?string $slug = null,
        public ?PropertyType $type = null,
        public ?PropertyStatus $status = null,
        public ?string $timezone = null,
        public ?string $currency = null,
        public ?string $address = null,
        public ?array $metadata = null,
        private array $present = [],
    ) {}

    public static function fromArray(array $input): self
    {
        return new self(
            code: $input['code'] ?? null,
            name: $input['name'] ?? null,
            slug: $input['slug'] ?? null,
            type: isset($input['type'])
                ? PropertyType::from($input['type'])
                : null,
            status: isset($input['status'])
                ? PropertyStatus::from($input['status'])
                : null,
            timezone: $input['timezone'] ?? null,
            currency: $input['currency'] ?? null,
            address: $input['address'] ?? null,
            metadata: $input['metadata'] ?? null,
            present: array_keys($input),
        );
    }

    public function toArray(): array
    {
        $values = [
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

        return array_intersect_key(
            $values,
            array_flip($this->present),
        );
    }
}
