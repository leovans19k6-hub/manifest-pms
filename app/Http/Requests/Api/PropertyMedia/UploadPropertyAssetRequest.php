<?php

namespace App\Http\Requests\Api\PropertyMedia;

use Domain\Property\Enums\PropertyAssetKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadPropertyAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
            'kind' => ['required', Rule::enum(PropertyAssetKind::class)],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
