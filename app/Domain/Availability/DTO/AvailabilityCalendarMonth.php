<?php

namespace Domain\Availability\DTO;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class AvailabilityCalendarMonth
{
    /**
     * @param Collection<int, AvailabilityCalendarWeek> $weeks
     */
    public function __construct(
        public CarbonImmutable $month,
        public Collection $weeks,
    ) {
    }
}