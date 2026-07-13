<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\PropertyAssetFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use DateTimeInterface;
use Domain\Foundation\Models\AuditLog;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Models\Permission;
use Domain\Foundation\Models\User;
use Domain\Foundation\Services\AuditLogger;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\Actions\CreatePropertyAssetDownloadAction;
use Domain\Property\Application\Actions\DeletePropertyAssetAction;
use Domain\Property\Application\Actions\UploadPropertyAssetAction;
use Domain\Property\Application\Commands\CreatePropertyAssetDownloadCommand;
use Domain\Property\Application\Commands\DeletePropertyAssetCommand;
use Domain\Property\Application\Commands\UploadPropertyAssetCommand;
use Domain\Property\Application\DTO\UploadFileData;
use Domain\Property\Contracts\PropertyStorage;
use Domain\Property\Enums\PropertyAssetKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PropertyMediaOperationalHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_cleanup_failure_does_not_hide_original_database_failure(): void
    {
        [$membership, $property] = $this->principal([
            'property.media.create',
        ]);

        $storage = new OperationalHardeningPropertyStorage;
        $storage->failDelete = true;

        $this->app->instance(PropertyStorage::class, $storage);
        $this->app->instance(
            AuditLogger::class,
            $this->failingAuditLogger(),
        );

        try {
            app(UploadPropertyAssetAction::class)->execute(
                new UploadPropertyAssetCommand(
                    $membership,
                    $property,
                    PropertyAssetKind::Image,
                    new UploadFileData(
                        'hero.jpg',
                        'image/jpeg',
                        'payload',
                    ),
                ),
            );

            $this->fail('Expected upload failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'original audit failure',
                $exception->getMessage(),
            );
        }

        $this->assertDatabaseCount('property_assets', 0);
        $this->assertCount(1, $storage->files);
    }

    public function test_delete_restore_failure_reports_consistency_failure_and_preserves_database_record(): void
    {
        [$membership, $property] = $this->principal([
            'property.media.delete',
        ]);

        $asset = PropertyAssetFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'storage_key' => 'assets/hero.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $storage = new OperationalHardeningPropertyStorage;
        $storage->files[$asset->storage_key] = 'payload';
        $storage->failPut = true;

        $this->app->instance(PropertyStorage::class, $storage);
        $this->app->instance(
            AuditLogger::class,
            $this->failingAuditLogger(),
        );

        try {
            app(DeletePropertyAssetAction::class)->execute(
                new DeletePropertyAssetCommand(
                    $membership,
                    $asset,
                ),
            );

            $this->fail('Expected consistency failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Property media consistency recovery failed.',
                $exception->getMessage(),
            );

            $this->assertInstanceOf(
                RuntimeException::class,
                $exception->getPrevious(),
            );

            $this->assertSame(
                'original audit failure',
                $exception->getPrevious()->getMessage(),
            );
        }

        $this->assertDatabaseHas('property_assets', [
            'id' => $asset->id,
        ]);

        $this->assertArrayNotHasKey(
            $asset->storage_key,
            $storage->files,
        );
    }

    public function test_private_download_rejects_non_positive_ttl(): void
    {
        [$membership, $property] = $this->principal([
            'property.media.view',
        ]);

        $asset = PropertyAssetFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'storage_key' => 'assets/hero.jpg',
            'disk' => 'local',
        ]);

        $storage = new OperationalHardeningPropertyStorage;
        $storage->files[$asset->storage_key] = 'payload';

        $this->app->instance(PropertyStorage::class, $storage);

        config()->set('property_media.download_ttl_seconds', 0);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Property media download TTL must be positive.',
        );

        app(CreatePropertyAssetDownloadAction::class)->execute(
            new CreatePropertyAssetDownloadCommand(
                $membership,
                $asset,
            ),
        );
    }

    public function test_upload_normalizes_filename_for_storage_key_and_persisted_original_name(): void
    {
        [$membership, $property] = $this->principal([
            'property.media.create',
        ]);

        $storage = new OperationalHardeningPropertyStorage;

        $this->app->instance(PropertyStorage::class, $storage);

        $asset = app(UploadPropertyAssetAction::class)->execute(
            new UploadPropertyAssetCommand(
                $membership,
                $property,
                PropertyAssetKind::Image,
                new UploadFileData(
                    '..\\unsafe/path/ hero?.jpg ',
                    'image/jpeg',
                    'payload',
                ),
            ),
        );

        $this->assertSame(
            'hero_.jpg',
            $asset->original_name,
        );

        $this->assertStringEndsWith(
            '-hero_.jpg',
            $asset->storage_key,
        );

        $this->assertStringNotContainsString(
            '..',
            $asset->storage_key,
        );

        $this->assertStringNotContainsString(
            '\\',
            $asset->storage_key,
        );
    }

    private function failingAuditLogger(): AuditLogger
    {
        return new class extends AuditLogger
        {
            public function __construct() {}

            public function record(
                string $event,
                ?Model $auditable = null,
                array $old = [],
                array $new = [],
                array $metadata = [],
                ?User $actor = null,
            ): AuditLog {
                throw new RuntimeException('original audit failure');
            }
        };
    }

    private function principal(array $permissions): array
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

        foreach ($permissions as $code) {
            $permission = Permission::query()
                ->where('code', $code)
                ->first();

            if (! $permission) {
                $permission = PermissionFactory::new()->create([
                    'code' => $code,
                ]);
            }

            $role->permissions()->syncWithoutDetaching([
                $permission->id,
            ]);
        }

        $membership->roles()->syncWithoutDetaching([
            $role->id,
        ]);

        app(CurrentOrganization::class)->set($organization);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        return [$membership, $property];
    }
}

final class OperationalHardeningPropertyStorage implements PropertyStorage
{
    public array $files = [];

    public bool $failPut = false;

    public bool $failDelete = false;

    public function put(
        string $key,
        string $contents,
        string $mimeType,
    ): void {
        if ($this->failPut) {
            throw new RuntimeException('put failed');
        }

        $this->files[$key] = $contents;
    }

    public function get(string $key): string
    {
        return $this->files[$key];
    }

    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->files);
    }

    public function delete(string $key): void
    {
        if ($this->failDelete) {
            throw new RuntimeException('delete failed');
        }

        if (! $this->exists($key)) {
            throw new RuntimeException('delete failed');
        }

        unset($this->files[$key]);
    }

    public function temporaryUrl(
        string $key,
        DateTimeInterface $expiresAt,
    ): string {
        return 'signed://'.$key.'?expires='.$expiresAt->getTimestamp();
    }

    public function disk(): string
    {
        return 'local';
    }
}
