<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Health\HealthMonitor;
use App\Plugin\Registries\PluginRegistry;

/**
 * Class PluginDoctorCommand
 * 
 * Artisan CLI command performing runtime checks, permission diagnostics,
 * and database connection audits for all active plugins.
 */
class PluginDoctorCommand extends Command
{
    protected $signature = 'plugin:doctor';
    protected $description = 'Perform diagnostic health checks and audits on all registered plugins';

    public function handle(): int
    {
        $this->info("=================================================");
        $this->info("iBridge Plugin SDK - System Doctor Diagnostics");
        $this->info("=================================================");

        $registry = app(PluginRegistry::class);
        $healthMonitor = app(HealthMonitor::class);

        $plugins = $registry->getPlugins();
        if (empty($plugins)) {
            $this->comment("No plugins detected in the system.");
            return Command::SUCCESS;
        }

        $allHealthy = true;
        foreach ($plugins as $plugin) {
            $this->line("\nDiagnosing Plugin: {$plugin->getName()} ({$plugin->getAlias()})");
            $this->line(str_repeat('-', 50));

            $report = $healthMonitor->checkPlugin($plugin->getAlias());
            
            if ($report['status'] !== 'healthy') {
                $allHealthy = false;
            }

            foreach ($report['checks'] as $checkKey => $check) {
                $statusColor = $check['status'] === 'ok' ? 'info' : ($check['status'] === 'warning' ? 'comment' : 'error');
                $statusIcon = $check['status'] === 'ok' ? '✔' : ($check['status'] === 'warning' ? '⚠' : '✘');
                $this->$statusColor("  {$statusIcon} [" . ucfirst($checkKey) . "]: {$check['message']}");
            }
        }

        $this->line(str_repeat('=', 50));
        if ($allHealthy) {
            $this->info("Doctor Report: ALL PLUGINS ARE HEALTHY AND RUNNING CORRECTLY.");
        } else {
            $this->error("Doctor Report: COMPLIANCE ISSUES OR UNHEALTHY TRANSITIONS ENCOUNTERED.");
        }

        return Command::SUCCESS;
    }
}
