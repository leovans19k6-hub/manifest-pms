<?php

namespace Tests\Feature\Foundation;

use Database\Factories\OrganizationFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FoundationDatabaseCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_and_organizations_use_ulid_primary_keys(): void
    {
        $user = UserFactory::new()->create();
        $organization = OrganizationFactory::new()->create();

        $this->assertTrue(Str::isUlid($user->id));
        $this->assertTrue(Str::isUlid($organization->id));
    }

    public function test_membership_relations_are_persisted_and_resolved(): void
    {
        $user = UserFactory::new()->create();
        $organization = OrganizationFactory::new()->create();

        OrganizationUser::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);

        $this->assertTrue($user->fresh()->organizations->contains($organization));
        $this->assertTrue($organization->fresh()->users->contains($user));
        $this->assertTrue($user->fresh()->memberships->first()->is_default);
    }

    public function test_duplicate_membership_is_rejected_by_database_constraint(): void
    {
        $user = UserFactory::new()->create();
        $organization = OrganizationFactory::new()->create();
        $data = ['organization_id' => $organization->id, 'user_id' => $user->id];

        OrganizationUser::create($data);

        $this->expectException(QueryException::class);
        OrganizationUser::create($data);
    }
}
