<?php

namespace Domain\Reservation\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Reservation\Models\Reservation;

final readonly class CancelReservationCommand
{
    public function __construct(
        public OrganizationUser $membership,
        public Reservation $reservation,
    ) {}
}
