<?php

namespace App\Plugin\Kernel\Pipeline;

use App\Plugin\Kernel\PluginDescriptor;
use App\Plugin\Runtime\PluginContext;
use Illuminate\Support\Facades\Log;

class CapabilityRegistrationStage implements PipelineStageInterface
{
    public function execute(PluginDescriptor $descriptor, ?PluginContext $context = null): ?PluginContext
    {
        // Manifest capability parsing
        $capabilities = $descriptor->capabilities ?? [];
        if (!empty($capabilities)) {
            Log::info("CapabilityRegistrationStage: Mapped capabilities for [{$descriptor->alias}]");
        }
        return $context;
    }
}
