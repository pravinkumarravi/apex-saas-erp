<?php

namespace Modules\RestApi\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\RestApi\Models\ApiKey;
use Modules\RestApi\Models\WebhookSubscription;

class RestApiController extends Controller
{
    /**
     * Admin REST API Dashboard.
     */
    public function dashboard()
    {
        $keysCount = ApiKey::where('is_active', true)->count();
        $webhooksCount = WebhookSubscription::where('is_active', true)->count();
        $recentKeys = ApiKey::latest()->take(5)->get();
        $recentWebhooks = WebhookSubscription::latest()->take(5)->get();

        return view('rest-api::index', compact('keysCount', 'webhooksCount', 'recentKeys', 'recentWebhooks'));
    }

    /**
     * Interactive API Documentation.
     */
    public function docs()
    {
        $endpoints = [
            [
                'method' => 'GET',
                'uri' => '/api/v1/health',
                'description' => 'Verify API availability and tenant operational status.',
                'auth' => 'Public / Token',
            ],
            [
                'method' => 'GET',
                'uri' => '/api/v1/resources',
                'description' => 'List available ERP domain resources and capabilities.',
                'auth' => 'API Key Required',
            ],
            [
                'method' => 'GET',
                'uri' => '/api/v1/sales/orders',
                'description' => 'Fetch confirmed customer sales contracts and items.',
                'auth' => 'API Key (Scope: sales:read)',
            ],
            [
                'method' => 'GET',
                'uri' => '/api/v1/inventory/products',
                'description' => 'Query active product catalog, HSN codes, and stock.',
                'auth' => 'API Key (Scope: inventory:read)',
            ],
            [
                'method' => 'POST',
                'uri' => '/api/v1/crm/customers',
                'description' => 'Create or register a new client with GST and billing data.',
                'auth' => 'API Key (Scope: crm:write)',
            ],
        ];

        return view('rest-api::docs', compact('endpoints'));
    }

    /**
     * API Health Check Endpoint.
     */
    public function health(Request $request): JsonResponse
    {
        $tenant = TenantContext::getTenant();

        return response()->json([
            'status' => 'healthy',
            'version' => '1.0.0',
            'system' => 'ApexERP Modular API Gateway',
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
            ] : 'Central',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Query ERP resources via API.
     */
    public function resources(Request $request): JsonResponse
    {
        $resources = [
            'sales_orders' => url('/api/v1/sales/orders'),
            'products' => url('/api/v1/inventory/products'),
            'customers' => url('/api/v1/crm/customers'),
            'webhooks' => url('/api/v1/webhooks'),
        ];

        $resources = apply_filters('rest_api_resources', $resources);

        return response()->json([
            'success' => true,
            'resources' => $resources,
        ]);
    }
}
