<?php

namespace Domain\Foundation\Models;

use Domain\Foundation\Enums\OrganizationStatus;
use Domain\Shared\Traits\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = ['code', 'name', 'slug', 'status', 'timezone', 'currency', 'locale'];

    protected function casts(): array
    {
        return ['status' => OrganizationStatus::class];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(OrganizationUser::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_users')
            ->withPivot(['id', 'status', 'is_default', 'joined_at'])
            ->withTimestamps();
    }
}
