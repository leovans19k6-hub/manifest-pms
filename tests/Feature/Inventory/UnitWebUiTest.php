<?php

namespace Tests\Feature\Inventory;

use Database\Factories\OrganizationFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UnitFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Models\Permission;
use Domain\Inventory\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitWebUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $property = PropertyFactory::new()->create();

        $this->get("/admin/properties/{$property->id}/units")
            ->assertRedirect('/login');
    }

    public function test_index_is_tenant_scoped_and_permission_aware(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.view',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'name' => 'Visible Unit',
        ]);

        $foreignUnit = UnitFactory::new()->create([
            'name' => 'Foreign Unit',
        ]);

        $this->actingAs($user)
            ->get("/admin/properties/{$property->id}/units")
            ->assertOk()
            ->assertSee('Visible Unit')
            ->assertDontSee($foreignUnit->name)
            ->assertDontSee('Create Unit')
            ->assertDontSee('Edit')
            ->assertDontSee('>Archive<', false);
    }

    public function test_create_validation_and_success_flow_use_application_layer_and_audit(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.create',
            'inventory.units.update',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($user)
            ->post(
                "/admin/properties/{$property->id}/units",
                [
                    'code' => '',
                    'name' => '',
                    'slug' => '',
                ],
            )
            ->assertSessionHasErrors([
                'code',
                'name',
                'slug',
            ]);

        $response = $this->post(
            "/admin/properties/{$property->id}/units",
            $this->data(),
        );

        $unit = Unit::query()
            ->where('organization_id', $organization->id)
            ->where('code', 'WEB-UNIT-1')
            ->firstOrFail();

        $response->assertRedirect(
            route('admin.units.edit', $unit),
        );

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'inventory.unit.created',
            'auditable_id' => $unit->id,
        ]);
    }

    public function test_create_page_renders_unit_form_contract(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.create',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($user)
            ->get("/admin/properties/{$property->id}/units/create")
            ->assertOk()
            ->assertSee($property->name)
            ->assertSee('Create Unit')
            ->assertSee('name="code"', false)
            ->assertSee('name="type"', false)
            ->assertSee('name="base_occupancy"', false)
            ->assertSee('name="max_occupancy"', false);
    }

    public function test_edit_update_archive_flow_uses_application_layer_and_audit(): void
    {
        [$user, $organization] = $this->principal([
            'inventory.units.view',
            'inventory.units.update',
            'inventory.units.archive',
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $unit = UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'name' => 'Original Unit',
        ]);

        $this->actingAs($user);

        $this->get("/admin/units/{$unit->id}/edit")
            ->assertOk()
            ->assertSee('Original Unit');

        $this->put(
            "/admin/units/{$unit->id}",
            [
                'name' => 'Changed Unit',
            ],
        )->assertRedirect(
            route('admin.units.edit', $unit),
        );

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'name' => 'Changed Unit',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'inventory.unit.updated',
            'auditable_id' => $unit->id,
        ]);

        $this->delete("/admin/units/{$unit->id}")
            ->assertRedirect(
                route(
                    'admin.properties.units.index',
                    $property,
                ),
            );

        $this->assertSoftDeleted('units', [
            'id' => $unit->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'inventory.unit.archived',
            'auditable_id' => $unit->id,
        ]);
    }

    public function test_cross_tenant_property_pages_are_not_found(): void
    {
        [$user] = $this->principal([
            'inventory.units.view',
            'inventory.units.create',
        ]);

        $foreignProperty = PropertyFactory::new()->create();

        $this->actingAs($user);

        $this->get(
            "/admin/properties/{$foreignProperty->id}/units",
        )->assertNotFound();

        $this->get(
            "/admin/properties/{$foreignProperty->id}/units/create",
        )->assertNotFound();

        $this->post(
            "/admin/properties/{$foreignProperty->id}/units",
            $this->data(),
        )->assertNotFound();
    }

    public function test_cross_tenant_unit_actions_are_unprocessable(): void
    {
        [$user] = $this->principal([
            'inventory.units.update',
            'inventory.units.archive',
        ]);

        $foreignUnit = UnitFactory::new()->create();

        $this->actingAs($user);

        $this->get("/admin/units/{$foreignUnit->id}/edit")
            ->assertForbidden();

        $this->put(
            "/admin/units/{$foreignUnit->id}",
            [
                'name' => 'No Change',
            ],
        )
            ->assertForbidden();

        $this->delete("/admin/units/{$foreignUnit->id}")
            ->assertForbidden();
    }

    public function test_each_web_action_requires_its_permission(): void
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

        $this->get("/admin/properties/{$property->id}/units")
            ->assertForbidden();

        $this->get(
            "/admin/properties/{$property->id}/units/create",
        )->assertForbidden();

        $this->post(
            "/admin/properties/{$property->id}/units",
            $this->data(),
        )->assertForbidden();

        $this->get("/admin/units/{$unit->id}/edit")
            ->assertForbidden();

        $this->put(
            "/admin/units/{$unit->id}",
            [
                'name' => 'No Change',
            ],
        )->assertForbidden();

        $this->delete("/admin/units/{$unit->id}")
            ->assertForbidden();
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
            'joined_at' => now(),
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

    private function data(): array
    {
        return [
            'code' => 'WEB-UNIT-1',
            'name' => 'Web Unit',
            'slug' => 'web-unit-1',
            'type' => 'room',
            'status' => 'active',
            'capacity_adults' => 2,
            'capacity_children' => 0,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'base_occupancy' => 1,
            'max_occupancy' => 2,
            'sort_order' => 0,
            'description' => 'Unit created from web UI.',
        ];
    }
}
