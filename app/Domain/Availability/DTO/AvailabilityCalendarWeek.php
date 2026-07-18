<?php

namespace Domain\Availability\DTO;

use Illuminate\Support\Collection;

final readonly class AvailabilityCalendarWeek
{
    /**
     * @param Collection<int, AvailabilityCalendarDay> $days
     */
    public function __construct(
        public Collection $days,
    ) {
    }
}