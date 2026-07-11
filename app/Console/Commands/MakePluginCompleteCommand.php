<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Validation\PluginCompletionEngine;
use App\Services\Validation\PluginScaffolder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakePluginCompleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'module:complete
                            {module : The alias or directory path of the plugin}
                            {--generate= : Scaffold missing components (crud|frontend|backend|tests|all)}
                            {--dry-run : Print proposed files and schema changes without writing them}
                            {--strict : Fail build (exit code 1) on any compliance warning or gap}
                            {--json : Output report in JSON format}';

    protected $aliases = ['plugin:complete'];

    /**
     * The console command description.
     */
    protected $description = 'Audit a module for structural completeness, calculate compliance, and scaffold missing files.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $target = $this->argument('module');
        $generateLevel = $this->option('generate');
        $isDryRun = $this->option('dry-run');
        $isStrict = $this->option('strict');
        $isJson = $this->option('json');

        // Resolve absolute path and alias
        $modulePath = null;
        $alias = null;

        if (File::isDirectory($target)) {
            $modulePath = realpath($target);
            $alias = basename($modulePath);
        } else {
            $possiblePath = base_path('Modules/' . $target);
            if (File::isDirectory($possiblePath)) {
                $modulePath = realpath($possiblePath);
                $alias = $target;
            }
        }

        if (!$modulePath) {
            if ($isJson) {
                echo json_encode([
                    'success' => false,
                    'error' => "Module directory not found for: '{$target}'",
                ], JSON_PRETTY_PRINT);
            } else {
                $this->error("Error: Module directory not found for: '{$target}'");
            }
            return 1;
        }

        // 1. Audit
        $engine = new PluginCompletionEngine($modulePath, $alias);
        $report = $engine->audit();

        // 2. Generate if requested
        $generatedFiles = [];
        if ($generateLevel) {
            $validLevels = ['crud', 'frontend', 'backend', 'tests', 'all'];
            if (!in_array($generateLevel, $validLevels)) {
                $this->error("Error: Invalid generate level '{$generateLevel}'. Valid options: " . implode(', ', $validLevels));
                return 1;
            }

            // Check dependency block before generation
            $hasMissingDeps = false;
            foreach ($report['dependencies'] as $dep => $depInfo) {
                if ($depInfo['status'] === 'missing') {
                    $hasMissingDeps = true;
                    if (!$isJson) {
                        $this->error("Error: Dependency '{$dep}' is missing or inactive. Scaffolding is aborted.");
                    }
                }
            }

            if (!$hasMissingDeps) {
                $scaffolder = new PluginScaffolder($modulePath, $alias, $isDryRun);
                $models = $engine->getModels();

                foreach ($models as $modelName => $modelInfo) {
                    $scaffolder->scaffold($modelName, $modelInfo, $generateLevel, $report['details'][$modelName]);
                }

                $generatedFiles = $scaffolder->getWrittenFiles();

                // Re-audit if we actually performed changes
                if (!$isDryRun && !empty($generatedFiles)) {
                    $report = $engine->audit();
                }
            }
        }

        // 3. Output results
        if ($isJson) {
            $report['generated_files'] = $generatedFiles;
            $report['dry_run'] = $isDryRun;
            $report['strict_mode'] = $isStrict;
            echo json_encode($report, JSON_PRETTY_PRINT);

            if ($report['missing_components'] > 0 && $isStrict) {
                return 1;
            }
            return 0;
        }

        // Human-friendly output
        $this->info("\n=================================================");
        $this->info("Evaluating Plugin: {$alias}");
        $this->info("=================================================");

        // Print dependencies
        if (!empty($report['dependencies'])) {
            $this->comment("Dependencies:");
            foreach ($report['dependencies'] as $dep => $depInfo) {
                $statusStr = $depInfo['status'] === 'active' ? "<fg=green>ACTIVE</>" : "<fg=red>MISSING</>";
                $this->line("  • {$dep}: {$statusStr}");
            }
            $this->info("-------------------------------------------------");
        }

        if (empty($report['details'])) {
            $this->comment("No models found inside the plugin. Build a model to trigger verification.");
            return 0;
        }

        // Draw Matrix
        foreach ($report['details'] as $modelName => $checks) {
            $this->comment("\nModel: {$modelName}");
            $this->line(str_repeat('-', 50));
            $this->line(sprintf("  %-25s | %-10s | %s", "Component", "Status", "Recommendation"));
            $this->line(str_repeat('-', 50));

            foreach ($checks as $compKey => $c) {
                $statusStr = "";
                if ($c['status'] === 'ok') {
                    $statusStr = "<fg=green>✔ VERIFIED</>";
                    $rec = "No action needed";
                } elseif ($c['status'] === 'warning') {
                    $statusStr = "<fg=yellow>⚠ WARNING</>";
                    $rec = $c['suggestion'];
                } else {
                    $statusStr = "<fg=red>✘ MISSING</>";
                    $rec = $c['suggestion'];
                }
                $this->line(sprintf("  %-25s | %-20s | %s", $c['label'], $statusStr, $rec));
            }
            $this->line(str_repeat('-', 50));
        }

        // Scaffolding Logs
        if (!empty($generatedFiles)) {
            $this->comment("\nScaffolding Operations (" . ($isDryRun ? "DRY RUN" : "WRITTEN") . "):");
            foreach ($generatedFiles as $gf) {
                $action = $isDryRun ? "Will create" : "Created";
                $this->line("  • [{$action}] Modules/{$alias}/{$gf}");
            }
            $this->info("-------------------------------------------------");
        }

        // Summary
        $score = $report['compliance_score'];
        $scoreStr = $score >= 90 ? "<fg=green>{$score}%</>" : ($score >= 70 ? "<fg=yellow>{$score}%</>" : "<fg=red>{$score}%</>");

        $this->line("\nPlugin Compliance Summary");
        $this->line("-------------------------------------------------");
        $this->line("Compliance Score:   {$scoreStr}");
        $this->line("Models Audited:     " . $report['models_count']);
        $this->line("Total Gaps:         " . ($report['missing_components'] > 0 ? "<fg=red>" . $report['missing_components'] . "</>" : "<fg=green>0</>"));
        $this->line("-------------------------------------------------");

        if ($report['missing_components'] > 0) {
            if ($isStrict) {
                $this->error("Validation failed (Strict Mode): {$report['missing_components']} component gaps detected.");
                return 1;
            } else {
                $this->comment("Run with --generate=all to automatically scaffold missing compliance targets.");
            }
        } else {
            $this->info("Success: Plugin is 100% compliant with standard architecture!");
        }

        return 0;
    }
}
