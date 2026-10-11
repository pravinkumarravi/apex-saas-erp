<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $tenant = null;

        // 1. Check Subdomain: e.g. companya.localhost or companya.erp.com
        $parts = explode('.', $host);
        if (count($parts) > 1 && $parts[0] !== 'www' && $parts[0] !== 'localhost' && $parts[0] !== '127') {
            $subdomain = $parts[0];
            $tenant = Tenant::where('subdomain', $subdomain)->first();
        }

        // 2. Custom Domain: e.g. erp.companya.com
        if (!$tenant && !in_array($host, ['localhost', '127.0.0.1'])) {
            $tenant = Tenant::where('domain', $host)->first();
        }

        // 3. Request Header or Query Param (X-Tenant-ID / ?tenant=companya)
        if (!$tenant) {
            $identifier = $request->header('X-Tenant-Subdomain') ?? $request->query('tenant');
            if ($identifier) {
                $tenant = Tenant::where('subdomain', $identifier)->orWhere('id', $identifier)->first();
                if ($tenant) {
                    session(['active_tenant_id' => $tenant->id]);
                }
            }
        }

        // 4. Session or Authenticated User
        if (!$tenant) {
            if ($request->user() && $request->user()->tenant_id) {
                $tenant = Tenant::find($request->user()->tenant_id);
            } elseif (session()->has('active_tenant_id')) {
                $tenant = Tenant::find(session('active_tenant_id'));
            }
        }

        // 5. Default fallback to the first active tenant for smooth local evaluation
        if (!$tenant) {
            $tenant = Tenant::where('status', 'active')->first();
            if ($tenant) {
                session(['active_tenant_id' => $tenant->id]);
            }
        }

        if ($tenant) {
            if ($tenant->status === 'suspended') {
                abort(403, 'This organization account has been suspended. Please contact support.');
            }

            TenantContext::setTenant($tenant);
            app()->instance('current_tenant', $tenant);
            view()->share('currentTenant', $tenant);
        }

        return $next($request);
    }
}
