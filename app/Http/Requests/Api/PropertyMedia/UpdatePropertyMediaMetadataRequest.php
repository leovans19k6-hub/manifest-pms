<?php

namespace App\Http\Requests\Api\PropertyMedia;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyMediaMetadataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metadata' => [
                'present',
                'nullable',
                'array',
            ],
        ];
    }
}
