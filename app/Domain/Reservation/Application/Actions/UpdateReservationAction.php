<?php

namespace Domain\Reservation\Application\Actions;

use Domain\Reservation\Application\Commands\UpdateReservationCommand;
use Domain\Reservation\Application\Validation\ReservationConflictChecker;
use Domain\Reservation\Application\Validation\ReservationValidator;
use Domain\Reservation\Models\Reservation;
use Domain\Reservation\Services\ReservationService;
use Illuminate\Validation\ValidationException;

final class UpdateReservationAction
{
    public function __construct(
        private ReservationValidator $validator,
        private ReservationConflictChecker $conflicts,
        private ReservationService $reservations,
    ) {}

    public function execute(
        UpdateReservationCommand $command,
    ): Reservation {
        $data = $this->validator->validate(
            $command->input,
            $command->reservation->organization_id,
            $command->reservation,
        );

        if (
            $this->conflicts->hasConflict(
                $command->reservation->unit_id,
                $data->checkIn,
                $data->checkOut,
                $command->reservation->id,
            )
        ) {
            throw ValidationException::withMessages([
                'check_in' => 'The unit already has a reservation for the selected dates.',
            ]);
        }

        return $this->reservations->update(
            $command->membership,
            $command->reservation,
            $data->toArray(),
        );
    }
}
