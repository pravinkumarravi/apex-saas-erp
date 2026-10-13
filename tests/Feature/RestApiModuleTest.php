<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Organization\Models\Tenant;
use Modules\Identity\Models\User;
use App\Services\TenantContext;
use App\Services\ModuleManager;
use Modules\RestApi\Models\ApiKey;
use Modules\RestApi\Models\WebhookSubscription;

class RestApiModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $tenant = Tenant::where('subdomain', 'companya')->first();
        if ($tenant) {
            TenantContext::setTenant($tenant);
            $user = User::where('tenant_id', $tenant->id)->first();
            if ($user) {
                $this->actingAs($user);
            }
        }
    }

    public function test_rest_api_module_discovery()
    {
        /** @var ModuleManager $moduleManager */
        $moduleManager = app(ModuleManager::class);
        $modules = $moduleManager->discoverModules();

        $this->assertArrayHasKey('rest-api', $modules);
        $this->assertEquals('REST API & Webhooks', $modules['rest-api']['name']);
        $this->assertTrue($modules['rest-api']['is_active']);
    }

    public function test_api_key_generation_and_authentication()
    {
        $result = ApiKey::createKey('Test Mobile Integration', ['sales:read']);
        $this->assertNotNull($result['api_key']);
        $this->assertStringStartsWith('apk_live_', $result['plain_text_token']);

        $token = $result['plain_text_token'];

        // 1. Unauthenticated request without token -> 401
        $resNoAuth = $this->getJson('/api/v1/resources?tenant=companya');
        $resNoAuth->assertStatus(401);

        // 2. Request with invalid token -> 403
        $resBadAuth = $this->withHeader('X-API-KEY', 'apk_invalid_random_string')
            ->getJson('/api/v1/resources?tenant=companya');
        $resBadAuth->assertStatus(403);

        // 3. Request with valid token -> 200
        $resGoodAuth = $this->withHeader('X-API-KEY', $token)
            ->getJson('/api/v1/resources?tenant=companya');
        $resGoodAuth->assertStatus(200);
        $resGoodAuth->assertJsonPath('success', true);
    }

    public function test_public_health_endpoint()
    {
        $response = $this->getJson('/api/v1/health?tenant=companya');
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'healthy');
        $response->assertJsonPath('version', '1.0.0');
    }

    public function test_admin_web_routes_render_views()
    {
        $this->get('/developer/api/dashboard?tenant=companya')->assertStatus(200);
        $this->get('/developer/api/keys?tenant=companya')->assertStatus(200);
        $this->get('/developer/api/webhooks?tenant=companya')->assertStatus(200);
        $this->get('/developer/api/docs?tenant=companya')->assertStatus(200);
    }

    public function test_webhook_subscription_lifecycle()
    {
        $subscription = WebhookSubscription::create([
            'name' => 'Slack Order Alerts',
            'target_url' => 'https://hooks.slack.com/services/test/sample',
            'events' => ['after_sales_order_created'],
        ]);

        $this->assertNotNull($subscription->id);
        $this->assertTrue($subscription->listensTo('after_sales_order_created'));
        $this->assertFalse($subscription->listensTo('user_logged_in'));

        // Query active for event
        $activeSubs = WebhookSubscription::where('is_active', true)->get()->filter(fn($s) => $s->listensTo('after_sales_order_created'));
        $this->assertTrue($activeSubs->contains('id', $subscription->id));
    }
}
