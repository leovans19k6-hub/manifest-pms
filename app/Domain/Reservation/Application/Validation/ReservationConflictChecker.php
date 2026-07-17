<?php

namespace Domain\Reservation\Application\Validation;

use Domain\Reservation\Models\Reservation;

final class ReservationConflictChecker
{
    public function hasConflict(
        string $unitId,
        \DateTimeInterface $checkIn,
        \DateTimeInterface $checkOut,
        ?string $ignoreReservationId = null,
    ): bool {
        return Reservation::query()
            ->where('unit_id', $unitId)
            ->when(
				$ignoreReservationId !== null,
				fn ($query) => $query->where('id', '!=', $ignoreReservationId),
			)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query
                    ->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            })
            ->exists();
    }
}
