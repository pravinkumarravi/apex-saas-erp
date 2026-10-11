<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ModuleManager;
use App\Services\ModuleInstaller;
use App\Services\TenantContext;
use Modules\QualityAudit\Repositories\Contracts\QualityAuditRepositoryInterface;
use Illuminate\Support\Facades\File;

class ModularPluginSystemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $tenant = Tenant::where('subdomain', 'companya')->first();
        TenantContext::setTenant($tenant);
        $user = User::where('tenant_id', $tenant->id)->first();
        $this->actingAs($user);
    }

    public function test_module_discovery_and_metadata()
    {
        /** @var ModuleManager $moduleManager */
        $moduleManager = app(ModuleManager::class);
        $modules = $moduleManager->discoverModules();

        $this->assertArrayHasKey('quality-audit', $modules);
        $this->assertEquals('QualityAudit', $modules['quality-audit']['name']);
        $this->assertTrue($modules['quality-audit']['is_active']);

        // Check dynamic menu registration
        $menus = $moduleManager->getModuleMenuItems();
        $this->assertNotEmpty($menus);
        $this->assertEquals('quality-audit', $menus[0]['module_slug']);
    }

    public function test_module_repository_pattern_and_ddd()
    {
        $tenant = TenantContext::getTenant();

        /** @var QualityAuditRepositoryInterface $repository */
        $repository = app(QualityAuditRepositoryInterface::class);
        $this->assertNotNull($repository);

        $audit = $repository->create([
            'audit_number' => 'QA-TEST-' . uniqid(),
            'title' => 'ISO 9001 Calibration Verification Audit',
            'audit_type' => 'internal',
            'auditor_name' => 'Dr. Anil Mehta',
            'score_percentage' => 98.5,
            'status' => 'passed',
            'scheduled_date' => now()->format('Y-m-d'),
        ]);

        $this->assertNotNull($audit->id);
        $this->assertEquals($tenant->id, $audit->tenant_id, 'Tenancy must be automatically scoped on module models');

        // Test custom repository method
        $internalAudits = $repository->getByType('internal');
        $this->assertTrue($internalAudits->contains('id', $audit->id));
    }

    public function test_module_packaging_and_zip_installation()
    {
        /** @var ModuleInstaller $installer */
        $installer = app(ModuleInstaller::class);

        $distDir = storage_path('app/modules_dist');
        if (!File::isDirectory($distDir)) {
            File::makeDirectory($distDir, 0755, true);
        }

        $zipPath = $distDir . DIRECTORY_SEPARATOR . 'QualityAudit.zip';

        // 1. Package module into ZIP
        $packaged = $installer->packageModule('QualityAudit', $zipPath);
        $this->assertTrue($packaged, 'Module packaging into ZIP must succeed');
        $this->assertFileExists($zipPath);

        // 2. Install from the packaged ZIP
        $result = $installer->installFromZip($zipPath);
        $this->assertTrue($result['success']);
        $this->assertEquals('QualityAudit', $result['module']['name']);
    }

    public function test_module_web_route_and_livewire_view()
    {
        // Access module page
        $response = $this->get('/modules/quality-audit?tenant=companya');
        $response->assertStatus(200);
        $response->assertSee('Quality Audits & Compliance');
    }
}
