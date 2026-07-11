<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\Rules\ManifestValidationRule;
use App\Services\Validation\Rules\FolderValidationRule;
use App\Services\Validation\Rules\MenuValidationRule;
use App\Services\Validation\Rules\PermissionValidationRule;
use App\Services\Validation\Rules\SettingsValidationRule;
use App\Services\Validation\Rules\RouteValidationRule;
use App\Services\Validation\Rules\ControllerValidationRule;
use App\Services\Validation\Rules\VueValidationRule;
use App\Services\Validation\Rules\DatabaseValidationRule;
use App\Services\Validation\Rules\BuildArtifactsValidationRule;
use App\Services\Validation\Rules\DocsValidationRule;
use App\Services\Validation\Rules\ComposerValidationRule;
use App\Services\Validation\Rules\TestValidationRule;
use Illuminate\Support\Facades\File;

class ValidateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'module:validate 
                            {module : The alias or directory path of the plugin} 
                            {--strict : Fail validation on warnings, undocumented elements, or missing tests} 
                            {--json : Output validation results as raw JSON}';

    protected $aliases = ['plugin:validate'];

    /**
     * The console command description.
     */
    protected $description = 'Run SDK compliance and quality checks on a module.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $target = $this->argument('module');
        $isJson = $this->option('json');
        $isStrict = $this->option('strict');

        // Resolve absolute path and alias
        $modulePath = null;
        $alias = null;

        if (File::isDirectory($target)) {
            $modulePath = realpath($target);
            $alias = basename($modulePath);
        } else {
            // Assume alias inside Modules directory
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

        // Initialize context and rules list
        $context = new ModuleContext($modulePath, $alias);

        $rules = [
            new ManifestValidationRule(),
            new FolderValidationRule(),
            new MenuValidationRule(),
            new PermissionValidationRule(),
            new SettingsValidationRule(),
            new RouteValidationRule(),
            new ControllerValidationRule(),
            new VueValidationRule(),
            new DatabaseValidationRule(),
            new BuildArtifactsValidationRule(),
            new DocsValidationRule(),
            new ComposerValidationRule(),
            new TestValidationRule(),
        ];

        $results = [];
        $totalErrors = 0;
        $totalWarnings = 0;
        $totalFatals = 0;

        $totalWeight = 0;
        $deductedWeight = 0;

        foreach ($rules as $rule) {
            $totalWeight += $rule->weight();
            try {
                $res = $rule->validate($context);
                $results[] = [
                    'rule' => $rule->name(),
                    'weight' => $rule->weight(),
                    'result' => $res
                ];

                foreach ($res->getLogs() as $log) {
                    if ($log['severity'] === 'ERROR') {
                        $totalErrors++;
                    } elseif ($log['severity'] === 'WARNING') {
                        $totalWarnings++;
                    } elseif ($log['severity'] === 'FATAL') {
                        $totalFatals++;
                    }
                }

                if (!$res->passed()) {
                    $deductedWeight += $rule->weight();
                }
            } catch (\Exception $e) {
                $deductedWeight += $rule->weight();
                $totalFatals++;
                $this->error("Rule '{$rule->name()}' crashed: " . $e->getMessage());
            }
        }

        // Calculate weighted score
        $score = 100;
        if ($totalWeight > 0) {
            $score = max(0, 100 - (int)round(($deductedWeight / $totalWeight) * 100));
        }

        $manifest = $context->getManifest();
        $sdkVersion = $manifest['sdk_version'] ?? 'N/A';

        // 1. JSON OUTPUT MODE
        if ($isJson) {
            $jsonReport = [
                'module' => $alias,
                'sdk_version' => $sdkVersion,
                'score' => $score,
                'warnings' => $totalWarnings,
                'errors' => $totalErrors + $totalFatals,
                'strict_mode' => $isStrict,
                'checks' => array_map(function ($r) {
                    return [
                        'name' => $r['rule'],
                        'weight' => $r['weight'],
                        'passed' => $r['result']->passed(),
                        'logs' => $r['result']->getLogs(),
                    ];
                }, $results),
            ];

            echo json_encode($jsonReport, JSON_PRETTY_PRINT);

            // Exit status
            if ($totalErrors > 0 || $totalFatals > 0 || ($isStrict && $totalWarnings > 0)) {
                return 1;
            }
            return 0;
        }

        // 2. HUMAN-FRIENDLY TERMINAL MODE
        $this->info("\n=================================================");
        $this->info("Evaluating Module: {$alias} (SDK v{$sdkVersion})");
        $this->info("=================================================");

        foreach ($results as $item) {
            $ruleName = str_pad($item['rule'], 20, ' ');
            $res = $item['result'];

            if ($res->passed()) {
                if ($res->hasWarnings()) {
                    $this->line(" <fg=yellow>⚠</> {$ruleName} Validated with warnings");
                } else {
                    $this->line(" <fg=green>✔</> {$ruleName} Verified");
                }
            } else {
                $this->line(" <fg=red>✘</> {$ruleName} FAILED");
            }

            // Print details of warnings/errors
            foreach ($res->getLogs() as $log) {
                if ($log['severity'] === 'ERROR' || $log['severity'] === 'FATAL') {
                    $this->line("     <fg=red>• [{$log['severity']}]</> {$log['message']}");
                } elseif ($log['severity'] === 'WARNING') {
                    $this->line("     <fg=yellow>• [WARNING]</> {$log['message']}");
                }
            }
        }

        $this->info("-------------------------------------------------");
        $this->line("Score:      " . ($score >= 90 ? "<fg=green>{$score}%</>" : ($score >= 70 ? "<fg=yellow>{$score}%</>" : "<fg=red>{$score}%</>")));
        $this->line("Warnings:   " . ($totalWarnings > 0 ? "<fg=yellow>{$totalWarnings}</>" : "0"));
        $this->line("Errors:     " . ($totalErrors + $totalFatals > 0 ? "<fg=red>" . ($totalErrors + $totalFatals) . "</>" : "0"));
        $this->info("-------------------------------------------------");

        // Fail criteria
        if ($totalErrors > 0 || $totalFatals > 0) {
            $this->error("Validation FAILED: {$totalErrors} error(s) detected.");
            return 1;
        }

        if ($isStrict && $totalWarnings > 0) {
            $this->error("Validation FAILED (Strict Mode): {$totalWarnings} warning(s) detected.");
            return 1;
        }

        $this->info("Validation PASSED successfully!");
        return 0;
    }
}
