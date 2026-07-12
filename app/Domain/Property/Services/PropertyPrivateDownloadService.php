<?php

namespace Domain\Property\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\DTO\PrivateDownloadData;
use Domain\Property\Contracts\PropertyStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class PropertyPrivateDownloadService
{
    public function __construct(private CurrentOrganization $org, private AuthorizationService $auth, private PropertyStorage $storage) {}

    public function create(OrganizationUser $m, Model $record, string $permission): PrivateDownloadData
    {
        abort_unless($this->auth->can($m, $permission), 403);
        abort_unless($m->organization_id === $this->org->id() && $record->organization_id === $this->org->id(), 404);
        if ($record->disk !== $this->storage->disk() || ! $this->storage->exists($record->storage_key)) {
            throw ValidationException::withMessages(['file' => 'Private file is unavailable.']);
        }
        $expiresAt = now()->addSeconds((int) config('property_media.download_ttl_seconds', 300));

        return new PrivateDownloadData($this->storage->temporaryUrl($record->storage_key, $expiresAt), $expiresAt);
    }
}
