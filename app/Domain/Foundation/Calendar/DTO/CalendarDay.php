final readonly class CalendarDay
{
    public function __construct(
        public CarbonImmutable $date,
        public bool $currentMonth,
    ) {}
}