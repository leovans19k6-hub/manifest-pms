<?php

namespace Domain\Property\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuditLogger;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\DTO\UploadFileData;
use Domain\Property\Contracts\PropertyStorage;
use Domain\Property\Models\Property;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PropertyMediaService
{
    public function __construct(private CurrentOrganization $org, private AuthorizationService $auth, private AuditLogger $audit, private PropertyStorage $storage) {}

    public function store(OrganizationUser $m, Property $p, string $permission, string $modelClass, string $classifier, string $value, UploadFileData $f): Model
    {
        $this->assert($m, $p, $permission);
        $key = 'organizations/'.$this->org->id().'/properties/'.$p->id.'/'.str()->ulid().'-'.basename($f->originalName);
        $this->storage->put($key, $f->contents, $f->mimeType);
        try {
            return DB::transaction(function () use ($p, $modelClass, $classifier, $value, $f, $key) {
                $record = $modelClass::query()->create(['organization_id' => $this->org->id(), 'property_id' => $p->id, $classifier => $value, 'disk' => $this->storage->disk(), 'storage_key' => $key, 'original_name' => $f->originalName, 'mime_type' => $f->mimeType, 'size_bytes' => $f->size(), 'checksum' => hash('sha256', $f->contents), 'metadata' => $f->metadata]);
                $this->audit->record('property.media.created', $record, [], $record->getAttributes());

                return $record;
            });
        } catch (\Throwable $e) {
            $this->storage->delete($key);
            throw $e;
        }
    }

    private function assert(OrganizationUser $m, Property $p, string $permission): void
    {
        abort_unless($this->auth->can($m, $permission), 403);
        if ($m->organization_id !== $this->org->id() || $p->organization_id !== $this->org->id()) {
            throw ValidationException::withMessages(['property' => 'Property does not belong to current organization.']);
        }
    }
}
