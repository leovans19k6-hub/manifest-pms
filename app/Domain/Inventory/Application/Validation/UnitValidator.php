<?php

namespace Domain\Inventory\Application\Validation;

use Domain\Inventory\Application\DTO\UnitData;
use Domain\Inventory\Enums\UnitStatus;
use Domain\Inventory\Enums\UnitType;
use Domain\Inventory\Models\Unit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class UnitValidator
{
    public function validate(
        UnitData $input,
        string $organizationId,
        string $propertyId,
        ?Unit $unit = null,
    ): UnitData {
        $data = $input->toArray();

        Validator::make($data, [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'code')
                    ->where('organization_id', $organizationId)
                    ->where('property_id', $propertyId)
                    ->ignore($unit?->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required',
                'string',
                'max:180',
                Rule::unique('units', 'slug')
                    ->where('organization_id', $organizationId)
                    ->where('property_id', $propertyId)
                    ->ignore($unit?->id),
            ],
            'type' => ['required', Rule::enum(UnitType::class)],
            'status' => ['required', Rule::enum(UnitStatus::class)],
            'capacity_adults' => ['required', 'integer', 'min:0'],
            'capacity_children' => ['required', 'integer', 'min:0'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'bathrooms' => ['required', 'integer', 'min:0'],
            'base_occupancy' => [
                'required',
                'integer',
                'min:0',
                'lte:max_occupancy',
            ],
            'max_occupancy' => ['required', 'integer', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ])->validate();

        return $input;
    }
}
