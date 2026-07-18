<?php

namespace Domain\Foundation\Calendar\DTO;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class CalendarMonth
{
    /**
     * @param Collection<int, CalendarWeek> $weeks
     */
    public function __construct(
        public CarbonImmutable $month,
        public Collection $weeks,
    ) {
    }
}