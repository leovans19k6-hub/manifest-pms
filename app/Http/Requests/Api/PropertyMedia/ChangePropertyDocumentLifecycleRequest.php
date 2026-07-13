<?php

namespace App\Http\Requests\Api\PropertyMedia;

use Domain\Property\Enums\PropertyDocumentLifecycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePropertyDocumentLifecycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lifecycle_status' => [
                'required',
                Rule::enum(PropertyDocumentLifecycle::class),
            ],
        ];
    }
}
