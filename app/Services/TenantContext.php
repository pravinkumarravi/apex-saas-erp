<?php

namespace App\Services;

use Modules\Organization\Models\Tenant;

class TenantContext
{
    protected static ?Tenant $currentTenant = null;

    public static function setTenant(?Tenant $tenant): void
    {
        static::$currentTenant = $tenant;
    }

    public static function getTenant(): ?Tenant
    {
        return static::$currentTenant;
    }

    public static function getTenantId(): ?int
    {
        return static::$currentTenant?->id;
    }

    public static function hasTenant(): bool
    {
        return static::$currentTenant !== null;
    }

    public static function getCurrency(): string
    {
        return static::$currentTenant?->currency ?? 'INR';
    }

    public static function getGstin(): ?string
    {
        return static::$currentTenant?->gstin;
    }

    public static function getStateCode(): string
    {
        return static::$currentTenant?->state_code ?? '27';
    }
}
