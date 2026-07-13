<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyWebUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/properties')->assertRedirect('/login');
    }

    public function test_index_is_tenant_scoped_filterable_paginated_and_permission_aware(): void
    {
        [$user,$org] = $this->principal(['property.properties.view']);
        PropertyFactory::new()->create(['organization_id' => $org->id, 'name' => 'Visible Search', 'status' => 'active']);
        PropertyFactory::new()->create();
        $this->actingAs($user)->get('/admin/properties?search=Visible&status=active&per_page=1')->assertOk()->assertSee('Visible Search')->assertDontSee('Thêm mới')->assertDontSee('>Sửa<', false);
    }

    public function test_create_validation_and_success_flow_use_application_layer_and_audit(): void
    {
        [$user,$org] = $this->principal(['property.properties.create', 'property.properties.update']);
        $this->actingAs($user)->post('/admin/properties', ['code' => '', 'name' => '', 'slug' => ''])->assertSessionHasErrors(['code', 'name', 'slug']);
        $response = $this->post('/admin/properties', ['code' => 'WEB-1', 'name' => 'Web Property', 'slug' => 'web-property', 'type' => 'villa', 'status' => 'active']);
        $p = Property::where('organization_id', $org->id)->where('code', 'WEB-1')->firstOrFail();
        $response->assertRedirect(route('admin.properties.edit', $p));
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.created', 'auditable_id' => $p->id]);
    }

    public function test_edit_update_archive_and_cross_tenant_boundaries(): void
    {
        [$user,$org] = $this->principal(['property.properties.view', 'property.properties.update', 'property.properties.archive']);
        $p = PropertyFactory::new()->create(['organization_id' => $org->id]);
        $other = PropertyFactory::new()->create();
        $this->actingAs($user);
        $this->get("/admin/properties/$p->id/edit")->assertOk()->assertSee($p->name);
        $this->put("/admin/properties/$p->id", ['name' => 'Changed'])->assertRedirect(route('admin.properties.edit', $p));
        $this->assertDatabaseHas('properties', ['id' => $p->id, 'name' => 'Changed']);
        $this->get("/admin/properties/$other->id/edit")->assertNotFound();
        $this->put("/admin/properties/$other->id", ['name' => 'No'])->assertNotFound();
        $this->delete("/admin/properties/$other->id")->assertNotFound();
        $this->delete("/admin/properties/$p->id")->assertRedirect(route('admin.properties.index'));
        $this->assertSoftDeleted('properties', ['id' => $p->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.archived', 'auditable_id' => $p->id]);
    }

    public function test_each_web_action_requires_its_permission(): void
    {
        [$user] = $this->principal([]);
        $p = PropertyFactory::new()->create();
        $this->actingAs($user);
        $this->get('/admin/properties')->assertForbidden();
        $this->get('/admin/properties/create')->assertForbidden();
        $this->post('/admin/properties', [])->assertForbidden();
    }

    private function principal(array $codes): array
    {
        $org = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        $m = OrganizationUser::create(['organization_id' => $org->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);
        if ($codes) {
            $r = RoleFactory::new()->create(['organization_id' => $org->id]);
            foreach ($codes as $code) {
                $p = PermissionFactory::new()->create(['code' => $code]);
                $r->permissions()->attach($p);
            }$m->roles()->attach($r);
        }

        return [$user, $org, $m];
    }
}
