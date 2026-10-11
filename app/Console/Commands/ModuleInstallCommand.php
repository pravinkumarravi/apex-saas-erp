<?php

namespace App\Console\Commands;

use App\Services\ModuleInstaller;
use Illuminate\Console\Command;

class ModuleInstallCommand extends Command
{
    protected $signature = 'module:install {path : Absolute or relative path to the module ZIP archive}';
    protected $description = 'Install and activate an external ERP module from a ZIP package';

    public function handle(ModuleInstaller $installer): int
    {
        $path = $this->argument('path');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $this->info("Extracting and verifying module archive from: {$path}...");

        $result = $installer->installFromZip($path);

        if ($result['success']) {
            $this->info("✔ {$result['message']}");
            return self::SUCCESS;
        }

        $this->error("✖ Installation failed: {$result['message']}");
        return self::FAILURE;
    }
}
