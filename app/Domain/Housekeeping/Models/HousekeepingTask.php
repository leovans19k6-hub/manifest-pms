<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Models;

use Database\Factories\HousekeepingTaskFactory;
use Domain\Foundation\Models\Organization;
use Domain\Foundation\Models\User;
use Domain\Housekeeping\Enums\HousekeepingTaskStatus;
use Domain\Housekeeping\Enums\HousekeepingTaskType;
use Domain\Inventory\Models\Unit;
use Domain\Property\Models\Property;
use Domain\Reservation\Models\Reservation;
use Domain\Shared\Traits\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class HousekeepingTask extends Model
{
    use HasFactory;
    use HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'property_id',
        'unit_id',
        'reservation_id',
        'assigned_to',
        'status',
        'type',
        'priority',
        'scheduled_at',
        'started_at',
        'completed_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => HousekeepingTaskStatus::class,
            'type' => HousekeepingTaskType::class,

            'priority' => 'integer',

            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): HousekeepingTaskFactory
    {
        return HousekeepingTaskFactory::new();
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

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assign(string $assigneeId): void
    {
        if ($this->status === HousekeepingTaskStatus::Completed) {
            throw ValidationException::withMessages([
                'task' => 'Completed housekeeping tasks cannot be assigned.',
            ]);
        }

        if ($this->status === HousekeepingTaskStatus::InProgress) {
            throw ValidationException::withMessages([
                'task' => 'Housekeeping task already in progress.',
            ]);
        }

        $this->assigned_to = $assigneeId;

        if ($this->status === HousekeepingTaskStatus::Pending) {
            $this->status = HousekeepingTaskStatus::Assigned;
        }
    }

    public function start(): void
    {
        if ($this->status !== HousekeepingTaskStatus::Assigned) {
            throw ValidationException::withMessages([
                'task' => 'Only assigned housekeeping tasks can be started.',
            ]);
        }

        $this->status = HousekeepingTaskStatus::InProgress;
        $this->started_at = now();
    }

    public function complete(): void
    {
        if ($this->status !== HousekeepingTaskStatus::InProgress) {
            throw ValidationException::withMessages([
                'task' => 'Only in-progress housekeeping tasks can be completed.',
            ]);
        }

        $this->status = HousekeepingTaskStatus::Completed;
        $this->completed_at = now();
    }
}