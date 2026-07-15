<?php

namespace Domain\Reservation\Application\Actions;

use Domain\Reservation\Application\Commands\CreateReservationCommand;
use Domain\Reservation\Application\Validation\ReservationConflictChecker;
use Domain\Reservation\Application\Validation\ReservationValidator;
use Domain\Reservation\Models\Reservation;
use Domain\Reservation\Services\ReservationService;
use Illuminate\Validation\ValidationException;

final class CreateReservationAction
{
    public function __construct(
        private ReservationValidator $validator,
        private ReservationConflictChecker $conflicts,
        private ReservationService $reservations,
    ) {}

    public function execute(
        CreateReservationCommand $command,
    ): Reservation {
        $data = $this->validator->validate(
            $command->input,
            $command->unit->organization_id,
        );

        if (
            $this->conflicts->hasConflict(
                $command->unit->id,
                $data->checkIn,
                $data->checkOut,
            )
        ) {
            throw ValidationException::withMessages([
                'check_in' => 'The unit already has a reservation for the selected dates.',
            ]);
        }

        return $this->reservations->create(
            $command->membership,
            $command->unit,
            $data->toArray(),
        );
    }
}
