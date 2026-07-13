<?php

namespace Tests\Feature\Property;

use App\Http\Requests\Api\PropertyMedia\ChangePropertyDocumentLifecycleRequest;
use App\Http\Requests\Api\PropertyMedia\ListPropertyAssetsRequest;
use App\Http\Requests\Api\PropertyMedia\ListPropertyDocumentsRequest;
use App\Http\Requests\Api\PropertyMedia\ReorderPropertyAssetsRequest;
use App\Http\Requests\Api\PropertyMedia\UpdatePropertyMediaMetadataRequest;
use App\Http\Requests\Api\PropertyMedia\UploadPropertyAssetRequest;
use App\Http\Requests\Api\PropertyMedia\UploadPropertyDocumentRequest;
use Domain\Property\Enums\PropertyAssetKind;
use Domain\Property\Enums\PropertyDocumentCategory;
use Domain\Property\Enums\PropertyDocumentLifecycle;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PropertyMediaFormRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::post('/_test/property-media/asset-upload', fn (UploadPropertyAssetRequest $request) => response()->json($request->validated()));

        Route::post('/_test/property-media/document-upload', fn (UploadPropertyDocumentRequest $request) => response()->json($request->validated()));

        Route::get('/_test/property-media/assets', fn (ListPropertyAssetsRequest $request) => response()->json($request->validated()));

        Route::get('/_test/property-media/documents', fn (ListPropertyDocumentsRequest $request) => response()->json($request->validated()));

        Route::patch('/_test/property-media/metadata', fn (UpdatePropertyMediaMetadataRequest $request) => response()->json($request->validated()));

        Route::post('/_test/property-media/reorder', fn (ReorderPropertyAssetsRequest $request) => response()->json($request->validated()));

        Route::patch('/_test/property-media/lifecycle', fn (ChangePropertyDocumentLifecycleRequest $request) => response()->json($request->validated()));
    }

    public function test_asset_upload_requires_valid_kind_and_file(): void
    {
        $validKind = PropertyAssetKind::cases()[0]->value;

        $this->postJson('/_test/property-media/asset-upload', [
            'kind' => $validKind,
        ])->assertUnprocessable()->assertJsonValidationErrors(['file']);

        $this->postJson('/_test/property-media/asset-upload', [
            'kind' => '__invalid__',
        ])->assertUnprocessable()->assertJsonValidationErrors(['file', 'kind']);
    }

    public function test_document_upload_requires_valid_category_and_file(): void
    {
        $validCategory = PropertyDocumentCategory::cases()[0]->value;

        $this->postJson('/_test/property-media/document-upload', [
            'category' => $validCategory,
        ])->assertUnprocessable()->assertJsonValidationErrors(['file']);

        $this->postJson('/_test/property-media/document-upload', [
            'category' => '__invalid__',
        ])->assertUnprocessable()->assertJsonValidationErrors(['file', 'category']);
    }

    public function test_asset_query_contract_validates_sort_direction_and_page_size(): void
    {
        $this->getJson('/_test/property-media/assets?sort=invalid&direction=sideways&per_page=101')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort', 'direction', 'per_page']);

        $this->getJson('/_test/property-media/assets?sort=position&direction=desc&per_page=100')
            ->assertOk()
            ->assertJson([
                'sort' => 'position',
                'direction' => 'desc',
                'per_page' => 100,
            ]);
    }

    public function test_document_query_contract_validates_lifecycle_and_sort(): void
    {
        $this->getJson('/_test/property-media/documents?lifecycle_status=invalid&sort=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['lifecycle_status', 'sort']);

        $lifecycle = PropertyDocumentLifecycle::cases()[0]->value;

        $this->getJson("/_test/property-media/documents?lifecycle_status={$lifecycle}&sort=created_at")
            ->assertOk()
            ->assertJson([
                'lifecycle_status' => $lifecycle,
                'sort' => 'created_at',
            ]);
    }

    public function test_metadata_contract_requires_key_but_allows_null_or_array(): void
    {
        $this->patchJson('/_test/property-media/metadata', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['metadata']);

        $this->patchJson('/_test/property-media/metadata', ['metadata' => null])
            ->assertOk()
            ->assertJson(['metadata' => null]);

        $this->patchJson('/_test/property-media/metadata', [
            'metadata' => ['alt' => 'Pool view'],
        ])->assertOk()->assertJsonPath('metadata.alt', 'Pool view');
    }

    public function test_reorder_requires_non_empty_distinct_asset_ids(): void
    {
        $this->postJson('/_test/property-media/reorder', [
            'asset_ids' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors(['asset_ids']);

        $this->postJson('/_test/property-media/reorder', [
            'asset_ids' => ['asset-1', 'asset-1'],
        ])->assertUnprocessable()->assertJsonValidationErrors(['asset_ids.0', 'asset_ids.1']);

        $this->postJson('/_test/property-media/reorder', [
            'asset_ids' => ['asset-1', 'asset-2'],
        ])->assertOk()->assertJson([
            'asset_ids' => ['asset-1', 'asset-2'],
        ]);
    }

    public function test_lifecycle_requires_valid_enum_value(): void
    {
        $this->patchJson('/_test/property-media/lifecycle', [
            'lifecycle_status' => '__invalid__',
        ])->assertUnprocessable()->assertJsonValidationErrors(['lifecycle_status']);

        $lifecycle = PropertyDocumentLifecycle::Archived->value;

        $this->patchJson('/_test/property-media/lifecycle', [
            'lifecycle_status' => $lifecycle,
        ])->assertOk()->assertJson([
            'lifecycle_status' => $lifecycle,
        ]);
    }
}
