<?php

namespace Domain\Availability\DTO;

use Carbon\CarbonImmutable;
use Domain\Availability\Enums\AvailabilityStatus;
use Domain\Reservation\Models\Reservation;

final readonly class AvailabilityDay
{
    public function __construct(
        public CarbonImmutable $date,
        public AvailabilityStatus $status,
        public ?Reservation $reservation = null,
    ) {}

    public function available(): bool
    {
        return $this->status === AvailabilityStatus::Available;
    }

    public function reserved(): bool
    {
        return $this->status === AvailabilityStatus::Reserved;
    }

    public function checkedIn(): bool
    {
        return $this->status === AvailabilityStatus::CheckedIn;
    }
}