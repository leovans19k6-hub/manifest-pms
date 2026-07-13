<?php

namespace App\Http\Requests\PropertyMedia;

use Illuminate\Foundation\Http\FormRequest;

class ReorderPropertyAssetsWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_ids' => ['required', 'array', 'min:1'],
            'asset_ids.*' => ['required', 'string', 'distinct'],
        ];
    }
}
