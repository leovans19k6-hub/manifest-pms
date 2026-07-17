<?php

namespace Domain\Availability\Services;

use Carbon\CarbonImmutable;
use Domain\Availability\DTO\AvailabilityDay;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Inventory\Models\Unit;
use Domain\Reservation\Models\Reservation;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

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
                    return new AvailabilityDay(
                        date: $date,
                        status: AvailabilityDay::RESERVED,
                        reservationCode: $reservation->code,
                    );
                }

                return new AvailabilityDay(
                    date: $date,
                    status: AvailabilityDay::AVAILABLE,
                );
            });
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