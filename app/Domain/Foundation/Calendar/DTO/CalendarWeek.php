<?php

namespace Domain\Foundation\Calendar\DTO;

use Illuminate\Support\Collection;

final readonly class CalendarWeek
{
    /**
     * @param Collection<int, CalendarDay> $days
     */
    public function __construct(
        public Collection $days,
    ) {
    }
}