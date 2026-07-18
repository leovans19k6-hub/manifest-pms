<?php

namespace Domain\Availability\Services;

use Carbon\CarbonImmutable;
use Domain\Availability\DTO\AvailabilityDay;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Inventory\Models\Unit;
use Domain\Reservation\Models\Reservation;
use Domain\Reservation\Enums\ReservationStatus;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Domain\Foundation\Calendar\CalendarBuilder;
use Domain\Foundation\Calendar\DTO\CalendarMonth;
use Domain\Availability\Enums\AvailabilityStatus;

final class AvailabilityQueryService
{
    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
    ) {}

    /**
     * @return Collection<int, AvailabilityDay>
     */
    public function timeline(
        OrganizationUser $membership,
        Unit $unit,
        CarbonImmutable $start,
        int $days = 30,
    ): Collection {
        $this->authorize($membership);

        $organizationId = $this->requireOrganizationId();

        if (
            $membership->organization_id !== $organizationId
            || $unit->organization_id !== $organizationId
        ) {
            throw ValidationException::withMessages([
                'unit' => 'Unit does not belong to the current organization.',
            ]);
        }

        $end = $start->addDays($days);

        $reservations = Reservation::query()
            ->where('organization_id', $organizationId)
            ->where('unit_id', $unit->id)
            ->where('check_in', '<', $end)
            ->where('check_out', '>', $start)
            ->orderBy('check_in')
            ->get();

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($start, $reservations): AvailabilityDay {

                $date = $start->addDays($offset);

                /** @var Reservation|null $reservation */
                $reservation = $reservations->first(function (Reservation $reservation) use ($date) {

                    return $reservation->check_in->startOfDay() <= $date
                        && $reservation->check_out->startOfDay() > $date;
                });

                if ($reservation instanceof Reservation) {

					$status = match ($reservation->status) {
						ReservationStatus::Reserved,
						ReservationStatus::Confirmed
							=> AvailabilityStatus::Reserved,

						ReservationStatus::CheckedIn
							=> AvailabilityStatus::CheckedIn,

						ReservationStatus::Cancelled,
						ReservationStatus::CheckedOut,
						ReservationStatus::NoShow
							=> AvailabilityStatus::Available,
					};

					return new AvailabilityDay(
						date: $date,
						status: $status,
						reservation: $status === AvailabilityStatus::Available
							? null
							: $reservation,
					);
				}

                return new AvailabilityDay(
                    date: $date,
                    status: AvailabilityStatus::Available,
                );
            });
    }

	/**
	 * Build monthly calendar structure for a unit.
	 */
	public function calendar(
		OrganizationUser $membership,
		Unit $unit,
		CarbonImmutable $month,
	): CalendarMonth {
		$this->authorize($membership);

		$organizationId = $this->requireOrganizationId();

		if (
			$membership->organization_id !== $organizationId
			|| $unit->organization_id !== $organizationId
		) {
			throw ValidationException::withMessages([
				'unit' => 'Unit does not belong to the current organization.',
			]);
		}

		$calendar = CalendarBuilder::build(
			$month->startOfMonth(),
		);

		/*
		 * Load reservations overlapping the visible calendar range.
		 * This prepares data for the calendar UI without coupling the
		 * Calendar domain model to Availability.
		 */
		Reservation::query()
			->where('organization_id', $organizationId)
			->where('unit_id', $unit->id)
			->where(
				'check_in',
				'<',
				$month
					->endOfMonth()
					->endOfWeek(CarbonImmutable::SUNDAY),
			)
			->where(
				'check_out',
				'>',
				$month
					->startOfMonth()
					->startOfWeek(CarbonImmutable::MONDAY),
			)
			->orderBy('check_in')
			->get();

		return $calendar;
	}

    private function authorize(
        OrganizationUser $membership,
    ): void {
        abort_unless(
            $this->authorization->can(
                $membership,
                'reservation.reservations.view',
            ),
            403,
        );
    }

    private function requireOrganizationId(): string
    {
        return $this->organization->id()
            ?? throw ValidationException::withMessages([
                'organization' => 'Current organization context is required.',
            ]);
    }
}