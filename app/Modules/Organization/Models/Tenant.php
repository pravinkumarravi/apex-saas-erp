<?php

namespace Modules\Organization\Models;

use Modules\Organization\Contracts\Tenant as TenantContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model implements TenantContract
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
        'domain',
        'plan_name',
        'status',
        'currency',
        'timezone',
        'gstin',
        'pan',
        'state_code',
        'address',
        'logo',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(\Modules\Identity\Models\User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(\Modules\Inventory\Models\Warehouse::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(\Modules\Inventory\Models\Product::class);
    }

    public function chartOfAccounts(): HasMany
    {
        return $this->hasMany(\Modules\Accounting\Models\ChartOfAccount::class);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Get the fully qualified URL for this tenant.
     * E.g. http://companya.xyz.com:8000 or https://companya.xyz.com
     */
    public function getUrl(string $path = '', bool $useCustomDomain = false): string
    {
        $baseDomain = config('app.central_domain', env('CENTRAL_DOMAIN', 'localhost'));
        $scheme = request()->getScheme() ?: 'http';
        $port = request()->getPort();
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ':' . $port : '';

        $host = ($useCustomDomain && $this->domain) ? $this->domain : ($this->subdomain . '.' . $baseDomain);
        $url = "{$scheme}://{$host}{$portSuffix}";

        return $path ? rtrim($url, '/') . '/' . ltrim($path, '/') : $url;
    }
}
