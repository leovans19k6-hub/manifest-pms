<?php

namespace Domain\Foundation\Calendar\DTO;

use Carbon\CarbonImmutable;

final readonly class CalendarDay
{
    public function __construct(
        public CarbonImmutable $date,
        public bool $inCurrentMonth,
    ) {
    }
}