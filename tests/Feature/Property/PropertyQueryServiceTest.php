<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Domain\Property\Services\PropertyQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PropertyQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_query_filters_searches_sorts_and_paginates_within_current_tenant(): void
    {
        [$organization, $membership] = $this->viewer();
        $other = OrganizationFactory::new()->create();
        app(CurrentOrganization::class)->set($organization);
        PropertyFactory::new()->create(['organization_id' => $organization->id, 'code' => 'HP-002', 'name' => 'Beach Villa', 'type' => PropertyType::Villa, 'status' => PropertyStatus::Active]);
        PropertyFactory::new()->create(['organization_id' => $organization->id, 'code' => 'HP-001', 'name' => 'City Hotel', 'type' => PropertyType::Hotel, 'status' => PropertyStatus::Inactive]);
        PropertyFactory::new()->create(['organization_id' => $other->id, 'code' => 'HP-003', 'name' => 'Other Beach Villa', 'type' => PropertyType::Villa, 'status' => PropertyStatus::Active]);

        $page = app(PropertyQueryService::class)->paginate($membership, ['type' => 'villa', 'status' => 'active', 'search' => 'Beach', 'sort' => 'code', 'direction' => 'desc', 'per_page' => 1]);

        $this->assertSame(1, $page->total());
        $this->assertSame('HP-002', $page->items()[0]->code);
        $this->assertSame(1, $page->perPage());
    }

    public function test_query_rejects_non_allowlisted_sort_and_excessive_page_size(): void
    {
        [$organization, $membership] = $this->viewer();
        app(CurrentOrganization::class)->set($organization);

        $this->expectException(ValidationException::class);
        app(PropertyQueryService::class)->paginate($membership, ['sort' => 'organization_id', 'per_page' => 1000]);
    }

    public function test_query_requires_view_permission(): void
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        $membership = OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);
        app(CurrentOrganization::class)->set($organization);

        $this->expectException(HttpException::class);
        app(PropertyQueryService::class)->paginate($membership);
    }

    private function viewer(): array
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        $membership = OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);
        $role = RoleFactory::new()->create(['organization_id' => $organization->id]);
        $role->permissions()->attach(PermissionFactory::new()->create(['code' => 'property.properties.view']));
        $membership->roles()->attach($role);

        return [$organization, $membership];
    }
}
