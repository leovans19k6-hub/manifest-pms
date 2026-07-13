<?php

namespace App\Http\Requests\Api\PropertyMedia;

use Illuminate\Foundation\Http\FormRequest;

class ReorderPropertyAssetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'asset_ids.*' => [
                'required',
                'string',
                'distinct',
            ],
        ];
    }
}
