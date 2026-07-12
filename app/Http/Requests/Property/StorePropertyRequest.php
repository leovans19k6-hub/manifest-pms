<?php

namespace App\Http\Requests\Property;

use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'], 'name' => ['required', 'string', 'max:150'], 'slug' => ['required', 'string', 'max:180'],
            'type' => ['nullable', Rule::enum(PropertyType::class)], 'status' => ['nullable', Rule::enum(PropertyStatus::class)],
            'timezone' => ['nullable', 'timezone'], 'currency' => ['nullable', 'string', 'size:3'], 'address' => ['nullable', 'string'], 'metadata' => ['nullable', 'array'],
        ];
    }
}
