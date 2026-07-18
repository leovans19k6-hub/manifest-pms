<?php

namespace Domain\Foundation\Calendar;

use Carbon\CarbonImmutable;
use Domain\Foundation\Calendar\DTO\CalendarDay;
use Domain\Foundation\Calendar\DTO\CalendarMonth;
use Domain\Foundation\Calendar\DTO\CalendarWeek;
use Illuminate\Support\Collection;

final class CalendarBuilder
{
    public static function build(
        CarbonImmutable $month,
    ): CalendarMonth {
        $month = $month->startOfMonth();

        $cursor = $month->startOfWeek(CarbonImmutable::MONDAY);

        $end = $month
            ->endOfMonth()
            ->endOfWeek(CarbonImmutable::SUNDAY);

        $weeks = collect();

        while ($cursor <= $end) {

            $days = collect();

            for ($i = 0; $i < 7; $i++) {

                $days->push(
                    new CalendarDay(
                        date: $cursor,
                        inCurrentMonth: $cursor->month === $month->month,
                    ),
                );

                $cursor = $cursor->addDay();
            }

            $weeks->push(
                new CalendarWeek(
                    days: $days,
                ),
            );
        }

        return new CalendarMonth(
            month: $month,
            weeks: $weeks,
        );
    }
}