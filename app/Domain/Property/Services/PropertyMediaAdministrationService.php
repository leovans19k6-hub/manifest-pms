<?php

namespace Domain\Property\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuditLogger;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\DTO\AssetOrderData;
use Domain\Property\Contracts\PropertyStorage;
use Domain\Property\Enums\PropertyDocumentLifecycle;
use Domain\Property\Models\Property;
use Domain\Property\Models\PropertyAsset;
use Domain\Property\Models\PropertyDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

final class PropertyMediaAdministrationService
{
    public function __construct(private CurrentOrganization $org, private AuthorizationService $auth, private AuditLogger $audit, private PropertyStorage $storage) {}

    public function updateMetadata(OrganizationUser $m, Model $record, string $permission, ?array $metadata): Model
    {
        $this->guardRecord($m, $record, $permission);

        return DB::transaction(function () use ($record, $metadata): Model {
            $old = $record->getAttributes();
            $record->update(['metadata' => $metadata]);
            $this->audit->record('property.media.metadata.updated', $record, $old, $record->fresh()->getAttributes());

            return $record->fresh();
        });
    }

    public function reorder(OrganizationUser $m, Property $property, AssetOrderData $order): void
    {
        $this->guardProperty($m, $property, 'property.media.update');
        DB::transaction(function () use ($property, $order): void {
            $assets = PropertyAsset::query()->where('organization_id', $this->org->id())->where('property_id', $property->id)->whereIn('id', $order->assetIds)->lockForUpdate()->get();
            if ($assets->count() !== count($order->assetIds)) {
                throw ValidationException::withMessages(['asset_ids' => 'Order contains foreign or missing assets.']);
            }
            foreach ($order->assetIds as $position => $id) {
                PropertyAsset::query()->whereKey($id)->update(['position' => $position]);
            }
            $this->audit->record('property.assets.reordered', $property, [], ['asset_ids' => $order->assetIds]);
        });
    }

    public function changeLifecycle(OrganizationUser $m, PropertyDocument $document, PropertyDocumentLifecycle $lifecycle): PropertyDocument
    {
        $this->guardRecord($m, $document, 'property.documents.update');

        return DB::transaction(function () use ($document, $lifecycle): PropertyDocument {
            $old = $document->getAttributes();
            $document->update(['lifecycle_status' => $lifecycle->value, 'archived_at' => $lifecycle === PropertyDocumentLifecycle::Archived ? now() : null]);
            $this->audit->record('property.document.lifecycle.changed', $document, $old, $document->fresh()->getAttributes());

            return $document->fresh();
        });
    }

    public function delete(OrganizationUser $m, Model $record, string $permission): void
    {
        $this->guardRecord($m, $record, $permission);
        $key = (string) $record->storage_key;
        $mime = (string) $record->mime_type;
        if (! $this->storage->exists($key)) {
            throw ValidationException::withMessages(['file' => 'Stored file is missing.']);
        }
        $backup = $this->storage->get($key);
        $this->storage->delete($key);
        try {
            DB::transaction(function () use ($record): void {
                $old = $record->getAttributes();
                $this->audit->record('property.media.deleted', $record, $old, []);
                $record->delete();
            });
        } catch (Throwable $e) {
            $this->storage->put($key, $backup, $mime);
            throw $e;
        }
    }

    private function guardProperty(OrganizationUser $m, Property $p, string $permission): void
    {
        abort_unless($this->auth->can($m, $permission), 403);
        abort_unless($m->organization_id === $this->org->id() && $p->organization_id === $this->org->id(), 404);
    }

    private function guardRecord(OrganizationUser $m, Model $record, string $permission): void
    {
        abort_unless($this->auth->can($m, $permission), 403);
        abort_unless($m->organization_id === $this->org->id() && $record->organization_id === $this->org->id(), 404);
    }
}
