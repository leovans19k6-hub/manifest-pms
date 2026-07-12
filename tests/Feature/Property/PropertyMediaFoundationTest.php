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
use Domain\Property\Application\Actions\UploadPropertyAssetAction;
use Domain\Property\Application\Commands\UploadPropertyAssetCommand;
use Domain\Property\Application\DTO\UploadFileData;
use Domain\Property\Enums\PropertyAssetKind;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PropertyMediaFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_relationships_upload_audit_and_tenant_metadata(): void
    {
        Storage::fake('local');
        [$m,$p] = $this->principal('property.media.create');
        $a = app(UploadPropertyAssetAction::class)->execute(new UploadPropertyAssetCommand($m, $p, PropertyAssetKind::Image, new UploadFileData('x.jpg', 'image/jpeg', 'abc')));
        $this->assertSame($p->organization_id, $a->organization_id);
        $this->assertSame($p->id, $a->property->id);
        Storage::disk('local')->assertExists($a->storage_key);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.media.created', 'auditable_id' => $a->id]);
    }

    public function test_validation_rejects_mime_and_size(): void
    {
        [$m,$p] = $this->principal('property.media.create');
        $this->expectException(ValidationException::class);
        app(UploadPropertyAssetAction::class)->execute(new UploadPropertyAssetCommand($m, $p, PropertyAssetKind::Image, new UploadFileData('x.exe', 'application/x-msdownload', 'abc')));
    }

    public function test_permission_and_cross_tenant_are_denied(): void
    {
        [$m,$p] = $this->principal(null);
        Storage::fake('local');
        $this->expectException(HttpException::class);
        app(UploadPropertyAssetAction::class)->execute(new UploadPropertyAssetCommand($m, $p, PropertyAssetKind::Image, new UploadFileData('x.jpg', 'image/jpeg', 'abc')));
    }

    public function test_cross_tenant_property_is_rejected(): void
    {
        [$m] = $this->principal('property.media.create');
        $other = PropertyFactory::new()->create();
        Storage::fake('local');
        $this->expectException(ValidationException::class);
        app(UploadPropertyAssetAction::class)->execute(new UploadPropertyAssetCommand($m, $other, PropertyAssetKind::Image, new UploadFileData('x.jpg', 'image/jpeg', 'abc')));
    }

    public function test_audit_failure_rolls_back_database_and_compensates_storage(): void
    {
        Storage::fake('local');
        [$m,$p] = $this->principal('property.media.create');
        $this->mock(AuditLogger::class, function ($mock) {
            $mock->shouldReceive('record')->andThrow(new \RuntimeException('audit failed'));
        });
        try {
            app(UploadPropertyAssetAction::class)->execute(new UploadPropertyAssetCommand($m, $p, PropertyAssetKind::Image, new UploadFileData('x.jpg', 'image/jpeg', 'abc')));
            $this->fail();
        } catch (\RuntimeException) {
        }$this->assertDatabaseCount('property_assets', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    private function principal(?string $permission): array
    {
        $org = OrganizationFactory::new()->create();
        $u = UserFactory::new()->create();
        $m = OrganizationUser::create(['organization_id' => $org->id, 'user_id' => $u->id, 'status' => 'active', 'is_default' => true]);
        if ($permission) {
            $r = RoleFactory::new()->create(['organization_id' => $org->id]);
            $perm = PermissionFactory::new()->create(['code' => $permission]);
            $r->permissions()->attach($perm);
            $m->roles()->attach($r);
        }app(CurrentOrganization::class)->set($org);
        $p = PropertyFactory::new()->create(['organization_id' => $org->id]);

        return [$m, $p];
    }
}
