<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Plugin\Kernel\PluginDescriptor;
use App\Plugin\Kernel\RuntimeKernel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PluginDoctorCommand extends Command
{
    protected $signature = 'plugin:doctor {alias?}';
    protected $description = 'Perform diagnostic health, signature, and dependency verification checks on installed modules';

    protected $kernel;

    public function __construct(RuntimeKernel $kernel)
    {
        parent::__construct();
        $this->kernel = $kernel;
    }

    public function handle()
    {
        $aliasFilter = $this->argument('alias');
        $this->info("==============================================");
        $this->info("         iBRIDGE PLUGIN DIAGNOSTIC DOCTOR     ");
        $this->info("==============================================");

        $activeModules = Module::where('status', 'active');
        if ($aliasFilter) {
            $activeModules->where('alias', $aliasFilter);
        }
        $modules = $activeModules->get();

        if ($modules->isEmpty()) {
            $this->warn('No active modules found to check.');
            return 0;
        }

        $totalErrors = 0;
        $totalWarnings = 0;

        foreach ($modules as $module) {
            $alias = $module->alias;
            $this->line("\nChecking module: [{$module->name}] ({$alias})");
            $this->line("----------------------------------------------");

            $moduleErrors = 0;
            $moduleWarnings = 0;

            // 1. Check folder existence
            $modulePath = base_path("Modules/{$alias}");
            if (!File::isDirectory($modulePath)) {
                $this->error("  ✖ Codebase folder does not exist at [Modules/{$alias}]");
                $moduleErrors++;
                $totalErrors++;
                continue;
            }

            // 2. Manifest check
            $manifestPath = "{$modulePath}/manifest.json";
            if (!File::exists($manifestPath)) {
                $manifestPath = "{$modulePath}/module.json";
            }

            if (!File::exists($manifestPath)) {
                $this->error("  ✖ Manifest file (manifest.json or module.json) is missing.");
                $moduleErrors++;
            } else {
                $this->line("  ✔ Manifest file detected.");
                
                try {
                    $manifest = json_decode(File::get($manifestPath), true);
                    if (!$manifest) {
                        $this->error("  ✖ Manifest could not be parsed as valid JSON.");
                        $moduleErrors++;
                    } else {
                        $this->line("  ✔ Manifest JSON structure is valid.");
                    }
                } catch (\Throwable $e) {
                    $this->error("  ✖ Manifest read error: " . $e->getMessage());
                    $moduleErrors++;
                }
            }

            // 3. Digital Signature check
            $sigPath = "{$modulePath}/signature.pem";
            if (!File::exists($sigPath)) {
                $this->warn("  ⚠ Digital signature (signature.pem) is missing. Running in unsigned developer mode.");
                $moduleWarnings++;
            } else {
                $this->line("  ✔ Digital signature file detected.");
            }

            // 4. Descriptor & Context validation
            $descriptor = $this->kernel->getDescriptor($alias);
            $context = $this->kernel->getContext($alias);

            if (!$descriptor) {
                $this->error("  ✖ Kernel failed to build PluginDescriptor. Plugin is skipped in runtime.");
                $moduleErrors++;
            } else {
                $this->line("  ✔ PluginDescriptor successfully mapped.");
                
                // Validate SDK and API version declarations
                $this->line("    - SDK Version: {$descriptor->sdkVersion}");
                $this->line("    - API Version: {$descriptor->apiVersion}");
            }

            if (!$context) {
                $this->error("  ✖ Kernel failed to build isolated PluginContext.");
                $moduleErrors++;
            } else {
                $this->line("  ✔ PluginContext build successful.");
                
                // Write access check
                try {
                    $storage = $context->storage();
                    $storage->put('.doctor_test', '1');
                    $storage->delete('.doctor_test');
                    $this->line("  ✔ Sandboxed storage directory is writable.");
                } catch (\Throwable $e) {
                    $this->error("  ✖ Sandboxed storage directory write test failed: " . $e->getMessage());
                    $moduleErrors++;
                }
            }

            // 5. Database Table Migration Status
            $tableName = str_replace('-', '_', $alias) . 's';
            if (Schema::hasTable($tableName)) {
                $this->line("  ✔ Custom database table [{$tableName}] exists.");
            } else {
                // If it is test-auto-module we generated, table is test_auto_modules (plural translation)
                $altName = str_replace('-', '_', $alias);
                if (Schema::hasTable($altName)) {
                    $this->line("  ✔ Custom database table [{$altName}] exists.");
                } else {
                    $this->warn("  ⚠ Custom database table for plugin was not detected. Ensure migrations have run.");
                    $moduleWarnings++;
                }
            }

            // Print summary for this module
            if ($moduleErrors === 0 && $moduleWarnings === 0) {
                $this->info("  STATUS: HEALTHY");
            } elseif ($moduleErrors === 0) {
                $this->comment("  STATUS: HEALTHY WITH WARNINGS ({$moduleWarnings} warnings)");
            } else {
                $this->error("  STATUS: UNHEALTHY ({$moduleErrors} errors, {$moduleWarnings} warnings)");
            }

            $totalErrors += $moduleErrors;
            $totalWarnings += $moduleWarnings;
        }

        $this->line("\n==============================================");
        if ($totalErrors === 0) {
            $this->info("DIAGNOSTICS COMPLETED: SUCCESS (0 errors, {$totalWarnings} warnings)");
        } else {
            $this->error("DIAGNOSTICS COMPLETED: FAILED ({$totalErrors} errors, {$totalWarnings} warnings)");
        }
        $this->line("==============================================");

        return $totalErrors === 0 ? 0 : 1;
    }
}
