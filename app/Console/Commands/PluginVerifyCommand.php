<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Registries\PluginRegistry;
use App\Plugin\Security\SignatureVerifier;

/**
 * Class PluginVerifyCommand
 * 
 * Artisan command auditing digital signatures and manifest checksums.
 */
class PluginVerifyCommand extends Command
{
    protected $signature = 'plugin:verify {plugin : The alias of the plugin}';
    protected $description = 'Verify the signature and integrity of a plugin package';

    public function handle(): int
    {
        $alias = $this->argument('plugin');
        $registry = app(PluginRegistry::class);
        $verifier = app(SignatureVerifier::class);

        $plugin = $registry->getPlugin($alias);
        if (!$plugin) {
            $this->error("Plugin '{$alias}' not found.");
            return Command::FAILURE;
        }

        $this->info("Verifying package signature for: {$plugin->getName()}...");

        try {
            if ($verifier->verify($plugin)) {
                $this->info("Success: Package integrity and digital signature are VALID.");
            } else {
                $this->error("Error: Digital signature verification FAILED.");
                return Command::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error("Verification failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
