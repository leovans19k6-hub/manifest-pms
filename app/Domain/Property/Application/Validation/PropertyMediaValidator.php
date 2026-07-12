<?php

namespace Domain\Property\Application\Validation;

use Domain\Property\Application\DTO\UploadFileData;
use Illuminate\Validation\ValidationException;

final class PropertyMediaValidator
{
    public function asset(UploadFileData $f): void
    {
        $this->check($f, (array) config('property_media.asset_mimes'), (int) config('property_media.asset_max_bytes'));
    }

    public function document(UploadFileData $f): void
    {
        $this->check($f, (array) config('property_media.document_mimes'), (int) config('property_media.document_max_bytes'));
    }

    private function check(UploadFileData $f, array $mimes, int $max): void
    {
        if (! in_array($f->mimeType, $mimes, true)) {
            throw ValidationException::withMessages(['mime_type' => 'Unsupported MIME type.']);
        }if ($f->size() < 1 || $f->size() > $max) {
            throw ValidationException::withMessages(['size' => 'File size is outside allowed limits.']);
        }if (trim($f->originalName) === '') {
            throw ValidationException::withMessages(['original_name' => 'Original name is required.']);
        }
    }
}
