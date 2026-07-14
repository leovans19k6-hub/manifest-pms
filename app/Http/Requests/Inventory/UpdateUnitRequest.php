<?php

namespace App\Http\Requests\Inventory;

class UpdateUnitRequest extends StoreUnitRequest
{
    public function rules(): array
    {
        return array_map(
            fn (array $rules): array => array_values(
                array_filter(
                    $rules,
                    fn ($rule): bool => $rule !== 'required',
                ),
            ),
            parent::rules(),
        );
    }
}
