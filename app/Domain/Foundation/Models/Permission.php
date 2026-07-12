<?php

namespace Domain\Foundation\Models;

use Domain\Foundation\Enums\PermissionGroup;
use Domain\Shared\Traits\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['code', 'name', 'group', 'description'];

    protected function casts(): array
    {
        return ['group' => PermissionGroup::class];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')->withTimestamps();
    }
}
