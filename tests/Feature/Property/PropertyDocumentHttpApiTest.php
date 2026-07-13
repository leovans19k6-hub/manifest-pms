<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\PropertyDocumentFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use DateTimeInterface;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Contracts\PropertyStorage;
use Domain\Property\Models\PropertyDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class PropertyDocumentHttpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_http_crud_lifecycle_download_and_audit_contracts(): void
    {
        Storage::fake('local');

        [$user, $org, $property] = $this->principal([
            'property.documents.view',
            'property.documents.create',
            'property.documents.update',
            'property.documents.delete',
        ]);

        $this->actingAs($user);

        $path = tempnam(sys_get_temp_dir(), 'document-http-');

        if ($path === false) {
            $this->fail('Unable to create temporary upload file.');
        }

        file_put_contents(
            $path,
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF",
        );

        $file = new UploadedFile(
            $path,
            'policy.pdf',
            'application/pdf',
            null,
            true,
        );

        $documentId = $this->postJson(
            "/api/v1/properties/{$property->id}/documents",
            [
                'category' => 'policy',
                'file' => $file,
                'metadata' => ['title' => 'Sales Policy'],
            ],
        )
            ->assertCreated()
            ->assertJsonPath('data.category', 'policy')
            ->assertJsonPath('data.lifecycle_status', 'active')
            ->assertJsonPath('data.original_name', 'policy.pdf')
            ->assertJsonPath('data.metadata.title', 'Sales Policy')
            ->assertJsonMissingPath('data.storage_key')
            ->assertJsonMissingPath('data.disk')
            ->json('data.id');

        $document = PropertyDocument::query()->findOrFail($documentId);

        $this->assertSame($org->id, $document->organization_id);
        $this->assertSame($property->id, $document->property_id);

        Storage::disk('local')->assertExists($document->storage_key);

        PropertyDocumentFactory::new()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'category' => 'legal',
            'lifecycle_status' => 'active',
        ]);

        $this->getJson(
            "/api/v1/properties/{$property->id}/documents"
            .'?category=policy'
            .'&lifecycle_status=active'
            .'&sort=created_at'
            .'&direction=desc'
            .'&per_page=1',
        )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $documentId)
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->patchJson(
            "/api/v1/property-documents/{$document->id}",
            ['metadata' => ['title' => 'Updated Policy']],
        )
            ->assertOk()
            ->assertJsonPath(
                'data.metadata.title',
                'Updated Policy',
            );

        $this->patchJson(
            "/api/v1/property-documents/{$document->id}/lifecycle",
            ['lifecycle_status' => 'archived'],
        )
            ->assertOk()
            ->assertJsonPath('data.lifecycle_status', 'archived');

        $this->assertNotNull($document->fresh()->archived_at);

        $storage = new DocumentHttpInMemoryPropertyStorage;
        $storage->files[$document->storage_key] = 'payload';

        $this->app->instance(PropertyStorage::class, $storage);

        $this->postJson(
            "/api/v1/property-documents/{$document->id}/download",
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['url', 'expires_at'],
            ]);

        $this->deleteJson(
            "/api/v1/property-documents/{$document->id}",
        )->assertNoContent();

        $this->assertDatabaseMissing('property_documents', [
            'id' => $document->id,
        ]);

        $this->assertArrayNotHasKey(
            $document->storage_key,
            $storage->files,
        );

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.created',
            'auditable_id' => $documentId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.metadata.updated',
            'auditable_id' => $documentId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.document.lifecycle.changed',
            'auditable_id' => $documentId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.deleted',
            'auditable_id' => $documentId,
        ]);
    }

    public function test_guest_document_api_is_json_unauthorized(): void
    {
        $property = PropertyFactory::new()->create();

        $this->getJson(
            "/api/v1/properties/{$property->id}/documents",
        )->assertUnauthorized();
    }

    public function test_document_api_denies_missing_permission(): void
    {
        [$user, , $property] = $this->principal([]);

        $this->actingAs($user);

        $this->getJson(
            "/api/v1/properties/{$property->id}/documents",
        )
            ->assertForbidden()
            ->assertJsonPath('error.code', 'permission_denied');
    }

    public function test_document_api_never_exposes_cross_tenant_property_or_document(): void
    {
        [$user] = $this->principal([
            'property.documents.view',
            'property.documents.update',
            'property.documents.delete',
        ]);

        $foreignProperty = PropertyFactory::new()->create();

        $foreignDocument = PropertyDocumentFactory::new()->create([
            'organization_id' => $foreignProperty->organization_id,
            'property_id' => $foreignProperty->id,
        ]);

        $this->actingAs($user);

        $this->getJson(
            "/api/v1/properties/{$foreignProperty->id}/documents",
        )->assertNotFound();

        $this->patchJson(
            "/api/v1/property-documents/{$foreignDocument->id}",
            ['metadata' => ['title' => 'Forbidden']],
        )->assertNotFound();

        $this->patchJson(
            "/api/v1/property-documents/{$foreignDocument->id}/lifecycle",
            ['lifecycle_status' => 'archived'],
        )->assertNotFound();

        $this->deleteJson(
            "/api/v1/property-documents/{$foreignDocument->id}",
        )->assertNotFound();

        $this->postJson(
            "/api/v1/property-documents/{$foreignDocument->id}/download",
        )->assertNotFound();
    }

    public function test_document_http_validation_contracts_are_json(): void
    {
        [$user, , $property] = $this->principal([
            'property.documents.create',
            'property.documents.update',
        ]);

        $this->actingAs($user);

        $this->postJson(
            "/api/v1/properties/{$property->id}/documents",
            ['category' => 'invalid'],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category', 'file']);

        $document = PropertyDocumentFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
        ]);

        $this->patchJson(
            "/api/v1/property-documents/{$document->id}/lifecycle",
            ['lifecycle_status' => 'invalid'],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['lifecycle_status']);
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

final class DocumentHttpInMemoryPropertyStorage implements PropertyStorage
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
