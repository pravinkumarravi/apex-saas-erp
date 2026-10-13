<?php

namespace Modules\Organization\Models;

use Modules\Organization\Contracts\TenantModule as TenantModuleContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantModule extends Model implements TenantModuleContract
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'module_slug',
        'is_enabled',
        'version',
        'settings',
        'installed_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'settings' => 'array',
        'installed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
