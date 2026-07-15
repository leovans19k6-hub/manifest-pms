<?php

namespace Domain\Reservation\Application\Validation;

use Domain\Reservation\Application\DTO\ReservationData;
use Domain\Reservation\Enums\ReservationSource;
use Domain\Reservation\Enums\ReservationStatus;
use Domain\Reservation\Models\Reservation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ReservationValidator
{
    public function validate(
        ReservationData $input,
        string $organizationId,
        ?Reservation $reservation = null,
    ): ReservationData {
        $data = $input->toArray();

        Validator::make($data, [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('reservations', 'code')
                    ->where('organization_id', $organizationId)
                    ->ignore($reservation?->id),
            ],

            'status' => [
                'required',
                Rule::enum(ReservationStatus::class),
            ],

            'source' => [
                'required',
                Rule::enum(ReservationSource::class),
            ],

            'guest_name' => [
                'required',
                'string',
                'max:255',
            ],

            'guest_phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'guest_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'adults' => [
                'required',
                'integer',
                'min:1',
            ],

            'children' => [
                'required',
                'integer',
                'min:0',
            ],

            'check_in' => [
                'required',
                'date',
            ],

            'check_out' => [
                'required',
                'date',
                'after:check_in',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'metadata' => [
                'nullable',
                'array',
            ],
        ])->validate();

        return $input;
    }
}
