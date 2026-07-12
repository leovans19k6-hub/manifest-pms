<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\PropertyAssetFactory;
use Database\Factories\PropertyDocumentFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use DateTimeInterface;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Models\Permission;
use Domain\Foundation\Services\AuditLogger;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\Actions\ChangePropertyDocumentLifecycleAction;
use Domain\Property\Application\Actions\CreatePropertyAssetDownloadAction;
use Domain\Property\Application\Actions\DeletePropertyAssetAction;
use Domain\Property\Application\Actions\ReorderPropertyAssetsAction;
use Domain\Property\Application\Actions\UpdatePropertyAssetMetadataAction;
use Domain\Property\Application\Commands\ChangePropertyDocumentLifecycleCommand;
use Domain\Property\Application\Commands\CreatePropertyAssetDownloadCommand;
use Domain\Property\Application\Commands\DeletePropertyAssetCommand;
use Domain\Property\Application\Commands\ReorderPropertyAssetsCommand;
use Domain\Property\Application\Commands\UpdatePropertyAssetMetadataCommand;
use Domain\Property\Application\DTO\AssetOrderData;
use Domain\Property\Application\DTO\MediaMetadataData;
use Domain\Property\Contracts\PropertyStorage;
use Domain\Property\Enums\PropertyDocumentLifecycle;
use Domain\Property\Services\PropertyMediaQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PropertyMediaAdministrationApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_metadata_update_is_authorized_tenant_safe_and_audited(): void
    {
        [$m,$p] = $this->principal(['property.media.update']);
        $asset = PropertyAssetFactory::new()->create(['organization_id' => $p->organization_id, 'property_id' => $p->id]);
        $updated = app(UpdatePropertyAssetMetadataAction::class)->execute(new UpdatePropertyAssetMetadataCommand($m, $asset, new MediaMetadataData(['caption' => 'Hero'])));
        $this->assertSame('Hero', $updated->metadata['caption']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.media.metadata.updated', 'auditable_id' => $asset->id]);
    }

    public function test_query_filters_sorts_paginates_and_never_leaks_tenants(): void
    {
        [$m,$p] = $this->principal(['property.media.view']);
        PropertyAssetFactory::new()->create(['organization_id' => $p->organization_id, 'property_id' => $p->id, 'position' => 2]);
        PropertyAssetFactory::new()->create(['organization_id' => $p->organization_id, 'property_id' => $p->id, 'position' => 1]);
        PropertyAssetFactory::new()->create();
        $page = app(PropertyMediaQueryService::class)->assets($m, $p, ['sort' => 'position', 'direction' => 'asc', 'per_page' => 1]);
        $this->assertSame(2, $page->total());
        $this->assertSame(1, $page->count());
        $this->assertSame(1, $page->items()[0]->position);
        $this->expectException(ValidationException::class);
        app(PropertyMediaQueryService::class)->assets($m, $p, ['sort' => 'organization_id']);
    }

    public function test_reorder_rejects_foreign_assets_and_orders_atomically(): void
    {
        [$m,$p] = $this->principal(['property.media.update']);
        $a = PropertyAssetFactory::new()->create(['organization_id' => $p->organization_id, 'property_id' => $p->id]);
        $b = PropertyAssetFactory::new()->create(['organization_id' => $p->organization_id, 'property_id' => $p->id]);
        app(ReorderPropertyAssetsAction::class)->execute(new ReorderPropertyAssetsCommand($m, $p, new AssetOrderData([$b->id, $a->id])));
        $this->assertSame(0, $b->fresh()->position);
        $this->assertSame(1, $a->fresh()->position);
        $foreign = PropertyAssetFactory::new()->create();
        $this->expectException(ValidationException::class);
        app(ReorderPropertyAssetsAction::class)->execute(new ReorderPropertyAssetsCommand($m, $p, new AssetOrderData([$a->id, $foreign->id])));
    }

    public function test_private_download_requires_permission_tenant_and_private_object(): void
    {
        [$m,$p] = $this->principal(['property.media.view']);
        $storage = new InMemoryPropertyStorage;
        $this->app->instance(PropertyStorage::class, $storage);
        $asset = PropertyAssetFactory::new()->create(['organization_id' => $p->organization_id, 'property_id' => $p->id, 'storage_key' => 'private/a']);
        $storage->files['private/a'] = 'x';
        $download = app(CreatePropertyAssetDownloadAction::class)->execute(new CreatePropertyAssetDownloadCommand($m, $asset));
        $this->assertStringStartsWith('signed://private/a?', $download->url);
        [$otherMembership] = $this->principal(['property.media.view']);
        app(CurrentOrganization::class)->set($p->organization);
        $this->expectException(HttpException::class);
        app(CreatePropertyAssetDownloadAction::class)->execute(new CreatePropertyAssetDownloadCommand($otherMembership, $asset));
    }

    public function test_storage_failure_leaves_database_untouched(): void
    {
        [$m,$p] = $this->principal(['property.media.delete']);
        $storage = new InMemoryPropertyStorage;
        $storage->failDelete = true;
        $this->app->instance(PropertyStorage::class, $storage);
        $asset = PropertyAssetFactory::new()->create(['organization_id' => $p->organization_id, 'property_id' => $p->id, 'storage_key' => 'private/a']);
        $storage->files['private/a'] = 'x';
        try {
            app(DeletePropertyAssetAction::class)->execute(new DeletePropertyAssetCommand($m, $asset));
            $this->fail();
        } catch (RuntimeException) {
        }
        $this->assertDatabaseHas('property_assets', ['id' => $asset->id]);
    }

    public function test_audit_failure_rolls_back_metadata_and_delete_restores_storage(): void
    {
        [$m,$p] = $this->principal(['property.media.update', 'property.media.delete']);
        $storage = new InMemoryPropertyStorage;
        $this->app->instance(PropertyStorage::class, $storage);
        $asset = PropertyAssetFactory::new()->create(['organization_id' => $p->organization_id, 'property_id' => $p->id, 'storage_key' => 'private/a', 'metadata' => ['old' => true]]);
        $storage->files['private/a'] = 'payload';
        $this->mock(AuditLogger::class, fn ($mock) => $mock->shouldReceive('record')->andThrow(new RuntimeException('audit failed')));
        try {
            app(UpdatePropertyAssetMetadataAction::class)->execute(new UpdatePropertyAssetMetadataCommand($m, $asset, new MediaMetadataData(['new' => true])));
        } catch (RuntimeException) {
        }
        $this->assertSame(['old' => true], $asset->fresh()->metadata);
        try {
            app(DeletePropertyAssetAction::class)->execute(new DeletePropertyAssetCommand($m, $asset->fresh()));
        } catch (RuntimeException) {
        }
        $this->assertDatabaseHas('property_assets', ['id' => $asset->id]);
        $this->assertSame('payload', $storage->files['private/a']);
    }

    public function test_document_lifecycle_is_explicit_and_audited(): void
    {
        [$m,$p] = $this->principal(['property.documents.update']);
        $document = PropertyDocumentFactory::new()->create(['organization_id' => $p->organization_id, 'property_id' => $p->id]);
        $document = app(ChangePropertyDocumentLifecycleAction::class)->execute(new ChangePropertyDocumentLifecycleCommand($m, $document, PropertyDocumentLifecycle::Archived));
        $this->assertSame(PropertyDocumentLifecycle::Archived, $document->lifecycle_status);
        $this->assertNotNull($document->archived_at);
        $this->assertDatabaseHas('audit_logs', ['event' => 'property.document.lifecycle.changed', 'auditable_id' => $document->id]);
    }

    private function principal(array $permissions): array
    {
        $org = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();

        $membership = OrganizationUser::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'status' => 'active',
            'is_default' => true,
        ]);

        $role = RoleFactory::new()->create([
            'organization_id' => $org->id,
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

        app(CurrentOrganization::class)->set($org);

        $property = PropertyFactory::new()->create([
            'organization_id' => $org->id,
        ]);

        return [$membership, $property];
    }
}

final class InMemoryPropertyStorage implements PropertyStorage
{
    public array $files = [];

    public bool $failDelete = false;

    public function put(string $key, string $contents, string $mimeType): void
    {
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
        } unset($this->files[$key]);
    }

    public function temporaryUrl(string $key, DateTimeInterface $expiresAt): string
    {
        return 'signed://'.$key.'?expires='.$expiresAt->getTimestamp();
    }

    public function disk(): string
    {
        return 'local';
    }
}
