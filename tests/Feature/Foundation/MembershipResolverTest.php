<?php

namespace Tests\Feature\Foundation;

use Database\Factories\OrganizationFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Enums\OrganizationMemberStatus;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\MembershipResolver;
use Domain\Foundation\Support\CurrentMembership;
use Domain\Foundation\Support\CurrentOrganization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_membership_resolves_for_current_organization(): void
    {
        [$user, $organization, $membership] = $this->member(OrganizationMemberStatus::Active);
        app(CurrentOrganization::class)->set($organization);

        $resolved = app(MembershipResolver::class)->resolve($user);

        $this->assertTrue($resolved->is($membership));
        $this->assertTrue(app(CurrentMembership::class)->get()->is($membership));
    }

    public function test_suspended_membership_is_rejected(): void
    {
        [$user, $organization] = $this->member(OrganizationMemberStatus::Suspended);
        app(CurrentOrganization::class)->set($organization);

        $this->assertNull(app(MembershipResolver::class)->resolve($user));
        $this->assertNull(app(CurrentMembership::class)->get());
    }

    public function test_cross_tenant_membership_is_rejected(): void
    {
        [$user] = $this->member(OrganizationMemberStatus::Active);
        $foreignOrganization = OrganizationFactory::new()->create();
        app(CurrentOrganization::class)->set($foreignOrganization);

        $this->assertNull(app(MembershipResolver::class)->resolve($user));
        $this->assertNull(app(CurrentMembership::class)->get());
    }

    public function test_missing_organization_context_is_rejected(): void
    {
        [$user] = $this->member(OrganizationMemberStatus::Active);

        $this->assertNull(app(MembershipResolver::class)->resolve($user));
        $this->assertNull(app(CurrentMembership::class)->get());
    }

    public function test_current_membership_clear_prevents_state_leakage(): void
    {
        [$user, $organization, $membership] = $this->member(OrganizationMemberStatus::Active);
        app(CurrentOrganization::class)->set($organization);
        app(MembershipResolver::class)->resolve($user);

        $this->assertTrue(app(CurrentMembership::class)->get()->is($membership));

        app(MembershipResolver::class)->clear();

        $this->assertNull(app(CurrentMembership::class)->get());
    }

    private function member(OrganizationMemberStatus $status): array
    {
        $user = UserFactory::new()->create();
        $organization = OrganizationFactory::new()->create();
        $membership = OrganizationUser::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => $status->value,
            'is_default' => true,
            'joined_at' => now(),
        ]);

        return [$user, $organization, $membership];
    }
}
