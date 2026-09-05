<?php

namespace App\Console\Commands;

use App\Services\LegacyModuleMigrationService;
use Illuminate\Console\Command;

class MigrateLegacyModule extends Command
{
    protected $signature = 'module:migrate-legacy
        {path : Path to the legacy module directory}
        {--output= : Output directory for migrated files (defaults to migrated-modules/<name>)}';

    protected $aliases = ['plugin:migrate-legacy'];

    protected $description = 'Analyse and migrate a legacy CodeIgniter iBridge module to the new platform format';

    public function handle(LegacyModuleMigrationService $migrator): int
    {
        $modulePath = $this->argument('path');

        if (!is_dir($modulePath)) {
            $this->error("Directory not found: {$modulePath}");
            return self::FAILURE;
        }

        $outputPath = $this->option('output');
        if (!$outputPath) {
            $moduleName = basename($modulePath);
            $outputPath = base_path("migrated-modules/{$moduleName}");
        }

        $this->info("Analyzing legacy module: {$modulePath}");
        $this->line("Output directory: {$outputPath}");
        $this->newLine();

        try {
            $analysis = $migrator->migrate($modulePath, $outputPath);
        } catch (\Exception $e) {
            $this->error("Migration failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        $meta = $analysis['metadata'];
        $this->info("✅ Module: {$meta['name']} v{$meta['version']}");

        // Summary table
        $this->newLine();
        $this->line('Migration Summary:');
        $this->newLine();

        $permCount = count($analysis['permissions']);
        $menuCount = count($analysis['menus']['children']);
        $ctrlCount = count($analysis['controllers']);
        $viewCount = count($analysis['views']);
        $helperCount = count($analysis['helpers']);
        $modelCount = count($analysis['models']);
        $libCount = count($analysis['libraries']);
        $hookCount = count($analysis['hooks']);
        $unsupportedCount = count($analysis['unsupported']);

        $this->table(
            ['Category', 'Count', 'Status'],
            [
                ['Permissions',         $permCount,        $permCount > 0 ? '✅ Auto-migrated' : 'N/A'],
                ['Menu Items',          $menuCount,        $menuCount > 0 ? '✅ Auto-migrated' : 'N/A'],
                ['Controllers',         $ctrlCount,        '📝 Skeleton generated'],
                ['Vue Pages',           $ctrlCount > 0 ? array_sum(array_map(fn($c) => count($c['methods']), $analysis['controllers'])) : 0, '📝 Skeleton generated'],
                ['Views',               $viewCount,        '📝 Manual migration'],
                ['Helpers',             $helperCount,      '📝 Manual migration'],
                ['Models',              $modelCount,       '📝 Manual migration'],
                ['Libraries',           $libCount,         '📝 Manual migration'],
                ['Hooks',               $hookCount,        '📝 Manual migration'],
                ['Unsupported APIs',    $unsupportedCount, '⚠️  Review required'],
            ]
        );

        $this->newLine();
        $this->line("Output written to: {$outputPath}");
        $this->line("Report: {$outputPath}/migration-report.md");
        $this->newLine();

        $this->warn('⚠️  This migration generates structural files only.');
        $this->warn('   Controller logic, views, and database queries require manual migration.');
        $this->warn('   Follow the steps in migration-report.md for each component.');

        return self::SUCCESS;
    }
}
