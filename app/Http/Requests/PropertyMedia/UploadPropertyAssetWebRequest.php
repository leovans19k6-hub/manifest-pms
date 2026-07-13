<?php

namespace App\Http\Requests\PropertyMedia;

use Domain\Property\Enums\PropertyAssetKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadPropertyAssetWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kind' => ['required', Rule::enum(PropertyAssetKind::class)],
            'file' => ['required', 'file'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
