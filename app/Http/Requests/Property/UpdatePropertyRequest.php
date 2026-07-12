<?php

namespace App\Http\Requests\Property;

class UpdatePropertyRequest extends StorePropertyRequest
{
    public function rules(): array
    {
        return array_map(fn (array $rules) => array_values(array_filter($rules, fn ($rule) => $rule !== 'required')), parent::rules());
    }
}
