<?php

namespace Tests\Feature\Availability;

use Carbon\CarbonImmutable;
use Database\Factories\OrganizationFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\ReservationFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UnitFactory;
use Database\Factories\UserFactory;
use Domain\Availability\DTO\AvailabilityDay;
use Domain\Availability\Services\AvailabilityQueryService;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Models\Permission;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Reservation\Enums\ReservationStatus;
use Domain\Availability\Enums\AvailabilityStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserved_reservation_marks_days_as_reserved(): void
	{
		[$user, $organization, $membership] = $this->principal([
			'reservation.reservations.view',
		]);

		app(CurrentOrganization::class)->set($organization);

		$property = PropertyFactory::new()->create([
			'organization_id' => $organization->id,
		]);

		$unit = UnitFactory::new()->create([
			'organization_id' => $organization->id,
			'property_id' => $property->id,
		]);

		$start = CarbonImmutable::parse('2026-07-01');

		ReservationFactory::new()
			->forUnit($unit)
			->status(ReservationStatus::Reserved)
			->create([
				'organization_id' => $organization->id,
				'property_id' => $property->id,
				'check_in' => $start->addDay(),
				'check_out' => $start->addDays(3),
				'code' => 'RES-0001',
			]);

		$days = app(AvailabilityQueryService::class)->timeline(
			$membership,
			$unit,
			$start,
			5,
		);

		$this->assertCount(5, $days);

		$this->assertSame(
			AvailabilityStatus::Available,
			$days[0]->status,
		);

		$this->assertSame(
			AvailabilityStatus::Reserved,
			$days[1]->status,
		);

		$this->assertSame(
			'RES-0001',
			$days[1]->reservation?->code,
		);
	}

    public function test_confirmed_reservation_marks_days_as_reserved(): void
    {
		[$user, $organization, $membership] = $this->principal([
			'reservation.reservations.view',
		]);

		app(CurrentOrganization::class)->set($organization);

		$property = PropertyFactory::new()->create([
			'organization_id' => $organization->id,
		]);

		$unit = UnitFactory::new()->create([
			'organization_id' => $organization->id,
			'property_id' => $property->id,
		]);

		$start = CarbonImmutable::parse('2026-07-01');

		ReservationFactory::new()
			->forUnit($unit)
			->status(ReservationStatus::Confirmed)
			->create([
				'organization_id' => $organization->id,
				'property_id' => $property->id,
				'check_in' => $start->addDay(),
				'check_out' => $start->addDays(3),
				'code' => 'RES-0001',
			]);

		$days = app(AvailabilityQueryService::class)->timeline(
			$membership,
			$unit,
			$start,
			5,
		);

		$this->assertCount(5, $days);

		$this->assertSame(
			AvailabilityStatus::Available,
			$days[0]->status,
		);

		$this->assertSame(
			AvailabilityStatus::Reserved,
			$days[1]->status,
		);

		$this->assertSame(
			'RES-0001',
			$days[1]->reservation?->code,
		);
    }

    public function test_checked_in_reservation_marks_days_as_checked_in(): void
    {
		[$user, $organization, $membership] = $this->principal([
			'reservation.reservations.view',
		]);

		app(CurrentOrganization::class)->set($organization);

		$property = PropertyFactory::new()->create([
			'organization_id' => $organization->id,
		]);

		$unit = UnitFactory::new()->create([
			'organization_id' => $organization->id,
			'property_id' => $property->id,
		]);

		$start = CarbonImmutable::parse('2026-07-01');

		ReservationFactory::new()
			->forUnit($unit)
			->status(ReservationStatus::CheckedIn)
			->create([
				'organization_id' => $organization->id,
				'property_id' => $property->id,
				'check_in' => $start->addDay(),
				'check_out' => $start->addDays(3),
				'code' => 'RES-0001',
			]);

		$days = app(AvailabilityQueryService::class)->timeline(
			$membership,
			$unit,
			$start,
			5,
		);

		$this->assertCount(5, $days);

		$this->assertSame(
			AvailabilityStatus::Available,
			$days[0]->status,
		);

		$this->assertSame(
			AvailabilityStatus::CheckedIn,
			$days[1]->status,
		);

		$this->assertSame(
			'RES-0001',
			$days[1]->reservation?->code,
		);
    }

    public function test_cancelled_reservation_does_not_occupy_room(): void
    {
		[$user, $organization, $membership] = $this->principal([
			'reservation.reservations.view',
		]);

		app(CurrentOrganization::class)->set($organization);

		$property = PropertyFactory::new()->create([
			'organization_id' => $organization->id,
		]);

		$unit = UnitFactory::new()->create([
			'organization_id' => $organization->id,
			'property_id' => $property->id,
		]);

		$start = CarbonImmutable::parse('2026-07-01');

		ReservationFactory::new()
			->forUnit($unit)
			->status(ReservationStatus::Cancelled)
			->create([
				'organization_id' => $organization->id,
				'property_id' => $property->id,
				'check_in' => $start->addDay(),
				'check_out' => $start->addDays(3),
				'code' => 'RES-0001',
			]);

		$days = app(AvailabilityQueryService::class)->timeline(
			$membership,
			$unit,
			$start,
			5,
		);

		$this->assertCount(5, $days);

		$this->assertSame(
			AvailabilityStatus::Available,
			$days[0]->status,
		);

		$this->assertSame(
			AvailabilityStatus::Available,
			$days[1]->status,
		);

		$this->assertNull($days[1]->reservation);
    }

    public function test_checked_out_reservation_does_not_occupy_room(): void
    {
		[$user, $organization, $membership] = $this->principal([
			'reservation.reservations.view',
		]);

		app(CurrentOrganization::class)->set($organization);

		$property = PropertyFactory::new()->create([
			'organization_id' => $organization->id,
		]);

		$unit = UnitFactory::new()->create([
			'organization_id' => $organization->id,
			'property_id' => $property->id,
		]);

		$start = CarbonImmutable::parse('2026-07-01');

		ReservationFactory::new()
			->forUnit($unit)
			->status(ReservationStatus::CheckedOut)
			->create([
				'organization_id' => $organization->id,
				'property_id' => $property->id,
				'check_in' => $start->addDay(),
				'check_out' => $start->addDays(3),
				'code' => 'RES-0001',
			]);

		$days = app(AvailabilityQueryService::class)->timeline(
			$membership,
			$unit,
			$start,
			5,
		);

		$this->assertCount(5, $days);

		$this->assertSame(
			AvailabilityStatus::Available,
			$days[0]->status,
		);

		$this->assertSame(
			AvailabilityStatus::Available,
			$days[1]->status,
		);

		$this->assertNull($days[1]->reservation);
    }

    public function test_no_show_reservation_does_not_occupy_room(): void
    {
		[$user, $organization, $membership] = $this->principal([
			'reservation.reservations.view',
		]);

		app(CurrentOrganization::class)->set($organization);

		$property = PropertyFactory::new()->create([
			'organization_id' => $organization->id,
		]);

		$unit = UnitFactory::new()->create([
			'organization_id' => $organization->id,
			'property_id' => $property->id,
		]);

		$start = CarbonImmutable::parse('2026-07-01');

		ReservationFactory::new()
			->forUnit($unit)
			->status(ReservationStatus::NoShow)
			->create([
				'organization_id' => $organization->id,
				'property_id' => $property->id,
				'check_in' => $start->addDay(),
				'check_out' => $start->addDays(3),
				'code' => 'RES-0001',
			]);

		$days = app(AvailabilityQueryService::class)->timeline(
			$membership,
			$unit,
			$start,
			5,
		);

		$this->assertCount(5, $days);

		$this->assertSame(
			AvailabilityStatus::Available,
			$days[0]->status,
		);

		$this->assertSame(
			AvailabilityStatus::Available,
			$days[1]->status,
		);

		$this->assertNull($days[1]->reservation);
    }

    public function test_reservations_outside_requested_period_are_ignored(): void
    {
		[$user, $organization, $membership] = $this->principal([
			'reservation.reservations.view',
		]);

		app(CurrentOrganization::class)->set($organization);

		$property = PropertyFactory::new()->create([
			'organization_id' => $organization->id,
		]);

		$unit = UnitFactory::new()->create([
			'organization_id' => $organization->id,
			'property_id' => $property->id,
		]);

		$start = CarbonImmutable::parse('2026-07-01');

		ReservationFactory::new()
			->forUnit($unit)
			->status(ReservationStatus::CheckedIn)
			->create([
				'organization_id' => $organization->id,
				'property_id' => $property->id,
				'check_in' => $start->addDay(),
				'check_out' => $start->addDays(3),
				'code' => 'RES-0001',
			]);

		$days = app(AvailabilityQueryService::class)->timeline(
			$membership,
			$unit,
			$start,
			5,
		);

		$this->assertCount(5, $days);

		$this->assertSame(
			AvailabilityStatus::Available,
			$days[0]->status,
		);

		$this->assertSame(
			AvailabilityStatus::CheckedIn,
			$days[1]->status,
		);

		$this->assertSame(
			'RES-0001',
			$days[1]->reservation?->code,
		);
    }
	
	private function principal(array $codes): array
{
    $organization = OrganizationFactory::new()->create();

    $user = UserFactory::new()->create();

    $membership = OrganizationUser::query()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'status' => 'active',
        'is_default' => true,
    ]);

    if ($codes !== []) {

        $role = RoleFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        foreach ($codes as $code) {

            $permission = Permission::query()
                ->where('code', $code)
                ->firstOrFail();

            $role->permissions()->attach($permission);
        }

        $membership->roles()->attach($role);
    }

    return [
        $user,
        $organization,
        $membership,
    ];
}
}