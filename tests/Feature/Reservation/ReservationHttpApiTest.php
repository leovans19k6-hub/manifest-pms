<?php

namespace Tests\Feature\Reservation;

use App\Models\User;
use Database\Factories\OrganizationFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\ReservationFactory;
use Database\Factories\UnitFactory;
use Domain\Foundation\Enums\OrganizationMemberStatus;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Models\Permission;
use Domain\Foundation\Models\Role;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Reservation\Enums\ReservationSource;
use Domain\Reservation\Enums\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReservationHttpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_list_reservations(): void
    {
        [$user, $membership, $property, $unit] = $this->contextWithPermission(
            'reservation.reservations.view',
        );

        ReservationFactory::new()->count(2)->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
        ]);

        ReservationFactory::new()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson(
            "/api/v1/units/{$unit->id}/reservations",
        );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_authorized_user_can_view_reservation(): void
    {
        [$user, $membership, $property, $unit] = $this->contextWithPermission(
            'reservation.reservations.view',
        );

        $reservation = ReservationFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson(
            "/api/v1/reservations/{$reservation->id}",
        )
            ->assertOk()
            ->assertJsonPath('data.id', $reservation->id)
            ->assertJsonPath('data.code', $reservation->code);
    }

    public function test_authorized_user_can_create_reservation(): void
    {
        [$user, $membership, $property, $unit] = $this->contextWithPermission(
            'reservation.reservations.create',
        );

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/v1/units/{$unit->id}/reservations",
            [
                'code' => 'RES-1001',
                'guest_name' => 'Nguyen Van A',
                'guest_phone' => '0901234567',
                'guest_email' => 'guest@example.com',
                'status' => ReservationStatus::Reserved->value,
                'source' => ReservationSource::Website->value,
                'adults' => 2,
                'children' => 1,
                'check_in' => now()->addDay()->toISOString(),
                'check_out' => now()->addDays(2)->toISOString(),
                'notes' => 'VIP Guest',
            ],
        );

        $response
            ->assertCreated()
            ->assertJsonPath(
                'data.code',
                'RES-1001',
            );

        $this->assertDatabaseHas(
            'reservations',
            [
                'organization_id' => $property->organization_id,
                'unit_id' => $unit->id,
                'code' => 'RES-1001',
            ],
        );
    }

    public function test_create_requires_permission(): void
    {
        [$user, $membership, $property, $unit] = $this->context();

        Sanctum::actingAs($user);

        $this->postJson(
            "/api/v1/units/{$unit->id}/reservations",
            [
                'code' => 'RES-1',
                'guest_name' => 'Guest',
                'check_in' => now()->addDay()->toISOString(),
                'check_out' => now()->addDays(2)->toISOString(),
            ],
        )->assertForbidden();
    }

    public function test_create_validates_required_fields(): void
    {
        [$user, $membership, $property, $unit] = $this->contextWithPermission(
            'reservation.reservations.create',
        );

        Sanctum::actingAs($user);

        $this->postJson(
            "/api/v1/units/{$unit->id}/reservations",
            [],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'code',
                'guest_name',
                'check_in',
                'check_out',
            ]);
    }

    public function test_authorized_user_can_update_reservation(): void
    {
        [$user, $membership, $property, $unit] = $this->contextWithPermission(
            'reservation.reservations.update',
        );

        $reservation = ReservationFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(
            "/api/v1/reservations/{$reservation->id}",
            [
                'guest_name' => 'Updated Guest',
                'adults' => 3,
                'notes' => 'Updated Note',
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.guest_name',
                'Updated Guest',
            );

        $this->assertDatabaseHas(
            'reservations',
            [
                'id' => $reservation->id,
                'guest_name' => 'Updated Guest',
                'adults' => 3,
            ],
        );
    }

    public function test_update_requires_permission(): void
    {
        [$user, $membership, $property, $unit] = $this->context();

        $reservation = ReservationFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
        ]);

        Sanctum::actingAs($user);

        $this->putJson(
            "/api/v1/reservations/{$reservation->id}",
            [
                'guest_name' => 'Denied',
            ],
        )->assertForbidden();
    }

    public function test_authorized_user_can_cancel_reservation(): void
    {
        [$user, $membership, $property, $unit] = $this->contextWithPermission(
            'reservation.reservations.cancel',
        );

        $reservation = ReservationFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson(
            "/api/v1/reservations/{$reservation->id}",
        )->assertNoContent();

        $this->assertSoftDeleted(
            'reservations',
            [
                'id' => $reservation->id,
            ],
        );
    }

    public function test_cancel_requires_permission(): void
    {
        [$user, $membership, $property, $unit] = $this->context();

        $reservation = ReservationFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson(
            "/api/v1/reservations/{$reservation->id}",
        )->assertForbidden();
    }

    private function contextWithPermission(
        string $permission,
    ): array {
        [$user, $membership, $property, $unit] = $this->context();

        $permissionModel = Permission::query()
            ->where('code', $permission)
            ->firstOrFail();

        $role = Role::query()->create([
            'organization_id' => $membership->organization_id,
            'code' => 'ROLE-'.str()->ulid(),
            'name' => 'Reservation Test Role',
            'scope' => 'organization',
            'status' => 'active',
            'is_system' => false,
        ]);

        $role->permissions()->attach(
            $permissionModel->id,
        );

        $membership->roles()->attach(
            $role->id,
        );

        return [
            $user,
            $membership->fresh(),
            $property,
            $unit,
        ];
    }

    private function context(): array
    {
        $organization = OrganizationFactory::new()->create();

        $user = User::factory()->create();

        $membership = OrganizationUser::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => OrganizationMemberStatus::Active->value,
            'is_default' => true,
            'joined_at' => now(),
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $unit = UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
        ]);

        app(CurrentOrganization::class)
            ->set($organization);

        return [
            $user,
            $membership,
            $property,
            $unit,
        ];
    }
}
