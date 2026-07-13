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
use RuntimeException;
use Tests\TestCase;

class PropertyDocumentWebUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_web_upload_update_lifecycle_download_delete_and_audit_contracts(): void
    {
        [$user, $org, $property] = $this->principal([
            'property.documents.view',
            'property.documents.create',
            'property.documents.update',
            'property.documents.delete',
        ]);

        $storage = new DocumentWebInMemoryPropertyStorage;
        $this->app->instance(PropertyStorage::class, $storage);

        $this->actingAs($user);

        $file = UploadedFile::fake()->createWithContent(
            'policy.pdf',
            $this->pdfContents(),
        );

        $this->post(
            "/admin/properties/{$property->id}/media/documents",
            [
                'category' => 'policy',
                'file' => $file,
                'metadata' => [
                    'caption' => 'Sales policy',
                ],
            ],
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHas('status');

        $document = PropertyDocument::query()
            ->where('organization_id', $org->id)
            ->where('property_id', $property->id)
            ->firstOrFail();

        $this->assertSame('policy.pdf', $document->original_name);
        $this->assertSame(
            'Sales policy',
            $document->metadata['caption'],
        );
        $this->assertArrayHasKey(
            $document->storage_key,
            $storage->files,
        );

        $this->patch(
            "/admin/property-documents/{$document->id}",
            [
                'metadata' => [
                    'caption' => 'Updated policy',
                ],
            ],
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHas('status');

        $this->assertSame(
            'Updated policy',
            $document->fresh()->metadata['caption'],
        );

        $this->patch(
            "/admin/property-documents/{$document->id}/lifecycle",
            [
                'lifecycle_status' => 'archived',
            ],
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHas('status');

        $this->assertSame(
            'archived',
            $document->fresh()->lifecycle_status->value,
        );

        $this->assertNotNull(
            $document->fresh()->archived_at,
        );

        $downloadResponse = $this->post(
            "/admin/property-documents/{$document->id}/download",
        );

        $downloadResponse->assertRedirect();

        $downloadLocation = $downloadResponse
            ->headers
            ->get('Location');

        $this->assertNotNull($downloadLocation);

        $this->assertStringStartsWith(
            'signed://'.$document->storage_key,
            $downloadLocation,
        );

        $this->delete(
            "/admin/property-documents/{$document->id}",
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('property_documents', [
            'id' => $document->id,
        ]);

        $this->assertArrayNotHasKey(
            $document->storage_key,
            $storage->files,
        );

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.created',
            'auditable_id' => $document->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.metadata.updated',
            'auditable_id' => $document->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.document.lifecycle.changed',
            'auditable_id' => $document->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'property.media.deleted',
            'auditable_id' => $document->id,
        ]);
    }

    public function test_document_web_routes_enforce_permissions(): void
    {
        [$user, $org, $property] = $this->principal([]);

        $document = PropertyDocumentFactory::new()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
        ]);

        $this->actingAs($user);

        $this->post(
            "/admin/properties/{$property->id}/media/documents",
            [],
        )->assertForbidden();

        $this->patch(
            "/admin/property-documents/{$document->id}",
            [
                'metadata' => null,
            ],
        )->assertForbidden();

        $this->patch(
            "/admin/property-documents/{$document->id}/lifecycle",
            [
                'lifecycle_status' => 'archived',
            ],
        )->assertForbidden();

        $this->post(
            "/admin/property-documents/{$document->id}/download",
        )->assertForbidden();

        $this->delete(
            "/admin/property-documents/{$document->id}",
        )->assertForbidden();
    }

    public function test_document_web_routes_never_expose_cross_tenant_documents(): void
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

        $this->patch(
            "/admin/property-documents/{$foreignDocument->id}",
            [
                'metadata' => [
                    'caption' => 'Forbidden',
                ],
            ],
        )->assertNotFound();

        $this->patch(
            "/admin/property-documents/{$foreignDocument->id}/lifecycle",
            [
                'lifecycle_status' => 'archived',
            ],
        )->assertNotFound();

        $this->post(
            "/admin/property-documents/{$foreignDocument->id}/download",
        )->assertNotFound();

        $this->delete(
            "/admin/property-documents/{$foreignDocument->id}",
        )->assertNotFound();
    }

    public function test_document_web_validation_errors_redirect_back_with_errors(): void
    {
        [$user, , $property] = $this->principal([
            'property.documents.create',
            'property.documents.update',
        ]);

        $this->actingAs($user);

        $this->from(
            "/admin/properties/{$property->id}/media",
        )->post(
            "/admin/properties/{$property->id}/media/documents",
            [
                'category' => 'invalid',
            ],
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHasErrors([
                'category',
                'file',
            ]);

        $document = PropertyDocumentFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
        ]);

        $this->from(
            "/admin/properties/{$property->id}/media",
        )->patch(
            "/admin/property-documents/{$document->id}/lifecycle",
            [
                'lifecycle_status' => 'invalid',
            ],
        )
            ->assertRedirect(
                "/admin/properties/{$property->id}/media",
            )
            ->assertSessionHasErrors('lifecycle_status');
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

    private function pdfContents(): string
    {
        return <<<'PDF'
%PDF-1.4
1 0 obj
<< /Type /Catalog >>
endobj
trailer
<< /Root 1 0 R >>
%%EOF
PDF;
    }
}

final class DocumentWebInMemoryPropertyStorage implements PropertyStorage
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
