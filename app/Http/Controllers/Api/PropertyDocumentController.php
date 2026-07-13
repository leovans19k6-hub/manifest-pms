<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PropertyMedia\ChangePropertyDocumentLifecycleRequest;
use App\Http\Requests\Api\PropertyMedia\ListPropertyDocumentsRequest;
use App\Http\Requests\Api\PropertyMedia\UpdatePropertyMediaMetadataRequest;
use App\Http\Requests\Api\PropertyMedia\UploadPropertyDocumentRequest;
use App\Http\Resources\PrivateDownloadResource;
use App\Http\Resources\PropertyDocumentResource;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Support\CurrentMembership;
use Domain\Property\Application\Actions\ChangePropertyDocumentLifecycleAction;
use Domain\Property\Application\Actions\CreatePropertyDocumentDownloadAction;
use Domain\Property\Application\Actions\DeletePropertyDocumentAction;
use Domain\Property\Application\Actions\UpdatePropertyDocumentMetadataAction;
use Domain\Property\Application\Actions\UploadPropertyDocumentAction;
use Domain\Property\Application\Commands\ChangePropertyDocumentLifecycleCommand;
use Domain\Property\Application\Commands\CreatePropertyDocumentDownloadCommand;
use Domain\Property\Application\Commands\DeletePropertyDocumentCommand;
use Domain\Property\Application\Commands\UpdatePropertyDocumentMetadataCommand;
use Domain\Property\Application\Commands\UploadPropertyDocumentCommand;
use Domain\Property\Application\DTO\MediaMetadataData;
use Domain\Property\Application\DTO\UploadFileData;
use Domain\Property\Enums\PropertyDocumentCategory;
use Domain\Property\Enums\PropertyDocumentLifecycle;
use Domain\Property\Models\Property;
use Domain\Property\Models\PropertyDocument;
use Domain\Property\Services\PropertyMediaQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use LogicException;

class PropertyDocumentController extends Controller
{
    public function __construct(private CurrentMembership $currentMembership) {}

    public function index(
        ListPropertyDocumentsRequest $request,
        Property $property,
        PropertyMediaQueryService $queries,
    ): AnonymousResourceCollection {
        return PropertyDocumentResource::collection(
            $queries->documents(
                $this->membership(),
                $property,
                $request->validated(),
            ),
        );
    }

    public function store(
        UploadPropertyDocumentRequest $request,
        Property $property,
        UploadPropertyDocumentAction $action,
    ): JsonResponse {
        $uploadedFile = $request->file('file');

        $document = $action->execute(
            new UploadPropertyDocumentCommand(
                $this->membership(),
                $property,
                PropertyDocumentCategory::from(
                    $request->validated('category'),
                ),
                new UploadFileData(
                    $uploadedFile->getClientOriginalName(),
                    $uploadedFile->getMimeType(),
                    $uploadedFile->getContent(),
                    $request->validated('metadata'),
                ),
            ),
        );

        return (new PropertyDocumentResource($document))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdatePropertyMediaMetadataRequest $request,
        PropertyDocument $document,
        UpdatePropertyDocumentMetadataAction $action,
    ): PropertyDocumentResource {
        $document = $action->execute(
            new UpdatePropertyDocumentMetadataCommand(
                $this->membership(),
                $document,
                new MediaMetadataData(
                    $request->validated('metadata'),
                ),
            ),
        );

        return new PropertyDocumentResource($document);
    }

    public function changeLifecycle(
        ChangePropertyDocumentLifecycleRequest $request,
        PropertyDocument $document,
        ChangePropertyDocumentLifecycleAction $action,
    ): PropertyDocumentResource {
        $document = $action->execute(
            new ChangePropertyDocumentLifecycleCommand(
                $this->membership(),
                $document,
                PropertyDocumentLifecycle::from(
                    $request->validated('lifecycle_status'),
                ),
            ),
        );

        return new PropertyDocumentResource($document);
    }

    public function destroy(
        PropertyDocument $document,
        DeletePropertyDocumentAction $action,
    ): JsonResponse {
        $action->execute(
            new DeletePropertyDocumentCommand(
                $this->membership(),
                $document,
            ),
        );

        return response()->json(null, 204);
    }

    public function download(
        PropertyDocument $document,
        CreatePropertyDocumentDownloadAction $action,
    ): PrivateDownloadResource {
        return new PrivateDownloadResource(
            $action->execute(
                new CreatePropertyDocumentDownloadCommand(
                    $this->membership(),
                    $document,
                ),
            ),
        );
    }

    private function membership(): OrganizationUser
    {
        return $this->currentMembership->get()
            ?? throw new LogicException(
                'Current membership has not been resolved.',
            );
    }
}
