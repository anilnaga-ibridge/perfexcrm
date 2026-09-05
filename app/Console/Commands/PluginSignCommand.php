<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Security\CanonicalPayloadBuilder;

/**
 * Class PluginSignCommand
 * 
 * Artisan command generating digital signatures for a plugin.
 */
class PluginSignCommand extends Command
{
    protected $signature = 'plugin:sign {plugin : The alias of the plugin}';
    protected $description = 'Sign the plugin manifest and file checksums';

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
        $manifestPath = File::exists($path . '/module.json') ? $path . '/module.json' : $path . '/manifest.json';

        if (!File::exists($manifestPath)) {
            $this->error("Manifest file not found in plugin path.");
            return Command::FAILURE;
        }

        $this->info("Building canonical checksums and payload for: {$plugin->getName()}...");

        // 1. Build canonical file checksums (including module.json)
        $checksums = CanonicalPayloadBuilder::buildFileChecksums($path);
        $canonicalChecksumsStr = CanonicalPayloadBuilder::canonicalChecksums($checksums);
        File::put($path . '/checksums.json', $canonicalChecksumsStr);

        // 2. Build canonical manifest & signed payload
        $manifest = json_decode(File::get($manifestPath), true) ?? [];
        $canonicalManifestStr = CanonicalPayloadBuilder::canonicalModuleManifest($manifest);
        $signedPayload = CanonicalPayloadBuilder::buildSignedPayload($canonicalManifestStr, $canonicalChecksumsStr);

        // Temporary signature data placeholder until Sub-phase 2B RSA-PSS integration
        $signatureData = [
            'algorithm' => 'RSA-PSS-SHA256',
            'signed_payload_hash' => hash('sha256', $signedPayload),
            'signature' => base64_encode(hash_hmac('sha256', $signedPayload, 'mock_key')),
        ];

        File::put($path . '/signature.json', json_encode($signatureData, JSON_PRETTY_PRINT));
        $this->info("Successfully generated canonical checksums.json and signature payload hash.");

        return Command::SUCCESS;
    }
}
