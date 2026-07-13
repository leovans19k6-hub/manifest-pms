<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PropertyMedia\ListPropertyAssetsRequest;
use App\Http\Requests\Api\PropertyMedia\ReorderPropertyAssetsRequest;
use App\Http\Requests\Api\PropertyMedia\UpdatePropertyMediaMetadataRequest;
use App\Http\Requests\Api\PropertyMedia\UploadPropertyAssetRequest;
use App\Http\Resources\PrivateDownloadResource;
use App\Http\Resources\PropertyAssetResource;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Support\CurrentMembership;
use Domain\Property\Application\Actions\CreatePropertyAssetDownloadAction;
use Domain\Property\Application\Actions\DeletePropertyAssetAction;
use Domain\Property\Application\Actions\ReorderPropertyAssetsAction;
use Domain\Property\Application\Actions\UpdatePropertyAssetMetadataAction;
use Domain\Property\Application\Actions\UploadPropertyAssetAction;
use Domain\Property\Application\Commands\CreatePropertyAssetDownloadCommand;
use Domain\Property\Application\Commands\DeletePropertyAssetCommand;
use Domain\Property\Application\Commands\ReorderPropertyAssetsCommand;
use Domain\Property\Application\Commands\UpdatePropertyAssetMetadataCommand;
use Domain\Property\Application\Commands\UploadPropertyAssetCommand;
use Domain\Property\Application\DTO\AssetOrderData;
use Domain\Property\Application\DTO\MediaMetadataData;
use Domain\Property\Application\DTO\UploadFileData;
use Domain\Property\Enums\PropertyAssetKind;
use Domain\Property\Models\Property;
use Domain\Property\Models\PropertyAsset;
use Domain\Property\Services\PropertyMediaQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use LogicException;

class PropertyAssetController extends Controller
{
    public function __construct(private CurrentMembership $currentMembership) {}

    public function index(
        ListPropertyAssetsRequest $request,
        Property $property,
        PropertyMediaQueryService $queries,
    ): AnonymousResourceCollection {
        return PropertyAssetResource::collection(
            $queries->assets(
                $this->membership(),
                $property,
                $request->validated(),
            ),
        );
    }

    public function store(
        UploadPropertyAssetRequest $request,
        Property $property,
        UploadPropertyAssetAction $action,
    ): JsonResponse {
        $uploadedFile = $request->file('file');

        $asset = $action->execute(
            new UploadPropertyAssetCommand(
                $this->membership(),
                $property,
                PropertyAssetKind::from($request->validated('kind')),
                new UploadFileData(
                    $uploadedFile->getClientOriginalName(),
                    $uploadedFile->getMimeType(),
                    $uploadedFile->getContent(),
                    $request->validated('metadata'),
                ),
            ),
        );

        return (new PropertyAssetResource($asset))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdatePropertyMediaMetadataRequest $request,
        PropertyAsset $asset,
        UpdatePropertyAssetMetadataAction $action,
    ): PropertyAssetResource {
        $asset = $action->execute(
            new UpdatePropertyAssetMetadataCommand(
                $this->membership(),
                $asset,
                new MediaMetadataData($request->validated('metadata')),
            ),
        );

        return new PropertyAssetResource($asset);
    }

    public function reorder(
        ReorderPropertyAssetsRequest $request,
        Property $property,
        ReorderPropertyAssetsAction $action,
    ): JsonResponse {
        $action->execute(
            new ReorderPropertyAssetsCommand(
                $this->membership(),
                $property,
                new AssetOrderData($request->validated('asset_ids')),
            ),
        );

        return response()->json(null, 204);
    }

    public function destroy(
        PropertyAsset $asset,
        DeletePropertyAssetAction $action,
    ): JsonResponse {
        $action->execute(
            new DeletePropertyAssetCommand(
                $this->membership(),
                $asset,
            ),
        );

        return response()->json(null, 204);
    }

    public function download(
        PropertyAsset $asset,
        CreatePropertyAssetDownloadAction $action,
    ): PrivateDownloadResource {
        return new PrivateDownloadResource(
            $action->execute(
                new CreatePropertyAssetDownloadCommand(
                    $this->membership(),
                    $asset,
                ),
            ),
        );
    }

    private function membership(): OrganizationUser
    {
        return $this->currentMembership->get()
            ?? throw new LogicException('Current membership has not been resolved.');
    }
}
