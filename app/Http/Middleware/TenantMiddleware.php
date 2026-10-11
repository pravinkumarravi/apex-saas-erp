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
        $host = strtolower($request->getHost());
        $tenant = null;

        $centralDomain = config('app.central_domain', env('CENTRAL_DOMAIN', 'xyz.com'));
        $centralDomains = array_unique(array_filter([
            $centralDomain,
            'localhost',
            '127.0.0.1',
            'xyz.com',
        ]));

        $isCentralHost = in_array($host, $centralDomains)
            || in_array($host, ['www.' . $centralDomain, 'www.xyz.com', 'www.localhost']);

        // 1. Check Subdomain: e.g. companya.xyz.com, companyb.xyz.com, companya.localhost
        if (!$isCentralHost) {
            foreach ($centralDomains as $cd) {
                if ($cd && str_ends_with($host, '.' . $cd)) {
                    $subdomainCandidate = substr($host, 0, -strlen('.' . $cd));
                    $subdomainCandidate = explode('.', $subdomainCandidate)[0];
                    if ($subdomainCandidate && $subdomainCandidate !== 'www') {
                        $tenant = Tenant::where('subdomain', $subdomainCandidate)->first();
                        if ($tenant) {
                            break;
                        }
                    }
                }
            }

            // Fallback generic subdomain check: e.g. companya.anydomain.com
            if (!$tenant) {
                $parts = explode('.', $host);
                if (count($parts) > 2 && $parts[0] !== 'www') {
                    $tenant = Tenant::where('subdomain', $parts[0])->first();
                } elseif (count($parts) === 2 && $parts[1] === 'localhost') {
                    $tenant = Tenant::where('subdomain', $parts[0])->first();
                }
            }

            // 2. Custom Domain: e.g. erp.companya.com
            if (!$tenant && !in_array($host, ['localhost', '127.0.0.1'])) {
                $tenant = Tenant::where('domain', $host)->first();
            }
        }

        // 3. Request Header or Query Param (X-Tenant-Subdomain / ?tenant=companya)
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

        // 5. Default fallback to the first active tenant for smooth local evaluation on root domain
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
