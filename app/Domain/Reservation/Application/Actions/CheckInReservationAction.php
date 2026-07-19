<?php

namespace Domain\Reservation\Application\Actions;

use Domain\Reservation\Application\Commands\CheckInReservationCommand;
use Domain\Reservation\Services\ReservationService;

final class CheckInReservationAction
{
    public function __construct(
        private ReservationService $reservations,
    ) {}

    public function execute(
        CheckInReservationCommand $command,
    ): void {
        $this->reservations->checkIn(
            $command->membership,
            $command->reservation,
        );
    }
}