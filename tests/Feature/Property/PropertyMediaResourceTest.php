<?php

namespace Tests\Feature\Property;

use App\Http\Resources\PrivateDownloadResource;
use App\Http\Resources\PropertyAssetResource;
use App\Http\Resources\PropertyDocumentResource;
use Database\Factories\PropertyAssetFactory;
use Database\Factories\PropertyDocumentFactory;
use Domain\Property\Application\DTO\PrivateDownloadData;
use Domain\Property\Enums\PropertyDocumentLifecycle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PropertyMediaResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_resource_exposes_public_contract_without_storage_internals(): void
    {
        $asset = PropertyAssetFactory::new()->create([
            'disk' => 'private',
            'storage_key' => 'tenant/internal/asset.jpg',
            'checksum' => 'secret-checksum',
            'metadata' => ['alt' => 'Pool view'],
        ]);

        $response = (new PropertyAssetResource($asset))
            ->response()
            ->getData(true);

        $this->assertSame($asset->id, $response['data']['id']);
        $this->assertSame($asset->property_id, $response['data']['property_id']);
        $this->assertSame($asset->kind->value, $response['data']['kind']);
        $this->assertSame($asset->position, $response['data']['position']);
        $this->assertSame(['alt' => 'Pool view'], $response['data']['metadata']);

        $this->assertArrayNotHasKey('organization_id', $response['data']);
        $this->assertArrayNotHasKey('disk', $response['data']);
        $this->assertArrayNotHasKey('storage_key', $response['data']);
        $this->assertArrayNotHasKey('checksum', $response['data']);
    }

    public function test_document_resource_exposes_public_contract_without_storage_internals(): void
    {
        $document = PropertyDocumentFactory::new()->create([
            'disk' => 'private',
            'storage_key' => 'tenant/internal/document.pdf',
            'checksum' => 'secret-checksum',
            'lifecycle_status' => PropertyDocumentLifecycle::Active->value,
            'metadata' => ['label' => 'Contract'],
        ]);

        $response = (new PropertyDocumentResource($document))
            ->response()
            ->getData(true);

        $this->assertSame($document->id, $response['data']['id']);
        $this->assertSame($document->property_id, $response['data']['property_id']);
        $this->assertSame($document->category->value, $response['data']['category']);
        $this->assertSame(
            $document->lifecycle_status->value,
            $response['data']['lifecycle_status'],
        );
        $this->assertSame(['label' => 'Contract'], $response['data']['metadata']);

        $this->assertArrayNotHasKey('organization_id', $response['data']);
        $this->assertArrayNotHasKey('disk', $response['data']);
        $this->assertArrayNotHasKey('storage_key', $response['data']);
        $this->assertArrayNotHasKey('checksum', $response['data']);
    }

    public function test_private_download_resource_exposes_only_temporary_url_and_expiry(): void
    {
        $expiresAt = Carbon::parse('2026-07-13 12:00:00');

        $resource = new PrivateDownloadResource(
            new PrivateDownloadData(
                'https://temporary.example/download-token',
                $expiresAt,
            ),
        );

        $response = $resource->response()->getData(true);

        $this->assertSame(
            [
                'url' => 'https://temporary.example/download-token',
                'expires_at' => $expiresAt->toISOString(),
            ],
            $response['data'],
        );
    }
}
