<?php

namespace App\Http\Requests\Api\PropertyMedia;

use Domain\Property\Enums\PropertyDocumentLifecycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPropertyDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string'],
            'lifecycle_status' => [
                'nullable',
                Rule::enum(PropertyDocumentLifecycle::class),
            ],
            'sort' => [
                'nullable',
                Rule::in([
                    'created_at',
                    'original_name',
                    'category',
                    'lifecycle_status',
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
        ];
    }
}
