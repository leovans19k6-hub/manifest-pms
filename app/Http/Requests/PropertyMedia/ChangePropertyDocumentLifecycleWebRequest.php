<?php

namespace App\Http\Requests\PropertyMedia;

use Domain\Property\Enums\PropertyDocumentLifecycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePropertyDocumentLifecycleWebRequest extends FormRequest
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
