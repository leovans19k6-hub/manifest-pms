<?php

namespace Domain\Property\Application\Validation;

use Domain\Property\Application\DTO\PropertyData;
use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Domain\Property\Models\Property;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PropertyValidator
{
    public function validate(array $input, ?Property $property = null): PropertyData
    {
        $validated = Validator::make(
            $input,
            $property === null
                ? $this->createRules()
                : $this->updateRules($property),
        )->validate();

        return PropertyData::fromArray($validated);
    }

    private function createRules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'slug' => [
                'required',
                'string',
                'max:180',
            ],
            'type' => [
                'sometimes',
                Rule::enum(PropertyType::class),
            ],
            'status' => [
                'sometimes',
                Rule::enum(PropertyStatus::class),
            ],
            'timezone' => [
                'sometimes',
                'timezone',
            ],
            'currency' => [
                'sometimes',
                'string',
                'size:3',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
        ];
    }

    private function updateRules(Property $property): array
    {
        return [
            'code' => [
                'sometimes',
                'string',
                'max:50',
            ],
            'name' => [
                'sometimes',
                'string',
                'max:150',
            ],
            'slug' => [
                'sometimes',
                'string',
                'max:180',
            ],
            'type' => [
                'sometimes',
                Rule::enum(PropertyType::class),
            ],
            'status' => [
                'sometimes',
                Rule::enum(PropertyStatus::class),
            ],
            'timezone' => [
                'sometimes',
                'timezone',
            ],
            'currency' => [
                'sometimes',
                'string',
                'size:3',
            ],
            'address' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'metadata' => [
                'sometimes',
                'nullable',
                'array',
            ],
        ];
    }
}
