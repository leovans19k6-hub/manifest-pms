<?php

namespace App\Http\Requests\PropertyMedia;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyDocumentMetadataWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metadata' => ['nullable', 'array'],
        ];
    }
}
