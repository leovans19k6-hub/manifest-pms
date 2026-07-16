<?php

namespace App\Http\Requests\Reservation;

use Domain\Reservation\Enums\ReservationSource;
use Domain\Reservation\Enums\ReservationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
            ],

            'status' => [
                'nullable',
                Rule::enum(ReservationStatus::class),
            ],

            'source' => [
                'nullable',
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
                'max:255',
            ],

            'guest_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'adults' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'children' => [
                'nullable',
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
        ];
    }
}
