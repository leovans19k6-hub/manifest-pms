<?php

namespace Tests\Feature\Foundation;

use Database\Factories\OrganizationFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Enums\OrganizationMemberStatus;
use Domain\Foundation\Events\OrganizationSwitched;
use Domain\Foundation\Exceptions\OrganizationContextException;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\OrganizationContextService;
use Domain\Foundation\Support\CurrentOrganization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrganizationContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_active_organization_is_resolved(): void
    {
        [$user, $organization] = $this->member(true);

        $resolved = app(OrganizationContextService::class)->resolveFor($user);

        $this->assertTrue($resolved->is($organization));
        $this->assertSame($organization->id, app(CurrentOrganization::class)->id());
        $this->assertSame($organization->id, session(OrganizationContextService::SESSION_KEY));
    }

    public function test_valid_active_session_organization_is_preferred(): void
    {
        [$user, $default] = $this->member(true);
        [, $selected] = $this->member(false, $user);
        session([OrganizationContextService::SESSION_KEY => $selected->id]);

        $resolved = app(OrganizationContextService::class)->resolveFor($user);

        $this->assertTrue($resolved->is($selected));
        $this->assertFalse($resolved->is($default));
    }

    public function test_invalid_session_organization_falls_back_to_default(): void
    {
        [$user, $default] = $this->member(true);
        $foreign = OrganizationFactory::new()->create();
        session([OrganizationContextService::SESSION_KEY => $foreign->id]);

        $resolved = app(OrganizationContextService::class)->resolveFor($user);

        $this->assertTrue($resolved->is($default));
        $this->assertSame($default->id, session(OrganizationContextService::SESSION_KEY));
    }

    public function test_suspended_membership_cannot_be_selected(): void
    {
        [$user, $organization] = $this->member(false, null, OrganizationMemberStatus::Suspended);

        $this->expectException(OrganizationContextException::class);

        app(OrganizationContextService::class)->switch($user, $organization->id);
    }

    public function test_cross_organization_switch_is_rejected(): void
    {
        [$user] = $this->member(true);
        $foreign = OrganizationFactory::new()->create();

        $this->expectException(OrganizationContextException::class);

        app(OrganizationContextService::class)->switch($user, $foreign->id);
    }

    public function test_switch_updates_context_session_and_dispatches_event(): void
    {
        Event::fake([OrganizationSwitched::class]);
        [$user, $first] = $this->member(true);
        [, $second] = $this->member(false, $user);
        $service = app(OrganizationContextService::class);
        $service->resolveFor($user);

        $switched = $service->switch($user, $second->id);

        $this->assertTrue($switched->is($second));
        $this->assertSame($second->id, app(CurrentOrganization::class)->id());
        $this->assertSame($second->id, session(OrganizationContextService::SESSION_KEY));
        Event::assertDispatched(
            OrganizationSwitched::class,
            fn (OrganizationSwitched $event): bool => $event->user->is($user)
                && $event->previousOrganization?->is($first)
                && $event->organization->is($second),
        );
    }

    public function test_context_can_be_cleared_to_prevent_leakage(): void
    {
        [$user] = $this->member(true);
        $service = app(OrganizationContextService::class);
        $service->resolveFor($user);

        $service->clear();

        $this->assertNull($service->current());
        $this->assertNull(session(OrganizationContextService::SESSION_KEY));
    }

    private function member(
        bool $default,
        $user = null,
        OrganizationMemberStatus $status = OrganizationMemberStatus::Active,
    ): array {
        $user ??= UserFactory::new()->create();
        $organization = OrganizationFactory::new()->create();

        OrganizationUser::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => $status->value,
            'is_default' => $default,
            'joined_at' => now(),
        ]);

        return [$user, $organization];
    }
}
