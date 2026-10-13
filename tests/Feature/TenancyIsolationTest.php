<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Organization\Models\Tenant;
use Modules\Identity\Models\User;
use Modules\Sales\Models\Customer;
use App\Services\TenantContext;

class TenancyIsolationTest extends TestCase
{
    public function test_tenant_context_and_query_isolation()
    {
        $tenantA = Tenant::where('subdomain', 'companya')->first();
        $tenantB = Tenant::where('subdomain', 'companyb')->first();

        $this->assertNotNull($tenantA, 'Tenant A should exist in seeded database');
        $this->assertNotNull($tenantB, 'Tenant B should exist in seeded database');

        // Set context to Tenant A
        TenantContext::setTenant($tenantA);
        $customersA = Customer::all();
        foreach ($customersA as $customer) {
            $this->assertEquals($tenantA->id, $customer->tenant_id, 'All customers retrieved must belong to Tenant A');
        }

        // Set context to Tenant B
        TenantContext::setTenant($tenantB);
        $customersB = Customer::all();
        foreach ($customersB as $customer) {
            $this->assertEquals($tenantB->id, $customer->tenant_id, 'All customers retrieved must belong to Tenant B');
        }
    }

    public function test_subdomain_and_query_param_middleware_resolution()
    {
        $user = User::first();
        $this->actingAs($user);

        // Access with ?tenant=companya
        $responseA = $this->get('/inventory/products?tenant=companya');
        $responseA->assertStatus(200);
        $this->assertEquals('companya', TenantContext::getTenant()?->subdomain);

        // Access with ?tenant=companyb
        $responseB = $this->get('/inventory/products?tenant=companyb');
        $responseB->assertStatus(200);
        $this->assertEquals('companyb', TenantContext::getTenant()?->subdomain);
    }

    public function test_subdomain_localhost_resolution()
    {
        $user = User::first();
        $this->actingAs($user);

        // Access via companya.localhost
        $responseA = $this->get('http://companya.localhost/inventory/products');
        $responseA->assertStatus(200);
        $this->assertEquals('companya', TenantContext::getTenant()?->subdomain);

        // Access via companyb.localhost
        $responseB = $this->get('http://companyb.localhost/inventory/products');
        $responseB->assertStatus(200);
        $this->assertEquals('companyb', TenantContext::getTenant()?->subdomain);
    }

    public function test_tenant_get_url_helper()
    {
        $tenantA = Tenant::where('subdomain', 'companya')->first();
        $this->assertNotNull($tenantA);

        $url = $tenantA->getUrl('inventory/products');
        $this->assertStringContainsString('companya.localhost', $url);
        $this->assertStringEndsWith('/inventory/products', $url);
    }
}


