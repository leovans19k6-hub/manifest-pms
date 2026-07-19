<?php

namespace Domain\Reservation\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuditLogger;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Inventory\Models\Unit;
use Domain\Reservation\Models\Reservation;
use Domain\Reservation\Enums\ReservationStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Events\ReservationCheckedOut;

final class ReservationService
{
    public function __construct(
		private CurrentOrganization $organization,
		private AuthorizationService $authorization,
		private AuditLogger $audit,
	) {}

    public function create(
        OrganizationUser $membership,
        Unit $unit,
        array $attributes,
    ): Reservation {
        $this->authorize($membership, 'reservation.reservations.create');
        $this->assertCurrentUnit($membership, $unit);

        return DB::transaction(function () use ($unit, $attributes): Reservation {
            $reservation = Reservation::query()->create([
                ...$attributes,
                'organization_id' => $this->requireOrganizationId(),
                'property_id' => $unit->property_id,
                'unit_id' => $unit->id,
            ]);

            $this->audit->record(
                'reservation.created',
                $reservation,
                [],
                $reservation->getAttributes(),
            );

            return $reservation;
        });
    }

    public function update(
        OrganizationUser $membership,
        Reservation $reservation,
        array $attributes,
    ): Reservation {
        $this->authorize($membership, 'reservation.reservations.update');
        $this->assertCurrentReservation($membership, $reservation);

        return DB::transaction(function () use ($reservation, $attributes): Reservation {
            $old = $reservation->getAttributes();

            $reservation->fill($attributes)->save();

            $this->audit->record(
                'reservation.updated',
                $reservation,
                $old,
                $reservation->getAttributes(),
            );

            return $reservation->refresh();
        });
    }

    public function cancel(
        OrganizationUser $membership,
        Reservation $reservation,
    ): void {
        $this->authorize($membership, 'reservation.reservations.cancel');
        $this->assertCurrentReservation($membership, $reservation);

        DB::transaction(function () use ($reservation): void {
            $old = $reservation->getAttributes();

			$reservation->status = ReservationStatus::Cancelled;
			$reservation->save();

			$this->audit->record(
				'reservation.cancelled',
				$reservation,
				$old,
				$reservation->getAttributes(),
			);
        });
    }
	
	public function checkIn(
		OrganizationUser $membership,
		Reservation $reservation,
	): Reservation {
		$this->authorize($membership, 'reservation.reservations.update');
		$this->assertCurrentReservation($membership, $reservation);

		if (
			! in_array(
				$reservation->status,
				[
					ReservationStatus::Reserved,
					ReservationStatus::Confirmed,
				],
				true,
			)
		) {
			throw ValidationException::withMessages([
				'reservation' => 'Reservation cannot be checked in.',
			]);
		}

		if (
			now()->startOfDay()->lt(
				$reservation->check_in->startOfDay()
			)
		) {
			throw ValidationException::withMessages([
				'reservation' => 'Guest cannot check in before arrival date.',
			]);
		}

		return DB::transaction(function () use ($reservation): Reservation {
			$old = $reservation->getAttributes();

			$reservation->status = ReservationStatus::CheckedIn;
			$reservation->save();

			$this->audit->record(
				'reservation.checked_in',
				$reservation,
				$old,
				$reservation->getAttributes(),
			);

			return $reservation->refresh();
		});
	}
	
	public function checkOut(
		OrganizationUser $membership,
		Reservation $reservation,
	): Reservation {
		$this->authorize($membership, 'reservation.reservations.update');
		$this->assertCurrentReservation($membership, $reservation);

		if ($reservation->status !== ReservationStatus::CheckedIn) {
			throw ValidationException::withMessages([
				'reservation' => 'Reservation cannot be checked out.',
			]);
		}

		return DB::transaction(function () use ($reservation): Reservation {
			$old = $reservation->getAttributes();

			$reservation->status = ReservationStatus::CheckedOut;
			$reservation->save();
			ReservationCheckedOut::dispatch($reservation);

			$this->audit->record(
				'reservation.checked_out',
				$reservation,
				$old,
				$reservation->getAttributes(),
			);

			return $reservation->refresh();
		});
	}

    private function authorize(
        OrganizationUser $membership,
        string $permission,
    ): void {
        abort_unless(
            $this->authorization->can($membership, $permission),
            403,
        );
    }

    private function assertCurrentUnit(
        OrganizationUser $membership,
        Unit $unit,
    ): void {
        $organizationId = $this->requireOrganizationId();

        if (
            $membership->organization_id !== $organizationId
            || $unit->organization_id !== $organizationId
        ) {
            throw ValidationException::withMessages([
                'unit' => 'Unit does not belong to the current organization.',
            ]);
        }
    }

    private function assertCurrentReservation(
        OrganizationUser $membership,
        Reservation $reservation,
    ): void {
        $organizationId = $this->requireOrganizationId();

        if (
            $membership->organization_id !== $organizationId
            || $reservation->organization_id !== $organizationId
        ) {
            throw ValidationException::withMessages([
                'reservation' => 'Reservation does not belong to the current organization.',
            ]);
        }
    }

    private function requireOrganizationId(): string
    {
        return $this->organization->id()
            ?? throw ValidationException::withMessages([
                'organization' => 'Current organization context is required.',
            ]);
    }
}
