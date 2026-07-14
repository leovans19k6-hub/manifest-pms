<?php

namespace App\Http\Requests\Inventory;

use Domain\Inventory\Enums\UnitStatus;
use Domain\Inventory\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'nullable',
                Rule::enum(UnitStatus::class),
            ],
            'type' => [
                'nullable',
                Rule::enum(UnitType::class),
            ],
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],
            'sort' => [
                'nullable',
                Rule::in([
                    'code',
                    'name',
                    'type',
                    'status',
                    'sort_order',
                    'created_at',
                    'updated_at',
                ]),
            ],
            'direction' => [
                'nullable',
                Rule::in(['asc', 'desc']),
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}
