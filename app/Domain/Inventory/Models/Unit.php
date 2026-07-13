<?php

namespace Domain\Inventory\Models;

use Database\Factories\UnitFactory;
use Domain\Foundation\Models\Organization;
use Domain\Inventory\Enums\UnitStatus;
use Domain\Inventory\Enums\UnitType;
use Domain\Property\Models\Property;
use Domain\Shared\Traits\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'property_id',
        'code',
        'name',
        'slug',
        'type',
        'status',
        'capacity_adults',
        'capacity_children',
        'bedrooms',
        'bathrooms',
        'base_occupancy',
        'max_occupancy',
        'sort_order',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => UnitType::class,
            'status' => UnitStatus::class,
            'capacity_adults' => 'integer',
            'capacity_children' => 'integer',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'base_occupancy' => 'integer',
            'max_occupancy' => 'integer',
            'sort_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): UnitFactory
    {
        return UnitFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
