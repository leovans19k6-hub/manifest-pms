<?php

namespace Domain\Property\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\DTO\PrivateDownloadData;
use Domain\Property\Contracts\PropertyStorage;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

final class PropertyPrivateDownloadService
{
    public function __construct(
        private CurrentOrganization $org,
        private AuthorizationService $auth,
        private PropertyStorage $storage,
    ) {}

    public function create(
        OrganizationUser $m,
        Model $media,
        string $permission,
    ): PrivateDownloadData {
        abort_unless(
            $this->auth->can($m, $permission),
            403,
        );

        abort_unless(
            $m->organization_id === $this->org->id()
            && $media->organization_id === $this->org->id(),
            404,
        );

        $ttl = (int) config(
            'property_media.download_ttl_seconds',
            300,
        );

        if ($ttl <= 0) {
            throw new RuntimeException(
                'Property media download TTL must be positive.',
            );
        }

        $expiresAt = now()->addSeconds($ttl);

        return new PrivateDownloadData(
            $this->storage->temporaryUrl(
                $media->storage_key,
                $expiresAt,
            ),
            $expiresAt,
        );
    }
}
