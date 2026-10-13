<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Organization\Models\Tenant;
use Modules\Identity\Models\User;
use App\Services\TenantContext;
use App\Services\HookManager;
use App\Services\MenuManager;
use App\Helpers\MenuHelper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class ModularHookAndMenuTest extends TestCase
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

    public function test_action_hooks()
    {
        $executed = [];

        add_action('custom_order_placed', function ($orderId) use (&$executed) {
            $executed[] = "Action 1: order {$orderId}";
        }, 20);

        add_action('custom_order_placed', function ($orderId) use (&$executed) {
            $executed[] = "Action 0 (High Priority): order {$orderId}";
        }, 5);

        $this->assertTrue(has_action('custom_order_placed'));

        do_action('custom_order_placed', 101);

        $this->assertCount(2, $executed);
        $this->assertEquals("Action 0 (High Priority): order 101", $executed[0]);
        $this->assertEquals("Action 1: order 101", $executed[1]);
    }

    public function test_filter_hooks()
    {
        add_filter('format_invoice_title', function ($title, $prefix = 'TAX') {
            return "[{$prefix}] {$title}";
        }, 10);

        add_filter('format_invoice_title', function ($title) {
            return strtoupper($title);
        }, 20);

        $this->assertTrue(has_filter('format_invoice_title'));

        $result = apply_filters('format_invoice_title', 'invoice 2026', 'GST');
        $this->assertEquals('[GST] INVOICE 2026', $result);
    }

    public function test_module_activation_and_deactivation_hooks()
    {
        $activated = false;
        $deactivated = false;

        register_activation_hook('test_module', function () use (&$activated) {
            $activated = true;
        });

        register_deactivation_hook('test_module', function () use (&$deactivated) {
            $deactivated = true;
        });

        hooks()->runActivationHook('test_module');
        $this->assertTrue($activated);

        hooks()->runDeactivationHook('test_module');
        $this->assertTrue($deactivated);
    }

    public function test_menu_registration_and_sidebar_filter()
    {
        /** @var MenuManager $menuManager */
        $menuManager = app(MenuManager::class);

        // Register custom module item
        $menuManager->addSidebarMenuItem('custom-tool', [
            'name' => 'Custom Analytics Tool',
            'icon' => 'dashboard',
            'path' => '/custom/analytics',
        ]);

        // Add subitem under CRM & Sales
        $menuManager->addSidebarChildItem('CRM & Sales', [
            'name' => 'Sales Commission Tracker',
            'path' => '/sales/commissions',
        ]);

        // Filter sidebar menu using hook
        add_filter('sidebar_menu', function ($groups) {
            $groups[] = [
                'title' => 'Custom Hooked Group',
                'items' => [
                    ['name' => 'Hooked Link', 'path' => '/hooked', 'icon' => 'settings']
                ]
            ];
            return $groups;
        });

        $menuGroups = MenuHelper::getMenuGroups();

        $this->assertNotEmpty($menuGroups);

        // Verify the hooked group was added via filter
        $groupTitles = array_column($menuGroups, 'title');
        $this->assertContains('Custom Hooked Group', $groupTitles);

        // Verify custom item was injected
        $foundCustom = false;
        foreach ($menuGroups as $group) {
            foreach ($group['items'] as $item) {
                if ($item['name'] === 'Custom Analytics Tool') {
                    $foundCustom = true;
                }
            }
        }
        $this->assertTrue($foundCustom, 'Custom registered menu item must be present in sidebar groups');
    }

    public function test_sales_module_views_load_from_module_directory()
    {
        // Assert module views exist in namespace
        $this->assertTrue(View::exists('sales::orders'));
        $this->assertTrue(View::exists('sales::delivery-challans'));
        $this->assertTrue(View::exists('sales::customers'));
        $this->assertTrue(View::exists('sales::leads'));

        // Render sales orders view via route
        $response = $this->get('/sales/orders?tenant=companya');
        $response->assertStatus(200);
        $response->assertSee('Customer Sales Orders');
        $response->assertSee('Create Sales Order');
    }

    public function test_blade_action_and_filter_directives()
    {
        $actionCalled = false;
        add_action('blade_test_action', function () use (&$actionCalled) {
            $actionCalled = true;
        });

        add_filter('blade_test_filter', function ($text) {
            return "MODIFIED: {$text}";
        });

        $renderedAction = Blade::render("@action('blade_test_action')");
        $this->assertTrue($actionCalled);

        $renderedFilter = Blade::render("@filter('blade_test_filter', 'Sample')");
        $this->assertEquals('MODIFIED: Sample', trim($renderedFilter));
    }

    public function test_audit_log_action_hook_decoupling()
    {
        $initialCount = \Modules\Governance\Models\AuditLog::count();

        // Trigger audit logging without importing AuditLog model
        do_action('audit_log', 'decoupled_test_event', 'SampleEntity', null, ['foo' => 'bar']);

        $this->assertEquals($initialCount + 1, \Modules\Governance\Models\AuditLog::count());

        $latestLog = \Modules\Governance\Models\AuditLog::latest('id')->first();
        $this->assertEquals('decoupled_test_event', $latestLog->action);
        $this->assertEquals('SampleEntity', $latestLog->auditable_type);
        $this->assertEquals(['foo' => 'bar'], $latestLog->new_values);
    }

    public function test_cross_module_data_filters()
    {
        $customers = apply_filters('accounting_customer_options', collect());
        $this->assertNotEmpty($customers);

        $products = apply_filters('accounting_product_options', collect());
        $this->assertNotEmpty($products);
    }
}

