<?php

namespace App\Http\Requests\Property;

use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(PropertyStatus::class)], 'type' => ['nullable', Rule::enum(PropertyType::class)],
            'search' => ['nullable', 'string', 'max:150'], 'sort' => ['nullable', Rule::in(['code', 'name', 'type', 'status', 'created_at', 'updated_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100'], 'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
