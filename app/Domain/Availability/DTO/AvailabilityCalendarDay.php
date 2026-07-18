<?php

namespace Domain\Availability\DTO;

use Domain\Foundation\Calendar\DTO\CalendarDay;

final readonly class AvailabilityCalendarDay
{
    public function __construct(
        public CalendarDay $day,
        public string $status,
        public ?string $reservationCode = null,
    ) {
    }

    public function isReserved(): bool
    {
        return $this->status !== AvailabilityDay::AVAILABLE;
    }

    public function badgeLabel(): string
    {
        return match ($this->status) {
            AvailabilityDay::AVAILABLE => 'Available',
            AvailabilityDay::RESERVED => 'Reserved',
            AvailabilityDay::CHECKED_IN => 'Checked In',
            default => 'Unknown',
        };
    }
}