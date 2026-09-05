<?php

namespace App\Plugin\Kernel\Pipeline;

use App\Plugin\Kernel\PluginDescriptor;
use App\Plugin\Security\SignatureVerifier;
use App\Plugin\Runtime\PluginContext;
use Illuminate\Support\Facades\Log;

class VerifySignatureStage implements PipelineStageInterface
{
    protected $signatureVerifier;

    public function __construct(SignatureVerifier $signatureVerifier)
    {
        $this->signatureVerifier = $signatureVerifier;
    }

    public function execute(PluginDescriptor $descriptor, ?PluginContext $context = null): ?PluginContext
    {
        $alias = $descriptor->alias;
        $modulePath = base_path("Modules/{$alias}");

        // At boot time we only have the extracted filesystem, not the original ZIP.
        // Use the filesystem-based SignatureVerifier to check installed integrity.
        try {
            // SignatureVerifier::verify() checks for signature.json + checksums.json on disk.
            // If neither exists (common for unsigned/dev modules), it returns true immediately.
            $attrs = $descriptor->toArray();
            $attrs['path'] = $modulePath;
            $pluginInstance = new \App\Plugin\PluginInstance($attrs);
            $this->signatureVerifier->verify($pluginInstance);
        } catch (\Throwable $e) {
            Log::warning("RuntimeKernel: Signature verification failed for [{$alias}]: " . $e->getMessage());
        }

        return $context;
    }
}
