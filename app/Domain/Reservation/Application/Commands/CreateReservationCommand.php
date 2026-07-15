<?php

namespace Domain\Reservation\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Inventory\Models\Unit;
use Domain\Reservation\Application\DTO\ReservationData;

final readonly class CreateReservationCommand
{
    public function __construct(
        public OrganizationUser $membership,
        public Unit $unit,
        public ReservationData $input,
    ) {}
}
