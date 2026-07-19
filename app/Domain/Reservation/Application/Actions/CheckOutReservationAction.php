<?php

namespace Domain\Reservation\Application\Actions;

use Domain\Reservation\Application\Commands\CheckOutReservationCommand;
use Domain\Reservation\Services\ReservationService;

final class CheckOutReservationAction
{
    public function __construct(
        private ReservationService $reservations,
    ) {}

    public function execute(
        CheckOutReservationCommand $command,
    ): void {
        $this->reservations->checkOut(
            $command->membership,
            $command->reservation,
        );
    }
}