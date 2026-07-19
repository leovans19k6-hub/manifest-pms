<?php

namespace Tests\Feature\Reservation;

use Database\Factories\OrganizationFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\ReservationFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UnitFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationHttpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_reservation_api_is_json_unauthorized(): void
    {
        $unit = UnitFactory::new()->create();

        $this->getJson("/api/v1/units/{$unit->id}/reservations")
            ->assertUnauthorized();
    }

    public function test_crud_happy_path_lists_shows_updates_and_cancels(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.view',
            'reservation.reservations.view',
            'reservation.reservations.create',
            'reservation.reservations.update',
            'reservation.reservations.cancel',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $unit = UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
        ]);

        $this->actingAs($user);
        $created = $this->postJson(
            "/api/v1/units/{$unit->id}/reservations",
            [
                'code' => 'RES-0001',
                'status' => 'reserved',
                'source' => 'website',

                'guest_name' => 'Nguyen Van A',
                'guest_phone' => '0901234567',
                'guest_email' => 'guest@example.com',

                'adults' => 2,
                'children' => 1,

                'check_in' => now()->addDay()->toISOString(),
                'check_out' => now()->addDays(3)->toISOString(),

                'notes' => 'API reservation',

                'metadata' => [
                    'channel' => 'website',
                ],

                'organization_id' => 'forbidden',
                'property_id' => 'forbidden',
                'unit_id' => 'forbidden',
            ],
        )
            ->assertCreated()
            ->assertJsonPath('data.code', 'RES-0001')
            ->assertJsonPath('data.unit_id', $unit->id)
            ->json('data.id');

        $this->assertDatabaseHas('reservations', [
            'id' => $created,
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'code' => 'RES-0001',
        ]);

        ReservationFactory::new()->create([
			'organization_id' => $organization->id,
			'property_id' => $property->id,
			'unit_id' => $unit->id,
			'code' => 'RES-0002',

			'check_in' => now()->addDays(10),
			'check_out' => now()->addDays(12),
		]);

        $this->getJson(
            "/api/v1/units/{$unit->id}/reservations",
        )
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $created)
            ->assertJsonPath('data.1.code', 'RES-0002');

        $this->getJson(
            "/api/v1/reservations/{$created}",
        )
            ->assertOk()
            ->assertJsonPath('data.id', $created);

        $this->patchJson(
            "/api/v1/reservations/{$created}",
            [
                'guest_name' => 'Tran Van B',
                'organization_id' => 'forbidden',
                'property_id' => 'forbidden',
                'unit_id' => 'forbidden',
            ],
        )
            ->assertOk()
            ->assertJsonPath(
                'data.guest_name',
                'Tran Van B',
            );

        $this->deleteJson(
            "/api/v1/reservations/{$created}",
        )->assertNoContent();

        $this->assertDatabaseHas('reservations', [
            'id' => $created,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'reservation.created',
            'auditable_id' => $created,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'reservation.updated',
            'auditable_id' => $created,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'reservation.cancelled',
            'auditable_id' => $created,
        ]);
    }

    public function test_create_validation_errors_are_standard_json(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.view',
            'reservation.reservations.create',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $unit = UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
        ]);

        $this->actingAs($user);

        $this->postJson(
            "/api/v1/units/{$unit->id}/reservations",
            [
                'code' => '',
                'status' => 'bad',
                'source' => 'bad',

                'guest_name' => '',
                'guest_email' => 'invalid-email',

                'adults' => 0,
                'children' => -1,

                'check_in' => '',
                'check_out' => '',

                'id' => 'forced',
                'organization_id' => 'forbidden',
                'property_id' => 'forbidden',
                'unit_id' => 'forbidden',
            ],
        )
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors',
            ])
            ->assertJsonValidationErrors([
                'code',
                'status',
                'source',
                'guest_name',
                'guest_email',
                'adults',
                'children',
                'check_in',
                'check_out',
            ]);
    }

    public function test_permission_denial_is_json_forbidden(): void
    {
        [$user, $organization] = $this->principal([]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $unit = UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
        ]);

        $this->actingAs($user);

        $this->getJson(
            "/api/v1/units/{$unit->id}/reservations",
        )
            ->assertForbidden()
            ->assertJson([
                'error' => [
                    'code' => 'permission_denied',
                    'message' => 'Missing required permission [reservation.reservations.view].',
                ],
            ]);
    }

    public function test_cross_tenant_list_and_create_are_unprocessable(): void
    {
        [$user] = $this->principal([
            'inventory.units.view',
            'reservation.reservations.view',
            'reservation.reservations.create',
        ]);

        $foreignUnit = UnitFactory::new()->create();

        $this->actingAs($user);

        $this->getJson(
            "/api/v1/units/{$foreignUnit->id}/reservations",
        )->assertUnprocessable()
            ->assertJsonValidationErrors('unit');

        $this->postJson(
            "/api/v1/units/{$foreignUnit->id}/reservations",
            [
                'code' => 'FOREIGN-RES',

                'status' => 'reserved',
                'source' => 'website',

                'guest_name' => 'Foreign Guest',

                'adults' => 2,
                'children' => 0,

                'check_in' => now()->addDay()->toISOString(),
                'check_out' => now()->addDays(2)->toISOString(),
            ],
        )->assertUnprocessable()
            ->assertJsonValidationErrors('unit');
    }

    public function test_cross_tenant_show_update_cancel_are_unprocessable(): void
    {
        [$user] = $this->principal([
            'reservation.reservations.view',
            'reservation.reservations.update',
            'reservation.reservations.cancel',
        ]);

        $reservation = ReservationFactory::new()->create();

        $this->actingAs($user);

        $this->getJson(
            "/api/v1/reservations/{$reservation->id}",
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reservation');

        $this->patchJson(
            "/api/v1/reservations/{$reservation->id}",
            [
                'guest_name' => 'No Permission',
            ],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reservation');

        $this->deleteJson(
            "/api/v1/reservations/{$reservation->id}",
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reservation');
    }

    public function test_duplicate_code_returns_json_validation_errors(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.view',
            'reservation.reservations.create',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $unit = UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
        ]);

        ReservationFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'code' => 'RES-0001',
        ]);

        $this->actingAs($user);

        $this->postJson(
            "/api/v1/units/{$unit->id}/reservations",
            [
                'code' => 'RES-0001',
                'status' => 'reserved',
                'source' => 'website',

                'guest_name' => 'Duplicate',

                'adults' => 2,
                'children' => 0,

                'check_in' => now()->addDay()->toISOString(),
                'check_out' => now()->addDays(3)->toISOString(),
            ],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');
    }

    public function test_reservation_conflict_returns_json_validation_errors(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.view',
            'reservation.reservations.create',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $unit = UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
        ]);

        ReservationFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,

            'check_in' => now()->addDay(),
            'check_out' => now()->addDays(3),
        ]);

        $this->actingAs($user);

        $this->postJson(
            "/api/v1/units/{$unit->id}/reservations",
            [
                'code' => 'RES-9999',

                'status' => 'reserved',
                'source' => 'website',

                'guest_name' => 'Conflict Guest',

                'adults' => 2,
                'children' => 0,

                'check_in' => now()->addDays(2)->toISOString(),
                'check_out' => now()->addDays(4)->toISOString(),
            ],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('check_in');
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
