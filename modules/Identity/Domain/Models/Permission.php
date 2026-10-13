<?php

namespace Modules\Identity\Domain\Models;

use Modules\Identity\Domain\Contracts\Permission as PermissionContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model implements PermissionContract
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'module',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}
