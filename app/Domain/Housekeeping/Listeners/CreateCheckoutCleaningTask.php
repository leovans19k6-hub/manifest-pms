<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Listeners;

use Domain\Housekeeping\Services\HousekeepingPlanner;
use Domain\Reservation\Events\ReservationCheckedOut;

final readonly class CreateCheckoutCleaningTask
{
    public function __construct(
        private HousekeepingPlanner $planner,
    ) {
    }

    public function handle(
        ReservationCheckedOut $event,
    ): void {
        $this->planner->createCheckoutCleaningTask(
            $event->reservation,
        );
    }
}