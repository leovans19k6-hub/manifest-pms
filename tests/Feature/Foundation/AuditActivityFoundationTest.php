<?php

namespace Tests\Feature\Foundation;

use Database\Factories\OrganizationFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Services\ActivityLogger;
use Domain\Foundation\Services\AuditLogger;
use Domain\Foundation\Services\AuditQueryService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Foundation\Support\RequestContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AuditActivityFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logger_captures_context_actor_values_and_request_id(): void
    {
        $organization = OrganizationFactory::new()->create();
        $actor = UserFactory::new()->create();
        app(CurrentOrganization::class)->set($organization);
        app(RequestContext::class)->set('550e8400-e29b-41d4-a716-446655440000');
        $log = app(AuditLogger::class)->record('role.updated', $organization, ['name' => 'Old'], ['name' => 'New'], ['source' => 'test'], $actor);
        $this->assertSame($organization->id, $log->organization_id);
        $this->assertSame($actor->id, $log->actor_id);
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $log->request_id);
        $this->assertSame(['name' => 'Old'], $log->old_values);
    }

    public function test_audit_logs_are_immutable_through_model(): void
    {
        app(CurrentOrganization::class)->set(OrganizationFactory::new()->create());
        $log = app(AuditLogger::class)->record('permission.created');
        $log->event = 'tampered';
        $this->expectException(LogicException::class);
        $log->save();
    }

    public function test_audit_query_is_tenant_safe(): void
    {
        $first = OrganizationFactory::new()->create();
        $second = OrganizationFactory::new()->create();
        app(CurrentOrganization::class)->set($first);
        app(AuditLogger::class)->record('first.event');
        app(CurrentOrganization::class)->set($second);
        app(AuditLogger::class)->record('second.event');
        $this->assertSame(['second.event'], app(AuditQueryService::class)->query()->pluck('event')->all());
    }

    public function test_audit_query_requires_organization_context(): void
    {
        $this->expectException(LogicException::class);
        app(AuditQueryService::class)->query();
    }

    public function test_activity_logger_captures_operational_activity(): void
    {
        $organization = OrganizationFactory::new()->create();
        app(CurrentOrganization::class)->set($organization);
        $log = app(ActivityLogger::class)->record('auth.login', 'User signed in', ['channel' => 'web']);
        $this->assertSame($organization->id, $log->organization_id);
        $this->assertSame('auth.login', $log->event);
        $this->assertSame(['channel' => 'web'], $log->metadata);
    }

    public function test_request_context_generates_stable_id_until_cleared(): void
    {
        $context = app(RequestContext::class);
        $first = $context->id();
        $this->assertSame($first, $context->id());
        $context->clear();
        $this->assertNotSame($first, $context->id());
    }

    public function test_retention_configuration_has_safe_defaults(): void
    {
        $this->assertSame(90, config('audit.activity_retention_days'));
        $this->assertNull(config('audit.audit_retention_days'));
    }
}
