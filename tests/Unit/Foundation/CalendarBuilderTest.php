<?php

namespace Tests\Unit\Foundation;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Domain\Foundation\Calendar\CalendarBuilder;
use Domain\Foundation\Calendar\DTO\CalendarMonth;
use PHPUnit\Framework\TestCase;

class CalendarBuilderTest extends TestCase
{
    public function test_builds_calendar_month(): void
    {
        $calendar = CalendarBuilder::build(
            CarbonImmutable::parse('2026-07-01'),
        );

        $this->assertInstanceOf(
            CalendarMonth::class,
            $calendar,
        );

        $this->assertNotEmpty(
            $calendar->weeks,
        );
    }

    public function test_every_week_contains_seven_days(): void
    {
        $calendar = CalendarBuilder::build(
            CarbonImmutable::parse('2026-07-01'),
        );

        foreach ($calendar->weeks as $week) {
            $this->assertCount(
                7,
                $week->days,
            );
        }
    }

    public function test_marks_days_inside_current_month(): void
    {
        $calendar = CalendarBuilder::build(
            CarbonImmutable::parse('2026-07-01'),
        );

        $days = $calendar->weeks
            ->flatMap(fn ($week) => $week->days)
            ->keyBy(fn ($day) => $day->date->toDateString());

        $this->assertTrue($days['2026-07-01']->inCurrentMonth);
        $this->assertTrue($days['2026-07-15']->inCurrentMonth);
        $this->assertTrue($days['2026-07-31']->inCurrentMonth);

        $this->assertFalse($days['2026-06-29']->inCurrentMonth);
        $this->assertFalse($days['2026-06-30']->inCurrentMonth);
        $this->assertFalse($days['2026-08-01']->inCurrentMonth);
    }

    public function test_starts_from_monday(): void
    {
        $calendar = CalendarBuilder::build(
            CarbonImmutable::parse('2026-07-01'),
        );

        $firstDay = $calendar->weeks
            ->first()
            ->days
            ->first();

        $this->assertEquals(
            CarbonInterface::MONDAY,
            $firstDay->date->dayOfWeek,
        );
    }

    public function test_ends_on_sunday(): void
    {
        $calendar = CalendarBuilder::build(
            CarbonImmutable::parse('2026-07-01'),
        );

        $lastDay = $calendar->weeks
            ->last()
            ->days
            ->last();

        $this->assertEquals(
            CarbonInterface::SUNDAY,
            $lastDay->date->dayOfWeek,
        );
    }
}