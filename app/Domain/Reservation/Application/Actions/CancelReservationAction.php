<?php

namespace Domain\Reservation\Application\Actions;

use Domain\Reservation\Application\Commands\CancelReservationCommand;
use Domain\Reservation\Services\ReservationService;

final class CancelReservationAction
{
    public function __construct(
        private ReservationService $reservations,
    ) {}

    public function execute(
        CancelReservationCommand $command,
    ): void {
        $this->reservations->cancel(
            $command->membership,
            $command->reservation,
        );
    }
}
