final readonly class CalendarMonth
{
    public function __construct(
        public CarbonImmutable $month,

        /** @var Collection<int, CalendarWeek> */
        public Collection $weeks,
    ) {}
}