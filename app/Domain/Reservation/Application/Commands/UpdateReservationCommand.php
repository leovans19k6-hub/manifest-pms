<?php

namespace Domain\Reservation\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Reservation\Application\DTO\ReservationData;
use Domain\Reservation\Models\Reservation;

final readonly class UpdateReservationCommand
{
    public function __construct(
        public OrganizationUser $membership,
        public Reservation $reservation,
        public ReservationData $input,
    ) {}
}
