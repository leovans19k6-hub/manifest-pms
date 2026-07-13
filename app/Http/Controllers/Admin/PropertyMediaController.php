<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyMedia\ChangePropertyDocumentLifecycleWebRequest;
use App\Http\Requests\PropertyMedia\ListPropertyMediaRequest;
use App\Http\Requests\PropertyMedia\ReorderPropertyAssetsWebRequest;
use App\Http\Requests\PropertyMedia\UpdatePropertyAssetMetadataWebRequest;
use App\Http\Requests\PropertyMedia\UpdatePropertyDocumentMetadataWebRequest;
use App\Http\Requests\PropertyMedia\UploadPropertyAssetWebRequest;
use App\Http\Requests\PropertyMedia\UploadPropertyDocumentWebRequest;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\Actions\ChangePropertyDocumentLifecycleAction;
use Domain\Property\Application\Actions\CreatePropertyAssetDownloadAction;
use Domain\Property\Application\Actions\CreatePropertyDocumentDownloadAction;
use Domain\Property\Application\Actions\DeletePropertyAssetAction;
use Domain\Property\Application\Actions\DeletePropertyDocumentAction;
use Domain\Property\Application\Actions\ReorderPropertyAssetsAction;
use Domain\Property\Application\Actions\UpdatePropertyAssetMetadataAction;
use Domain\Property\Application\Actions\UpdatePropertyDocumentMetadataAction;
use Domain\Property\Application\Actions\UploadPropertyAssetAction;
use Domain\Property\Application\Actions\UploadPropertyDocumentAction;
use Domain\Property\Application\Commands\ChangePropertyDocumentLifecycleCommand;
use Domain\Property\Application\Commands\CreatePropertyAssetDownloadCommand;
use Domain\Property\Application\Commands\CreatePropertyDocumentDownloadCommand;
use Domain\Property\Application\Commands\DeletePropertyAssetCommand;
use Domain\Property\Application\Commands\DeletePropertyDocumentCommand;
use Domain\Property\Application\Commands\ReorderPropertyAssetsCommand;
use Domain\Property\Application\Commands\UpdatePropertyAssetMetadataCommand;
use Domain\Property\Application\Commands\UpdatePropertyDocumentMetadataCommand;
use Domain\Property\Application\Commands\UploadPropertyAssetCommand;
use Domain\Property\Application\Commands\UploadPropertyDocumentCommand;
use Domain\Property\Application\DTO\AssetOrderData;
use Domain\Property\Application\DTO\MediaMetadataData;
use Domain\Property\Application\DTO\UploadFileData;
use Domain\Property\Enums\PropertyAssetKind;
use Domain\Property\Enums\PropertyDocumentCategory;
use Domain\Property\Enums\PropertyDocumentLifecycle;
use Domain\Property\Models\PropertyAsset;
use Domain\Property\Models\PropertyDocument;
use Domain\Property\Services\PropertyMediaQueryService;
use Domain\Property\Services\PropertyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyMediaController extends Controller
{
    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
    ) {}

    public function index(
        ListPropertyMediaRequest $request,
        string $property,
        PropertyService $properties,
        PropertyMediaQueryService $media,
    ): View {
        $membership = $this->membership($request);
        $propertyModel = $properties->find($property);
        $filters = $request->validated();

        $canViewAssets = $this->authorization->can(
            $membership,
            'property.media.view',
        );

        $canViewDocuments = $this->authorization->can(
            $membership,
            'property.documents.view',
        );

        abort_unless($canViewAssets || $canViewDocuments, 403);

        $assets = null;

        if ($canViewAssets) {
            $assets = $media->assets(
                $membership,
                $propertyModel,
                $this->assetFilters($filters),
            )->withQueryString();
        }

        $documents = null;

        if ($canViewDocuments) {
            $documents = $media->documents(
                $membership,
                $propertyModel,
                $this->documentFilters($filters),
            )->withQueryString();
        }

        return view('admin.properties.media.index', [
            'property' => $propertyModel,
            'assets' => $assets,
            'documents' => $documents,
            'filters' => $filters,
            'abilities' => $this->abilities($membership),
            'assetKinds' => PropertyAssetKind::cases(),
            'documentCategories' => PropertyDocumentCategory::cases(),
            'documentLifecycles' => PropertyDocumentLifecycle::cases(),
        ]);
    }

    public function storeAsset(
        UploadPropertyAssetWebRequest $request,
        string $property,
        PropertyService $properties,
        UploadPropertyAssetAction $action,
    ): RedirectResponse {
        $membership = $this->membership($request);
        $propertyModel = $properties->find($property);
        $file = $request->file('file');

        $action->execute(new UploadPropertyAssetCommand(
            $membership,
            $propertyModel,
            PropertyAssetKind::from($request->validated('kind')),
            new UploadFileData(
                $file->getClientOriginalName(),
                (string) $file->getMimeType(),
                (string) $file->get(),
                $request->validated('metadata'),
            ),
        ));

        return $this->mediaRedirect($propertyModel->id)
            ->with('status', 'Đã tải media lên.');
    }

    public function updateAsset(
        UpdatePropertyAssetMetadataWebRequest $request,
        string $asset,
        UpdatePropertyAssetMetadataAction $action,
    ): RedirectResponse {
        $assetModel = $this->asset($asset);

        $action->execute(new UpdatePropertyAssetMetadataCommand(
            $this->membership($request),
            $assetModel,
            new MediaMetadataData($request->validated('metadata')),
        ));

        return $this->mediaRedirect($assetModel->property_id)
            ->with('status', 'Đã cập nhật metadata.');
    }

    public function reorderAssets(
        ReorderPropertyAssetsWebRequest $request,
        string $property,
        PropertyService $properties,
        ReorderPropertyAssetsAction $action,
    ): RedirectResponse {
        $propertyModel = $properties->find($property);

        $action->execute(new ReorderPropertyAssetsCommand(
            $this->membership($request),
            $propertyModel,
            new AssetOrderData($request->validated('asset_ids')),
        ));

        return $this->mediaRedirect($propertyModel->id)
            ->with('status', 'Đã cập nhật thứ tự media.');
    }

    public function downloadAsset(
        Request $request,
        string $asset,
        CreatePropertyAssetDownloadAction $action,
    ): RedirectResponse {
        $assetModel = $this->asset($asset);

        $download = $action->execute(new CreatePropertyAssetDownloadCommand(
            $this->membership($request),
            $assetModel,
        ));

        return redirect()->away($download->url);
    }

    public function destroyAsset(
        Request $request,
        string $asset,
        DeletePropertyAssetAction $action,
    ): RedirectResponse {
        $assetModel = $this->asset($asset);
        $propertyId = $assetModel->property_id;

        $action->execute(new DeletePropertyAssetCommand(
            $this->membership($request),
            $assetModel,
        ));

        return $this->mediaRedirect($propertyId)
            ->with('status', 'Đã xóa media.');
    }

    public function storeDocument(
        UploadPropertyDocumentWebRequest $request,
        string $property,
        PropertyService $properties,
        UploadPropertyDocumentAction $action,
    ): RedirectResponse {
        $membership = $this->membership($request);
        $propertyModel = $properties->find($property);
        $file = $request->file('file');

        $action->execute(new UploadPropertyDocumentCommand(
            $membership,
            $propertyModel,
            PropertyDocumentCategory::from($request->validated('category')),
            new UploadFileData(
                $file->getClientOriginalName(),
                (string) $file->getMimeType(),
                (string) $file->get(),
                $request->validated('metadata'),
            ),
        ));

        return $this->mediaRedirect($propertyModel->id)
            ->with('status', 'Đã tải tài liệu lên.');
    }

    public function updateDocument(
        UpdatePropertyDocumentMetadataWebRequest $request,
        string $document,
        UpdatePropertyDocumentMetadataAction $action,
    ): RedirectResponse {
        $documentModel = $this->document($document);

        $action->execute(new UpdatePropertyDocumentMetadataCommand(
            $this->membership($request),
            $documentModel,
            new MediaMetadataData($request->validated('metadata')),
        ));

        return $this->mediaRedirect($documentModel->property_id)
            ->with('status', 'Đã cập nhật metadata tài liệu.');
    }

    public function changeDocumentLifecycle(
        ChangePropertyDocumentLifecycleWebRequest $request,
        string $document,
        ChangePropertyDocumentLifecycleAction $action,
    ): RedirectResponse {
        $documentModel = $this->document($document);

        $action->execute(new ChangePropertyDocumentLifecycleCommand(
            $this->membership($request),
            $documentModel,
            PropertyDocumentLifecycle::from(
                $request->validated('lifecycle_status'),
            ),
        ));

        return $this->mediaRedirect($documentModel->property_id)
            ->with('status', 'Đã cập nhật trạng thái tài liệu.');
    }

    public function downloadDocument(
        Request $request,
        string $document,
        CreatePropertyDocumentDownloadAction $action,
    ): RedirectResponse {
        $documentModel = $this->document($document);

        $download = $action->execute(new CreatePropertyDocumentDownloadCommand(
            $this->membership($request),
            $documentModel,
        ));

        return redirect()->away($download->url);
    }

    public function destroyDocument(
        Request $request,
        string $document,
        DeletePropertyDocumentAction $action,
    ): RedirectResponse {
        $documentModel = $this->document($document);
        $propertyId = $documentModel->property_id;

        $action->execute(new DeletePropertyDocumentCommand(
            $this->membership($request),
            $documentModel,
        ));

        return $this->mediaRedirect($propertyId)
            ->with('status', 'Đã xóa tài liệu.');
    }

    private function membership(Request $request): OrganizationUser
    {
        return OrganizationUser::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('organization_id', $this->organization->id())
            ->firstOrFail();
    }

    private function asset(string $asset): PropertyAsset
    {
        return PropertyAsset::query()
            ->where('organization_id', $this->organization->id())
            ->findOrFail($asset);
    }

    private function document(string $document): PropertyDocument
    {
        return PropertyDocument::query()
            ->where('organization_id', $this->organization->id())
            ->findOrFail($document);
    }

    private function mediaRedirect(string $property): RedirectResponse
    {
        return redirect()->route('admin.properties.media.index', $property);
    }

    private function assetFilters(array $filters): array
    {
        return array_filter([
            'kind' => $filters['asset_kind'] ?? null,
            'sort' => $filters['asset_sort'] ?? null,
            'direction' => $filters['direction'] ?? null,
            'per_page' => $filters['asset_per_page'] ?? null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function documentFilters(array $filters): array
    {
        return array_filter([
            'category' => $filters['document_category'] ?? null,
            'lifecycle_status' => $filters['document_lifecycle'] ?? null,
            'sort' => $filters['document_sort'] ?? null,
            'direction' => $filters['direction'] ?? null,
            'per_page' => $filters['document_per_page'] ?? null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function abilities(OrganizationUser $membership): array
    {
        return [
            'assets' => [
                'view' => $this->authorization->can($membership, 'property.media.view'),
                'create' => $this->authorization->can($membership, 'property.media.create'),
                'update' => $this->authorization->can($membership, 'property.media.update'),
                'delete' => $this->authorization->can($membership, 'property.media.delete'),
            ],
            'documents' => [
                'view' => $this->authorization->can($membership, 'property.documents.view'),
                'create' => $this->authorization->can($membership, 'property.documents.create'),
                'update' => $this->authorization->can($membership, 'property.documents.update'),
                'delete' => $this->authorization->can($membership, 'property.documents.delete'),
            ],
        ];
    }
}
