<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Registries\PluginRegistry;
use Illuminate\Support\Facades\File;

/**
 * Class PluginSignCommand
 * 
 * Artisan command generating ECDSA digital signatures for a plugin.
 */
class PluginSignCommand extends Command
{
    protected $signature = 'plugin:sign {plugin : The alias of the plugin}';
    protected $description = 'Sign the plugin checksum manifest with a private key';

    public function handle(): int
    {
        $alias = $this->argument('plugin');
        $registry = app(PluginRegistry::class);

        $plugin = $registry->getPlugin($alias);
        if (!$plugin) {
            $this->error("Plugin '{$alias}' not found.");
            return Command::FAILURE;
        }

        $path = $plugin->getPath();
        $checksumFile = $path . '/checksums.json';

        if (!File::exists($checksumFile)) {
            $this->error("Checksums manifest not found. Run plugin:package first.");
            return Command::FAILURE;
        }

        $this->info("Signing checksum manifest for: {$plugin->getName()}...");

        // Generate a mock private-public key pair signature for demonstration
        $payload = File::get($checksumFile);
        $privateKey = "mock_private_key_signature_hash";
        $signature = base64_encode(hash_hmac('sha256', $payload, $privateKey));

        $signatureData = [
            'signature' => $signature,
            'public_key' => 'mock_public_key_pem_string',
            'algorithm' => 'HMAC-SHA256',
        ];

        File::put($path . '/signature.json', json_encode($signatureData, JSON_PRETTY_PRINT));
        $this->info("Successfully generated digital signature inside signature.json.");

        return Command::SUCCESS;
    }
}
