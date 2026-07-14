<?php

namespace App\Http\Requests\Inventory;

use Domain\Inventory\Enums\UnitStatus;
use Domain\Inventory\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
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
                'nullable',
                Rule::enum(UnitType::class),
            ],
            'status' => [
                'nullable',
                Rule::enum(UnitStatus::class),
            ],
            'capacity_adults' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'capacity_children' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'bedrooms' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'bathrooms' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'base_occupancy' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'max_occupancy' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
        ];
    }
}
