<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;

class ApiSanctumTest extends TestCase
{
    public function test_api_authentication_and_tenant_scoping()
    {
        $tenant = Tenant::where('subdomain', 'companya')->first();
        TenantContext::setTenant($tenant);
        $user = User::where('tenant_id', $tenant->id)->first();

        // 1. Generate Sanctum Bearer token via /api/auth/login
        $tokenResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Automated Mobile Test Device',
        ], [
            'X-Tenant-Subdomain' => 'companya'
        ]);

        $tokenResponse->assertStatus(200);
        $tokenData = $tokenResponse->json();
        $this->assertArrayHasKey('access_token', $tokenData);
        $token = $tokenData['access_token'];

        // 2. Query products endpoint with Bearer token
        $prodResponse = $this->getJson('/api/products', [
            'Authorization' => "Bearer {$token}",
            'X-Tenant-Subdomain' => 'companya'
        ]);

        $prodResponse->assertStatus(200);
        $this->assertNotEmpty($prodResponse->json('data'));

        // 3. Query sales orders endpoint with Bearer token
        $soResponse = $this->getJson('/api/sales/orders', [
            'Authorization' => "Bearer {$token}",
            'X-Tenant-Subdomain' => 'companya'
        ]);

        $soResponse->assertStatus(200);
        $this->assertNotEmpty($soResponse->json('data'));
    }
}
