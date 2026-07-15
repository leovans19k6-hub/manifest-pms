<?php

namespace Domain\Reservation\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Inventory\Models\Unit;
use Domain\Reservation\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

final class ReservationQueryService
{
    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
    ) {}

    public function list(
        OrganizationUser $membership,
        Unit $unit,
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

        return Reservation::query()
            ->where('organization_id', $organizationId)
            ->where('unit_id', $unit->id)
            ->orderBy('check_in')
            ->orderBy('id')
            ->get();
    }

    public function find(
        OrganizationUser $membership,
        string $id,
    ): Reservation {
        $this->authorize($membership);

        $reservation = Reservation::query()->find($id);

        if (
            $reservation === null
            || $membership->organization_id !== $this->requireOrganizationId()
            || $reservation->organization_id !== $this->requireOrganizationId()
        ) {
            throw ValidationException::withMessages([
                'reservation' => 'Reservation does not belong to the current organization.',
            ]);
        }

        return $reservation;
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
