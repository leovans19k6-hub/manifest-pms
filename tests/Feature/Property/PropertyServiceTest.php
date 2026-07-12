<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Services\PropertyService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PropertyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_update_archive_are_tenant_scoped_authorized_and_audited(): void
    {
        [$organization, $membership] = $this->authorizedMembership([
            'property.properties.create', 'property.properties.update', 'property.properties.archive',
        ]);
        app(CurrentOrganization::class)->set($organization);
        $service = app(PropertyService::class);

        $property = $service->create($membership, ['code' => 'P-001', 'name' => 'Property One', 'slug' => 'property-one']);
        $this->assertSame($organization->id, $property->organization_id);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.created', 'auditable_id' => $property->id]);

        $property = $service->update($membership, $property, ['name' => 'Updated']);
        $this->assertSame('Updated', $property->name);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.updated', 'auditable_id' => $property->id]);

        $service->archive($membership, $property);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.archived', 'auditable_id' => $property->id]);
    }

    public function test_list_and_find_never_return_cross_tenant_properties(): void
    {
        $current = OrganizationFactory::new()->create();
        $other = OrganizationFactory::new()->create();
        $visible = PropertyFactory::new()->create(['organization_id' => $current->id]);
        $hidden = PropertyFactory::new()->create(['organization_id' => $other->id]);
        app(CurrentOrganization::class)->set($current);
        $service = app(PropertyService::class);

        $this->assertTrue($service->list()->contains($visible));
        $this->assertFalse($service->list()->contains($hidden));
        $this->assertTrue($service->find($visible->id)->is($visible));
        $this->expectException(ModelNotFoundException::class);
        $service->find($hidden->id);
    }

    public function test_cross_tenant_update_is_rejected(): void
    {
        [$organization, $membership] = $this->authorizedMembership(['property.properties.update']);
        $other = PropertyFactory::new()->create();
        app(CurrentOrganization::class)->set($organization);

        $this->expectException(ValidationException::class);
        app(PropertyService::class)->update($membership, $other, ['name' => 'Forbidden']);
    }

    public function test_missing_permission_is_denied(): void
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        $membership = OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);
        app(CurrentOrganization::class)->set($organization);

        $this->expectException(HttpException::class);
        app(PropertyService::class)->create($membership, ['code' => 'DENY', 'name' => 'Denied', 'slug' => 'denied']);
    }

    private function authorizedMembership(array $codes): array
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        $membership = OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);
        $role = RoleFactory::new()->create(['organization_id' => $organization->id]);
        foreach ($codes as $code) {
            $permission = PermissionFactory::new()->create(['code' => $code]);
            $role->permissions()->attach($permission);
        }
        $membership->roles()->attach($role);

        return [$organization, $membership];
    }
}
