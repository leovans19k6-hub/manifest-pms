<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyHttpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_api_is_json_unauthorized(): void
    {
        $this->getJson('/api/v1/properties')->assertUnauthorized()->assertJsonStructure(['message']);
    }

    public function test_crud_happy_path_filters_paginates_audits_and_archives(): void
    {
        [$user,$org] = $this->principal(['property.properties.view', 'property.properties.create', 'property.properties.update', 'property.properties.archive']);
        $this->actingAs($user);
        $created = $this->postJson('/api/v1/properties', ['code' => 'API-1', 'name' => 'API Property', 'slug' => 'api-property', 'type' => 'villa', 'status' => 'active', 'organization_id' => 'forbidden'])
            ->assertCreated()->assertJsonPath('data.code', 'API-1')->json('data.id');
        $this->assertDatabaseHas('properties', ['id' => $created, 'organization_id' => $org->id]);
        PropertyFactory::new()->create(['organization_id' => $org->id, 'name' => 'Search Me', 'status' => 'draft']);
        $this->getJson('/api/v1/properties?search=Search&status=draft&sort=name&direction=desc&per_page=1')->assertOk()->assertJsonCount(1, 'data')->assertJsonStructure(['data', 'links', 'meta']);
        $this->getJson("/api/v1/properties/$created")->assertOk()->assertJsonPath('data.id', $created);
        $this->patchJson("/api/v1/properties/$created", ['name' => 'Updated', 'organization_id' => 'forbidden'])->assertOk()->assertJsonPath('data.name', 'Updated');
        $this->deleteJson("/api/v1/properties/$created")->assertNoContent();
        $this->assertSoftDeleted('properties', ['id' => $created]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.created', 'auditable_id' => $created]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.updated', 'auditable_id' => $created]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.archived', 'auditable_id' => $created]);
    }

    public function test_validation_errors_are_standard_json_and_system_fields_are_ignored(): void
    {
        [$user] = $this->principal(['property.properties.create']);
        $this->actingAs($user);
        $this->postJson('/api/v1/properties', ['code' => '', 'name' => '', 'slug' => '', 'type' => 'bad', 'id' => 'forced'])->assertUnprocessable()->assertJsonStructure(['message', 'errors']);
    }

    public function test_permission_denial_is_json_forbidden(): void
    {
        [$user] = $this->principal([]);
        $this->actingAs($user);
        $this->getJson('/api/v1/properties')->assertForbidden()->assertJsonStructure(['message']);
    }

    public function test_cross_tenant_show_update_archive_are_not_found(): void
    {
        [$user] = $this->principal(['property.properties.view', 'property.properties.update', 'property.properties.archive']);
        $other = PropertyFactory::new()->create();
        $this->actingAs($user);
        $this->getJson("/api/v1/properties/$other->id")->assertNotFound();
        $this->patchJson("/api/v1/properties/$other->id", ['name' => 'No'])->assertNotFound();
        $this->deleteJson("/api/v1/properties/$other->id")->assertNotFound();
    }

    public function test_filter_contract_returns_json_validation_errors(): void
    {
        [$user] = $this->principal(['property.properties.view']);
        $this->actingAs($user);
        $this->getJson('/api/v1/properties?sort=organization_id&per_page=101')->assertUnprocessable()->assertJsonValidationErrors(['sort', 'per_page']);
    }

    private function principal(array $codes): array
    {
        $org = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        $membership = OrganizationUser::create(['organization_id' => $org->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);
        if ($codes) {
            $role = RoleFactory::new()->create(['organization_id' => $org->id]);
            foreach ($codes as $code) {
                $p = PermissionFactory::new()->create(['code' => $code]);
                $role->permissions()->attach($p);
            } $membership->roles()->attach($role);
        }

        return [$user, $org, $membership];
    }
}
