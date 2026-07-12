<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\UploadPropertyDocumentCommand;
use Domain\Property\Application\Validation\PropertyMediaValidator;
use Domain\Property\Models\PropertyDocument;
use Domain\Property\Services\PropertyMediaService;

final class UploadPropertyDocumentAction
{
    public function __construct(private PropertyMediaValidator $validator, private PropertyMediaService $service) {}

    public function execute(UploadPropertyDocumentCommand $c): PropertyDocument
    {
        $this->validator->document($c->file);

        return $this->service->store($c->membership, $c->property, 'property.documents.create', PropertyDocument::class, 'category', $c->category->value, $c->file);
    }
}
