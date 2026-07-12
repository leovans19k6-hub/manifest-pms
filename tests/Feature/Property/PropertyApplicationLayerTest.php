<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuditLogger;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\Actions\ArchivePropertyAction;
use Domain\Property\Application\Actions\CreatePropertyAction;
use Domain\Property\Application\Actions\UpdatePropertyAction;
use Domain\Property\Application\Commands\ArchivePropertyCommand;
use Domain\Property\Application\Commands\CreatePropertyCommand;
use Domain\Property\Application\Commands\UpdatePropertyCommand;
use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PropertyApplicationLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_action_validates_input_ignores_system_fields_and_audits(): void
    {
        [$organization, $membership] = $this->membershipWith(['property.properties.create']);
        app(CurrentOrganization::class)->set($organization);

        $property = app(CreatePropertyAction::class)->execute(new CreatePropertyCommand($membership, [
            ...$this->validInput('P-101'),
            'organization_id' => OrganizationFactory::new()->create()->id,
            'id' => '01AAAAAAAAAAAAAAAAAAAAAAAA',
            'deleted_at' => now(),
        ]));

        $this->assertSame($organization->id, $property->organization_id);
        $this->assertNotSame('01AAAAAAAAAAAAAAAAAAAAAAAA', $property->id);
        $this->assertNull($property->deleted_at);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.created', 'auditable_id' => $property->id]);
    }

    public function test_validation_rejects_invalid_enum_currency_and_duplicate_tenant_code(): void
    {
        [$organization, $membership] = $this->membershipWith(['property.properties.create']);
        app(CurrentOrganization::class)->set($organization);
        PropertyFactory::new()->create(['organization_id' => $organization->id, 'code' => 'DUP']);

        $this->expectException(ValidationException::class);
        app(CreatePropertyAction::class)->execute(new CreatePropertyCommand($membership, [
            ...$this->validInput('DUP'), 'type' => 'invalid', 'currency' => 'vn',
        ]));
    }

    public function test_update_and_archive_actions_use_existing_permission_and_tenant_boundaries(): void
    {
        [$organization, $membership] = $this->membershipWith(['property.properties.update', 'property.properties.archive']);
        app(CurrentOrganization::class)->set($organization);
        $property = PropertyFactory::new()->create(['organization_id' => $organization->id, 'code' => 'OLD', 'slug' => 'old']);

        $updated = app(UpdatePropertyAction::class)->execute(new UpdatePropertyCommand($membership, $property, $this->validInput('NEW')));
        $this->assertSame('NEW', $updated->code);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.updated', 'auditable_id' => $property->id]);

        app(ArchivePropertyAction::class)->execute(new ArchivePropertyCommand($membership, $updated));
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.archived', 'auditable_id' => $property->id]);
    }

    public function test_audit_failure_rolls_back_property_mutation(): void
    {
        [$organization, $membership] = $this->membershipWith(['property.properties.create']);
        app(CurrentOrganization::class)->set($organization);
        $this->mock(AuditLogger::class, function ($mock): void {
            $mock->shouldReceive('record')->once()->andThrow(new RuntimeException('audit failed'));
        });

        try {
            app(CreatePropertyAction::class)->execute(new CreatePropertyCommand($membership, $this->validInput('ROLLBACK')));
            $this->fail('Expected audit failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('audit failed', $exception->getMessage());
        }

        $this->assertDatabaseMissing('properties', ['code' => 'ROLLBACK']);
    }

    public function test_application_action_denies_missing_permission(): void
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        $membership = OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);
        app(CurrentOrganization::class)->set($organization);

        $this->expectException(HttpException::class);
        app(CreatePropertyAction::class)->execute(new CreatePropertyCommand($membership, $this->validInput('DENIED')));
    }

    private function validInput(string $code): array
    {
        return ['code' => $code, 'name' => "Property {$code}", 'slug' => strtolower($code), 'type' => PropertyType::Villa->value, 'status' => PropertyStatus::Active->value, 'timezone' => 'Asia/Ho_Chi_Minh', 'currency' => 'VND', 'metadata' => ['source' => 'test']];
    }

    private function membershipWith(array $permissions): array
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        $membership = OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);
        $role = RoleFactory::new()->create(['organization_id' => $organization->id]);
        foreach ($permissions as $code) {
            $role->permissions()->attach(PermissionFactory::new()->create(['code' => $code]));
        }
        $membership->roles()->attach($role);

        return [$organization, $membership];
    }
}
