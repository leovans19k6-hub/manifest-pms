<?php

namespace Domain\Foundation\Models;

use Domain\Foundation\Enums\OrganizationMemberStatus;
use Domain\Shared\Traits\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationUser extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['organization_id', 'user_id', 'status', 'is_default', 'joined_at'];

    protected function casts(): array
    {
        return [
            'status' => OrganizationMemberStatus::class,
            'is_default' => 'boolean',
            'joined_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
