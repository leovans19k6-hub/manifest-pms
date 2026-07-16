<?php

namespace Domain\Reservation\Application\Mappers;

use DateTimeImmutable;
use Domain\Reservation\Application\DTO\ReservationData;
use Domain\Reservation\Enums\ReservationSource;
use Domain\Reservation\Enums\ReservationStatus;

final class ReservationDataMapper
{
    public function fromArray(array $attributes): ReservationData
    {
        return new ReservationData(
            code: $attributes['code'],

            status: ReservationStatus::from(
                $attributes['status']
                    ?? ReservationStatus::Reserved->value,
            ),

            source: ReservationSource::from(
                $attributes['source']
                    ?? ReservationSource::Website->value,
            ),

            guestName: $attributes['guest_name'],

            guestPhone: $attributes['guest_phone']
                ?? null,

            guestEmail: $attributes['guest_email']
                ?? null,

            adults: (int) (
                $attributes['adults']
                    ?? 1
            ),

            children: (int) (
                $attributes['children']
                    ?? 0
            ),

            checkIn: $this->dateTime(
                $attributes['check_in'],
            ),

            checkOut: $this->dateTime(
                $attributes['check_out'],
            ),

            notes: $attributes['notes']
                ?? null,

            metadata: $attributes['metadata']
                ?? null,
        );
    }

    private function dateTime(
        mixed $value,
    ): \DateTimeInterface {
        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        return new DateTimeImmutable(
            (string) $value,
        );
    }
}
