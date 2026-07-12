<?php

namespace Domain\Foundation\Models;

use Domain\Foundation\Enums\RoleScope;
use Domain\Foundation\Enums\RoleStatus;
use Domain\Shared\Traits\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'scope',
        'status',
        'is_system',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'scope' => RoleScope::class,
            'status' => RoleStatus::class,
            'is_system' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')->withTimestamps();
    }

    public function memberships(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganizationUser::class,
            'organization_user_roles',
            'role_id',
            'organization_user_id',
        )->withTimestamps();
    }
}
