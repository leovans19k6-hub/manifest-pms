<?php

namespace App\Http\Requests\PropertyMedia;

use Domain\Property\Enums\PropertyDocumentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadPropertyDocumentWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
            'category' => [
                'required',
                Rule::enum(PropertyDocumentCategory::class),
            ],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
