<?php

declare(strict_types=1);

namespace Tests\Feature\Housekeeping;

use Database\Factories\HousekeepingTaskFactory;
use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Housekeeping\Enums\HousekeepingTaskStatus;
use Domain\Housekeeping\Services\HousekeepingQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class HousekeepingQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_query_filters_sorts_and_paginates_within_current_tenant(): void
    {
        [$organization, $membership] = $this->viewer();

        $other = OrganizationFactory::new()->create();

        app(CurrentOrganization::class)->set($organization);

        HousekeepingTaskFactory::new()->create([
            'organization_id' => $organization->id,
            'priority' => 3,
            'status' => HousekeepingTaskStatus::Pending,
        ]);

        HousekeepingTaskFactory::new()->create([
            'organization_id' => $organization->id,
            'priority' => 1,
            'status' => HousekeepingTaskStatus::Completed,
        ]);

        HousekeepingTaskFactory::new()->create([
            'organization_id' => $other->id,
            'priority' => 5,
            'status' => HousekeepingTaskStatus::Pending,
        ]);

        $page = app(HousekeepingQueryService::class)->paginate(
            $membership,
            [
                'status' => HousekeepingTaskStatus::Pending,
                'sort' => 'priority',
                'direction' => 'desc',
                'per_page' => 15,
            ],
        );

        $this->assertSame(1, $page->total());

        $task = $page->items()[0];

        $this->assertSame($organization->id, $task->organization_id);
        $this->assertSame(3, $task->priority);
        $this->assertSame(
            HousekeepingTaskStatus::Pending,
            $task->status,
        );
    }

    public function test_query_rejects_invalid_sort(): void
    {
        [$organization, $membership] = $this->viewer();

        app(CurrentOrganization::class)->set($organization);

        $this->expectException(ValidationException::class);

        app(HousekeepingQueryService::class)->paginate(
            $membership,
            [
                'sort' => 'organization_id',
            ],
        );
    }

    public function test_query_rejects_invalid_page_size(): void
    {
        [$organization, $membership] = $this->viewer();

        app(CurrentOrganization::class)->set($organization);

        $this->expectException(ValidationException::class);

        app(HousekeepingQueryService::class)->paginate(
            $membership,
            [
                'per_page' => 1000,
            ],
        );
    }

    public function test_query_requires_view_permission(): void
    {
        $organization = OrganizationFactory::new()->create();

        $user = UserFactory::new()->create();

        $membership = OrganizationUser::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => 'active',
            'is_default' => true,
        ]);

        app(CurrentOrganization::class)->set($organization);

        $this->expectException(HttpException::class);

        app(HousekeepingQueryService::class)->paginate($membership);
    }

    private function viewer(): array
    {
        $organization = OrganizationFactory::new()->create();

        $user = UserFactory::new()->create();

        $membership = OrganizationUser::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => 'active',
            'is_default' => true,
        ]);

        $role = RoleFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $permission = \Domain\Foundation\Models\Permission::query()->firstOrCreate(
			[
				'code' => 'housekeeping.tasks.view',
			],
			[
				'name' => 'View Housekeeping Tasks',
				'group' => 'housekeeping',
				'description' => 'View housekeeping tasks.',
			],
		);

		$role->permissions()->syncWithoutDetaching([
			$permission->id,
		]);

        $membership->roles()->attach($role);

        return [$organization, $membership];
    }
}