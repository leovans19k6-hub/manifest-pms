<?php

namespace Domain\Reservation\Models;

use Database\Factories\ReservationFactory;
use Domain\Foundation\Models\Organization;
use Domain\Inventory\Models\Unit;
use Domain\Property\Models\Property;
use Domain\Reservation\Enums\ReservationSource;
use Domain\Reservation\Enums\ReservationStatus;
use Domain\Shared\Traits\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;
    use HasUlids;

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'source' => ReservationSource::class,

            'adults' => 'integer',
            'children' => 'integer',

            'check_in' => 'datetime',
            'check_out' => 'datetime',

            'metadata' => 'array',
        ];
    }

    protected $fillable = [
        'organization_id',
        'property_id',
        'unit_id',

        'code',

        'status',
        'source',

        'guest_name',
        'guest_phone',
        'guest_email',

        'adults',
        'children',

        'check_in',
        'check_out',

        'notes',

        'metadata',
    ];

    protected static function newFactory(): ReservationFactory
    {
        return ReservationFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
