<?php

namespace Tests\Feature\Inventory;

use Database\Factories\OrganizationFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UnitFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitHttpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_unit_api_is_json_unauthorized(): void
    {
        $property = PropertyFactory::new()->create();

        $this->getJson("/api/v1/properties/{$property->id}/units")
            ->assertUnauthorized();
    }

    public function test_crud_happy_path_lists_audits_and_archives(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.view',
            'inventory.units.create',
            'inventory.units.update',
            'inventory.units.archive',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($user);

        $created = $this->postJson(
            "/api/v1/properties/{$property->id}/units",
            [
                'code' => 'ROOM-101',
                'name' => 'Room 101',
                'slug' => 'room-101',
                'type' => 'room',
                'status' => 'active',
                'capacity_adults' => 2,
                'capacity_children' => 1,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'base_occupancy' => 1,
                'max_occupancy' => 3,
                'sort_order' => 10,
                'description' => 'API unit',
                'metadata' => [
                    'floor' => 1,
                ],
                'organization_id' => 'forbidden',
                'property_id' => 'forbidden',
            ],
        )
            ->assertCreated()
            ->assertJsonPath('data.code', 'ROOM-101')
            ->assertJsonPath('data.property_id', $property->id)
            ->json('data.id');

        $this->assertDatabaseHas('units', [
            'id' => $created,
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'code' => 'ROOM-101',
        ]);

        UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'code' => 'ROOM-102',
            'name' => 'Search Me',
            'slug' => 'room-102',
            'type' => 'room',
            'status' => 'draft',
            'sort_order' => 20,
        ]);

        $this->getJson(
            "/api/v1/properties/{$property->id}/units",
        )
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $created)
            ->assertJsonPath('data.1.code', 'ROOM-102');

        $this->getJson("/api/v1/units/{$created}")
            ->assertOk()
            ->assertJsonPath('data.id', $created);

        $this->patchJson(
            "/api/v1/units/{$created}",
            [
                'name' => 'Updated Unit',
                'organization_id' => 'forbidden',
                'property_id' => 'forbidden',
            ],
        )
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Unit');

        $this->deleteJson("/api/v1/units/{$created}")
            ->assertNoContent();

        $this->assertDatabaseHas('units', [
            'id' => $created,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'inventory.unit.created',
            'auditable_id' => $created,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'inventory.unit.updated',
            'auditable_id' => $created,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'inventory.unit.archived',
            'auditable_id' => $created,
        ]);
    }

    public function test_create_validation_errors_are_standard_json_and_system_fields_are_ignored(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.create',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($user);

        $this->postJson(
            "/api/v1/properties/{$property->id}/units",
            [
                'code' => '',
                'name' => '',
                'slug' => '',
                'type' => 'bad',
                'status' => 'bad',
                'capacity_adults' => -1,
                'base_occupancy' => 3,
                'max_occupancy' => 2,
                'id' => 'forced',
                'organization_id' => 'forbidden',
                'property_id' => 'forbidden',
            ],
        )
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors',
            ])
            ->assertJsonValidationErrors([
                'code',
                'name',
                'slug',
                'type',
                'status',
                'capacity_adults',
            ]);
    }

    public function test_permission_denial_is_json_forbidden(): void
    {
        [$user, $organization] = $this->principal([]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($user);

        $this->getJson("/api/v1/properties/{$property->id}/units")
            ->assertForbidden()
            ->assertJson([
                'error' => [
                    'code' => 'permission_denied',
                    'message' => 'Missing required permission [inventory.units.view].',
                ],
            ]);
    }

    public function test_cross_tenant_property_list_and_create_are_not_found(): void
    {
        [$user] = $this->principal([
            'inventory.units.view',
            'inventory.units.create',
        ]);

        $foreignProperty = PropertyFactory::new()->create();

        $this->actingAs($user);

        $this->getJson(
            "/api/v1/properties/{$foreignProperty->id}/units",
        )->assertNotFound();

        $this->postJson(
            "/api/v1/properties/{$foreignProperty->id}/units",
            [
                'code' => 'FOREIGN-1',
                'name' => 'Foreign Unit',
                'slug' => 'foreign-unit',
                'type' => 'room',
                'status' => 'active',
                'capacity_adults' => 2,
                'capacity_children' => 0,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'base_occupancy' => 1,
                'max_occupancy' => 2,
                'sort_order' => 0,
            ],
        )->assertNotFound();
    }

    public function test_cross_tenant_show_update_archive_are_unprocessable(): void
    {
        [$user] = $this->principal([
            'inventory.units.view',
            'inventory.units.update',
            'inventory.units.archive',
        ]);

        $foreignUnit = UnitFactory::new()->create();

        $this->actingAs($user);

        $this->getJson("/api/v1/units/{$foreignUnit->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('unit');

        $this->patchJson(
            "/api/v1/units/{$foreignUnit->id}",
            [
                'name' => 'No',
            ],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('unit');

        $this->deleteJson("/api/v1/units/{$foreignUnit->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('unit');
    }

    public function test_filter_contract_returns_json_validation_errors(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.view',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($user);

        $this->getJson(
            "/api/v1/properties/{$property->id}/units"
            .'?sort=organization_id&per_page=101'
            .'&type=bad&status=bad&direction=sideways',
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'sort',
                'per_page',
                'type',
                'status',
                'direction',
            ]);
    }

    public function test_duplicate_code_and_slug_return_json_validation_errors(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.create',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'code' => 'ROOM-101',
            'slug' => 'room-101',
        ]);

        $this->actingAs($user);

        $this->postJson(
            "/api/v1/properties/{$property->id}/units",
            [
                'code' => 'ROOM-101',
                'name' => 'Duplicate Code',
                'slug' => 'different-slug',
                'type' => 'room',
                'status' => 'draft',
                'capacity_adults' => 2,
                'capacity_children' => 0,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'base_occupancy' => 1,
                'max_occupancy' => 2,
                'sort_order' => 0,
            ],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');

        $this->postJson(
            "/api/v1/properties/{$property->id}/units",
            [
                'code' => 'ROOM-102',
                'name' => 'Duplicate Slug',
                'slug' => 'room-101',
                'type' => 'room',
                'status' => 'draft',
                'capacity_adults' => 2,
                'capacity_children' => 0,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'base_occupancy' => 1,
                'max_occupancy' => 2,
                'sort_order' => 0,
            ],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slug');
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
