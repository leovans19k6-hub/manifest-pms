<?php

namespace Domain\Property\Models;

use Domain\Property\Enums\PropertyDocumentCategory;
use Domain\Property\Enums\PropertyDocumentLifecycle;
use Domain\Shared\Traits\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDocument extends Model
{
    use HasUlids;

    protected $fillable = ['organization_id', 'property_id', 'category', 'lifecycle_status', 'disk', 'storage_key', 'original_name', 'mime_type', 'size_bytes', 'checksum', 'metadata', 'archived_at'];

    protected function casts(): array
    {
        return ['category' => PropertyDocumentCategory::class, 'lifecycle_status' => PropertyDocumentLifecycle::class, 'metadata' => 'array', 'archived_at' => 'immutable_datetime'];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
