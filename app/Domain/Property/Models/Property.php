<?php

namespace Domain\Property\Models;

use Database\Factories\PropertyFactory;
use Domain\Foundation\Models\Organization;
use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Domain\Shared\Traits\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'organization_id', 'code', 'name', 'slug', 'type', 'status',
        'timezone', 'currency', 'address', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => PropertyType::class,
            'status' => PropertyStatus::class,
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): PropertyFactory
    {
        return PropertyFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
