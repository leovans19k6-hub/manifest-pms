<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\PropertyAssetFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use DateTimeInterface;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Contracts\PropertyStorage;
use Domain\Property\Models\PropertyAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class PropertyAssetHttpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_http_crud_reorder_download_and_audit_contracts(): void
    {
        Storage::fake('local');

        [$user, $org, $property] = $this->principal([
            'property.media.view',
            'property.media.create',
            'property.media.update',
            'property.media.delete',
        ]);

        $this->actingAs($user);

        $path = tempnam(sys_get_temp_dir(), 'asset-http-');

        if ($path === false) {
            $this->fail('Unable to create temporary upload file.');
        }

        $jpegBytes = base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAF//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABBQJ//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAwEBPwF//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAgEBPwF//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQAGPwJ//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPyF//9oADAMBAAIAAwAAABB//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAwEBPxB//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAgEBPxB//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxB//9k=',
            true,
        );

        if ($jpegBytes === false) {
            $this->fail('Unable to decode JPEG fixture.');
        }

        file_put_contents($path, $jpegBytes);

        $file = new UploadedFile(
            $path,
            'hero.jpg',
            'image/jpeg',
            null,
            true,
        );

        $assetId = $this->postJson(
            "/api/v1/properties/{$property->id}/assets",
            [
                'kind' => 'image',
                'file' => $file,
                'metadata' => ['caption' => 'Hero'],
            ],
        )
            ->assertCreated()
            ->assertJsonPath('data.kind', 'image')
            ->assertJsonPath('data.original_name', 'hero.jpg')
            ->assertJsonPath('data.metadata.caption', 'Hero')
            ->assertJsonMissingPath('data.storage_key')
            ->assertJsonMissingPath('data.disk')
            ->json('data.id');

        $asset = PropertyAsset::query()->findOrFail($assetId);

        $this->assertSame($org->id, $asset->organization_id);
        $this->assertSame($property->id, $asset->property_id);
        Storage::disk('local')->assertExists($asset->storage_key);

        $second = PropertyAssetFactory::new()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'position' => 1,
        ]);

        $this->getJson(
            "/api/v1/properties/{$property->id}/assets?sort=position&direction=asc&per_page=1",
        )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->patchJson(
            "/api/v1/property-assets/{$asset->id}",
            ['metadata' => ['caption' => 'Updated']],
        )
            ->assertOk()
            ->assertJsonPath('data.metadata.caption', 'Updated');

        $this->postJson(
            "/api/v1/properties/{$property->id}/assets/reorder",
            ['asset_ids' => [$second->id, $asset->id]],
        )->assertNoContent();

        $this->assertSame(0, $second->fresh()->position);
        $this->assertSame(1, $asset->fresh()->position);

        $storage = new AssetHttpInMemoryPropertyStorage;
        $storage->files[$asset->storage_key] = 'payload';

        $this->app->instance(PropertyStorage::class, $storage);

        $this->postJson(
            "/api/v1/property-assets/{$asset->id}/download",
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['url', 'expires_at'],
            ]);

        $this->deleteJson(
            "/api/v1/property-assets/{$asset->id}",
        )->assertNoContent();

        $this->assertDatabaseMissing('property_assets', [
            'id' => $asset->id,
        ]);

        $this->assertArrayNotHasKey(
            $asset->storage_key,
            $storage->files,
        );

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.created',
            'auditable_id' => $assetId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.metadata.updated',
            'auditable_id' => $assetId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.assets.reordered',
            'auditable_id' => $property->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.deleted',
            'auditable_id' => $assetId,
        ]);
    }

    public function test_guest_asset_api_is_json_unauthorized(): void
    {
        $property = PropertyFactory::new()->create();

        $this->getJson(
            "/api/v1/properties/{$property->id}/assets",
        )->assertUnauthorized();
    }

    public function test_asset_api_denies_missing_permission(): void
    {
        [$user, , $property] = $this->principal([]);

        $this->actingAs($user);

        $this->getJson(
            "/api/v1/properties/{$property->id}/assets",
        )
            ->assertForbidden()
            ->assertJsonPath('error.code', 'permission_denied');
    }

    public function test_asset_api_never_exposes_cross_tenant_property_or_asset(): void
    {
        [$user] = $this->principal([
            'property.media.view',
            'property.media.update',
            'property.media.delete',
        ]);

        $foreignProperty = PropertyFactory::new()->create();

        $foreignAsset = PropertyAssetFactory::new()->create([
            'organization_id' => $foreignProperty->organization_id,
            'property_id' => $foreignProperty->id,
        ]);

        $this->actingAs($user);

        $this->getJson(
            "/api/v1/properties/{$foreignProperty->id}/assets",
        )->assertNotFound();

        $this->patchJson(
            "/api/v1/property-assets/{$foreignAsset->id}",
            ['metadata' => ['caption' => 'Forbidden']],
        )->assertNotFound();

        $this->deleteJson(
            "/api/v1/property-assets/{$foreignAsset->id}",
        )->assertNotFound();

        $this->postJson(
            "/api/v1/property-assets/{$foreignAsset->id}/download",
        )->assertNotFound();
    }

    public function test_asset_http_validation_contracts_are_json(): void
    {
        [$user, , $property] = $this->principal([
            'property.media.create',
            'property.media.update',
        ]);

        $this->actingAs($user);

        $this->postJson(
            "/api/v1/properties/{$property->id}/assets",
            ['kind' => 'invalid'],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['kind', 'file']);

        $this->postJson(
            "/api/v1/properties/{$property->id}/assets/reorder",
            ['asset_ids' => []],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['asset_ids']);
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

        if ($permissions !== []) {
            $role = RoleFactory::new()->create([
                'organization_id' => $org->id,
            ]);

            foreach ($permissions as $code) {
                $permission = PermissionFactory::new()->create([
                    'code' => $code,
                ]);

                $role->permissions()->attach($permission);
            }

            $membership->roles()->attach($role);
        }

        $property = PropertyFactory::new()->create([
            'organization_id' => $org->id,
        ]);

        return [$user, $org, $property, $membership];
    }
}

final class AssetHttpInMemoryPropertyStorage implements PropertyStorage
{
    public array $files = [];

    public function put(
        string $key,
        string $contents,
        string $mimeType,
    ): void {
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
