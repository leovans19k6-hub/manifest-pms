<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyMedia\ListPropertyMediaRequest;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Services\PropertyMediaQueryService;
use Domain\Property\Services\PropertyService;
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
        ]);
    }

    private function membership(
        ListPropertyMediaRequest $request,
    ): OrganizationUser {
        return OrganizationUser::query()
            ->where(
                'user_id',
                $request->user()->getAuthIdentifier(),
            )
            ->where(
                'organization_id',
                $this->organization->id(),
            )
            ->firstOrFail();
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
                'view' => $this->authorization->can(
                    $membership,
                    'property.media.view',
                ),
                'create' => $this->authorization->can(
                    $membership,
                    'property.media.create',
                ),
                'update' => $this->authorization->can(
                    $membership,
                    'property.media.update',
                ),
                'delete' => $this->authorization->can(
                    $membership,
                    'property.media.delete',
                ),
            ],
            'documents' => [
                'view' => $this->authorization->can(
                    $membership,
                    'property.documents.view',
                ),
                'create' => $this->authorization->can(
                    $membership,
                    'property.documents.create',
                ),
                'update' => $this->authorization->can(
                    $membership,
                    'property.documents.update',
                ),
                'delete' => $this->authorization->can(
                    $membership,
                    'property.documents.delete',
                ),
            ],
        ];
    }
}
