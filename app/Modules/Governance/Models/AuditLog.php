<?php

namespace Modules\Governance\Models;

use Modules\Governance\Contracts\AuditLog as AuditLogContract;
use App\Services\TenantContext;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model implements AuditLogContract
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class);
    }

    public static function log(string $action, Model|string $model, ?array $oldValues = null, ?array $newValues = null): self
    {
        return static::create([
            'tenant_id' => TenantContext::getTenantId(),
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => is_string($model) ? $model : get_class($model),
            'auditable_id' => is_string($model) ? null : $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
