<?php

namespace App\Http\Requests\Reservation;

class UpdateReservationRequest extends StoreReservationRequest
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
