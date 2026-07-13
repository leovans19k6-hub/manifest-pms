<?php

namespace App\Http\Requests\PropertyMedia;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPropertyMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_kind' => [
                'nullable',
                Rule::in(['image', 'video', 'floor_plan']),
            ],
            'document_category' => [
                'nullable',
                Rule::in(['legal', 'policy', 'brochure', 'other']),
            ],
            'document_lifecycle' => [
                'nullable',
                Rule::in(['active', 'archived']),
            ],
            'asset_sort' => [
                'nullable',
                Rule::in(['position', 'created_at', 'original_name']),
            ],
            'document_sort' => [
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
            'asset_per_page' => [
                'nullable',
                'integer',
                'between:1,100',
            ],
            'document_per_page' => [
                'nullable',
                'integer',
                'between:1,100',
            ],
        ];
    }
}
