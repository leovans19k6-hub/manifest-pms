<?php

namespace Domain\Property\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuditLogger;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\DTO\UploadFileData;
use Domain\Property\Contracts\PropertyStorage;
use Domain\Property\Enums\PropertyDocumentLifecycle;
use Domain\Property\Models\Property;
use Domain\Property\Models\PropertyDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

final class PropertyMediaService
{
    public function __construct(
        private CurrentOrganization $org,
        private AuthorizationService $auth,
        private AuditLogger $audit,
        private PropertyStorage $storage,
    ) {}

    public function store(
        OrganizationUser $m,
        Property $p,
        string $permission,
        string $modelClass,
        string $classifier,
        string $value,
        UploadFileData $f,
    ): Model {
        $this->assert($m, $p, $permission);

        $originalName = $this->normalizeOriginalName(
            $f->originalName,
        );

        $key = 'organizations/'
            .$this->org->id()
            .'/properties/'
            .$p->id
            .'/'
            .str()->ulid()
            .'-'
            .$originalName;

        $this->storage->put(
            $key,
            $f->contents,
            $f->mimeType,
        );

        try {
            return DB::transaction(function () use (
                $p,
                $modelClass,
                $classifier,
                $value,
                $f,
                $key,
                $originalName,
            ): Model {
                $attributes = [
                    'organization_id' => $this->org->id(),
                    'property_id' => $p->id,
                    $classifier => $value,
                    'disk' => $this->storage->disk(),
                    'storage_key' => $key,
                    'original_name' => $originalName,
                    'mime_type' => $f->mimeType,
                    'size_bytes' => $f->size(),
                    'checksum' => hash('sha256', $f->contents),
                    'metadata' => $f->metadata,
                ];

                if ($modelClass === PropertyDocument::class) {
                    $attributes['lifecycle_status']
                        = PropertyDocumentLifecycle::Active->value;
                }

                $record = $modelClass::query()->create($attributes);

                $this->audit->record(
                    'property.media.created',
                    $record,
                    [],
                    $record->getAttributes(),
                );

                return $record;
            });
        } catch (Throwable $original) {
            try {
                $this->storage->delete($key);
            } catch (Throwable) {
                // Cleanup is best-effort.
                // Never hide the original persistence/audit failure.
            }

            throw $original;
        }
    }

    private function normalizeOriginalName(string $originalName): string
    {
        $normalized = str_replace('\\', '/', trim($originalName));
        $normalized = basename($normalized);
        $normalized = trim($normalized);

        $normalized = preg_replace(
            '/[^A-Za-z0-9._-]+/',
            '_',
            $normalized,
        );

        if (
            ! is_string($normalized)
            || $normalized === ''
            || $normalized === '.'
            || $normalized === '..'
        ) {
            return 'file';
        }

        return $normalized;
    }

    private function assert(
        OrganizationUser $m,
        Property $p,
        string $permission,
    ): void {
        abort_unless(
            $this->auth->can($m, $permission),
            403,
        );

        if (
            $m->organization_id !== $this->org->id()
            || $p->organization_id !== $this->org->id()
        ) {
            throw ValidationException::withMessages([
                'property' => 'Property does not belong to current organization.',
            ]);
        }
    }
}
