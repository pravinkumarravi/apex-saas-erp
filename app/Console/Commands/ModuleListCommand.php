<?php

namespace App\Console\Commands;

use App\Services\ModuleManager;
use Illuminate\Console\Command;

class ModuleListCommand extends Command
{
    protected $signature = 'module:list';
    protected $description = 'List all discovered and installed ERP modules & plugins';

    public function handle(ModuleManager $moduleManager): int
    {
        $modules = $moduleManager->getAllModules();

        if (empty($modules)) {
            $this->info('No modules installed in modules/ directory.');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($modules as $slug => $mod) {
            $rows[] = [
                $mod['name'] ?? $slug,
                $slug,
                $mod['version'] ?? '1.0.0',
                $mod['is_active'] ? '<fg=green>ACTIVE</>' : '<fg=yellow>DISABLED</>',
                $mod['description'] ?? '-',
                $mod['author'] ?? 'ApexERP',
            ];
        }

        $this->table(['Module Name', 'Slug', 'Version', 'Status', 'Description', 'Author'], $rows);

        return self::SUCCESS;
    }
}
