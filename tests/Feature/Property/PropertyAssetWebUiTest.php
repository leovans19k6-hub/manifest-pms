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
use RuntimeException;
use Tests\TestCase;

class PropertyAssetWebUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_web_upload_update_reorder_download_delete_and_audit_contracts(): void
    {
        [$user, $org, $property] = $this->principal([
            'property.media.view',
            'property.media.create',
            'property.media.update',
            'property.media.delete',
        ]);

        $storage = new AssetWebInMemoryPropertyStorage;
        $this->app->instance(PropertyStorage::class, $storage);

        $this->actingAs($user);

        $file = UploadedFile::fake()->createWithContent(
            'hero.jpg',
            $this->jpegContents(),
        );

        $this->post(
            "/admin/properties/{$property->id}/media/assets",
            [
                'kind' => 'image',
                'file' => $file,
                'metadata' => [
                    'caption' => 'Hero',
                ],
            ],
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHas('status');

        $asset = PropertyAsset::query()
            ->where('organization_id', $org->id)
            ->where('property_id', $property->id)
            ->firstOrFail();

        $this->assertSame('hero.jpg', $asset->original_name);
        $this->assertSame('Hero', $asset->metadata['caption']);
        $this->assertArrayHasKey($asset->storage_key, $storage->files);

        $second = PropertyAssetFactory::new()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'position' => 1,
        ]);

        $this->patch(
            "/admin/property-assets/{$asset->id}",
            [
                'metadata' => [
                    'caption' => 'Updated',
                ],
            ],
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHas('status');

        $this->assertSame(
            'Updated',
            $asset->fresh()->metadata['caption'],
        );

        $this->post(
            "/admin/properties/{$property->id}/media/assets/reorder",
            [
                'asset_ids' => [
                    $second->id,
                    $asset->id,
                ],
            ],
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHas('status');

        $this->assertSame(0, $second->fresh()->position);
        $this->assertSame(1, $asset->fresh()->position);

        $this->post(
            "/admin/property-assets/{$asset->id}/download",
        )->assertRedirect();

        $downloadLocation = $this->post(
            "/admin/property-assets/{$asset->id}/download",
        )->headers->get('Location');

        $this->assertNotNull($downloadLocation);
        $this->assertStringStartsWith(
            'signed://'.$asset->storage_key,
            $downloadLocation,
        );

        $this->delete(
            "/admin/property-assets/{$asset->id}",
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('property_assets', [
            'id' => $asset->id,
        ]);

        $this->assertArrayNotHasKey(
            $asset->storage_key,
            $storage->files,
        );

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.created',
            'auditable_id' => $asset->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.metadata.updated',
            'auditable_id' => $asset->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.assets.reordered',
            'auditable_id' => $property->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.deleted',
            'auditable_id' => $asset->id,
        ]);
    }

    public function test_asset_web_routes_enforce_permissions(): void
    {
        [$user, $org, $property] = $this->principal([]);

        $asset = PropertyAssetFactory::new()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
        ]);

        $this->actingAs($user);

        $this->post(
            "/admin/properties/{$property->id}/media/assets",
            [],
        )->assertForbidden();

        $this->patch(
            "/admin/property-assets/{$asset->id}",
            [
                'metadata' => null,
            ],
        )->assertForbidden();

        $this->post(
            "/admin/properties/{$property->id}/media/assets/reorder",
            [
                'asset_ids' => [$asset->id],
            ],
        )->assertForbidden();

        $this->post(
            "/admin/property-assets/{$asset->id}/download",
        )->assertForbidden();

        $this->delete(
            "/admin/property-assets/{$asset->id}",
        )->assertForbidden();
    }

    public function test_asset_web_routes_never_expose_cross_tenant_assets(): void
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

        $this->patch(
            "/admin/property-assets/{$foreignAsset->id}",
            [
                'metadata' => [
                    'caption' => 'Forbidden',
                ],
            ],
        )->assertNotFound();

        $this->post(
            "/admin/property-assets/{$foreignAsset->id}/download",
        )->assertNotFound();

        $this->delete(
            "/admin/property-assets/{$foreignAsset->id}",
        )->assertNotFound();
    }

    public function test_asset_web_validation_errors_redirect_back_with_errors(): void
    {
        [$user, , $property] = $this->principal([
            'property.media.create',
            'property.media.update',
        ]);

        $this->actingAs($user);

        $this->from(
            "/admin/properties/{$property->id}/media",
        )->post(
            "/admin/properties/{$property->id}/media/assets",
            [
                'kind' => 'invalid',
            ],
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHasErrors([
                'kind',
                'file',
            ]);

        $this->from(
            "/admin/properties/{$property->id}/media",
        )->post(
            "/admin/properties/{$property->id}/media/assets/reorder",
            [
                'asset_ids' => [],
            ],
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHasErrors('asset_ids');
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

    private function jpegContents(): string
    {
        return hex2bin(
            'FFD8FFE000104A46494600010100000100010000FFD9',
        );
    }
}

final class AssetWebInMemoryPropertyStorage implements PropertyStorage
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
