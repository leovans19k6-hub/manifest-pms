<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCheckedOut;
use Domain\Housekeeping\Services\HousekeepingPlanner;

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