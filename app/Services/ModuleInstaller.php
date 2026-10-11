<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantModule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ModuleInstaller
{
    protected ModuleManager $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Install a module from an uploaded ZIP file or absolute file path.
     */
    public function installFromZip(string|UploadedFile $zipSource): array
    {
        $zipPath = $zipSource instanceof UploadedFile ? $zipSource->getRealPath() : $zipSource;

        if (!File::exists($zipPath)) {
            return ['success' => false, 'message' => "ZIP archive file not found: {$zipPath}"];
        }

        $zip = new ZipArchive();
        $openResult = $zip->open($zipPath);

        if ($openResult !== true) {
            return ['success' => false, 'message' => "Failed to open ZIP archive (Error Code: {$openResult})."];
        }

        // Temporary extraction directory
        $tempDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp_module_' . uniqid());
        File::makeDirectory($tempDir, 0755, true);

        $zip->extractTo($tempDir);
        $zip->close();

        // Locate module.json (might be at root or inside a root subfolder)
        $manifestPath = $tempDir . DIRECTORY_SEPARATOR . 'module.json';
        $moduleSourceDir = $tempDir;

        if (!File::exists($manifestPath)) {
            // Check first sub-directory
            $subDirs = File::directories($tempDir);
            if (!empty($subDirs)) {
                $subManifest = $subDirs[0] . DIRECTORY_SEPARATOR . 'module.json';
                if (File::exists($subManifest)) {
                    $manifestPath = $subManifest;
                    $moduleSourceDir = $subDirs[0];
                }
            }
        }

        if (!File::exists($manifestPath)) {
            File::deleteDirectory($tempDir);
            return [
                'success' => false,
                'message' => 'Invalid module archive: module.json manifest is missing.'
            ];
        }

        $manifest = json_decode(File::get($manifestPath), true);
        if (!$manifest || empty($manifest['slug'])) {
            File::deleteDirectory($tempDir);
            return [
                'success' => false,
                'message' => 'Malformed module.json: missing required "slug" field.'
            ];
        }

        $slug = $manifest['slug'];
        $targetDir = $this->moduleManager->getModulesPath() . DIRECTORY_SEPARATOR . $slug;

        // Clean existing version if present
        if (File::isDirectory($targetDir)) {
            File::deleteDirectory($targetDir);
        }

        // Move to modules directory
        File::copyDirectory($moduleSourceDir, $targetDir);
        File::deleteDirectory($tempDir);

        // Run migrations if present
        $migrationsPath = $targetDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
        if (File::isDirectory($migrationsPath)) {
            $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $migrationsPath);
            Artisan::call('migrate', [
                '--path' => $relativePath,
                '--force' => true,
            ]);
        }

        // Register for all tenants or current tenant
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            TenantModule::withoutGlobalScopes()->updateOrCreate([
                'tenant_id' => $tenant->id,
                'module_slug' => $slug,
            ], [
                'is_enabled' => true,
                'version' => $manifest['version'] ?? '1.0.0',
            ]);
        }

        // Refresh discovered modules
        $this->moduleManager->discoverModules();

        return [
            'success' => true,
            'message' => "Module '{$manifest['name']}' ({$slug}) installed and activated successfully!",
            'module' => $manifest,
        ];
    }

    /**
     * Package an existing module directory into a distributable .zip archive.
     */
    public function packageModule(string $slug, string $destinationZipPath): bool
    {
        $moduleDir = $this->moduleManager->getModulesPath() . DIRECTORY_SEPARATOR . $slug;
        if (!File::isDirectory($moduleDir)) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($destinationZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($moduleDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($moduleDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
        return true;
    }

    /**
     * Uninstall a module.
     */
    public function uninstallModule(string $slug): bool
    {
        $targetDir = $this->moduleManager->getModulesPath() . DIRECTORY_SEPARATOR . $slug;
        if (File::isDirectory($targetDir)) {
            File::deleteDirectory($targetDir);
        }

        TenantModule::where('module_slug', $slug)->delete();
        $this->moduleManager->discoverModules();

        return true;
    }
}
