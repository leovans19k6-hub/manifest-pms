<?php

namespace Domain\Availability\DTO;

use Domain\Foundation\Calendar\DTO\CalendarDay;
use Domain\Availability\Enums\AvailabilityStatus;

final readonly class AvailabilityCalendarDay
{
    public function __construct(
        public CalendarDay $day,
		public AvailabilityStatus $status,
		public ?string $reservationCode = null,
    ) {
    }

    public function isReserved(): bool
    {
        return $this->status !== AvailabilityStatus::Available;
    }

    public function badgeLabel(): string
    {
        return match ($this->status) {
			AvailabilityStatus::Available => 'Available',
			AvailabilityStatus::Reserved => 'Reserved',
			AvailabilityStatus::CheckedIn => 'Checked In',
		};
    }
}