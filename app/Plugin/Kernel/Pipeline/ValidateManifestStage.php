<?php

namespace App\Plugin\Kernel\Pipeline;

use App\Plugin\Kernel\PluginDescriptor;
use App\Plugin\Runtime\PluginContext;
use Illuminate\Support\Facades\File;

class ValidateManifestStage implements PipelineStageInterface
{
    public function execute(PluginDescriptor $descriptor, ?PluginContext $context = null): ?PluginContext
    {
        $alias = $descriptor->alias;
        $modulePath = base_path("Modules/{$alias}");
        
        $manifestPath = "{$modulePath}/manifest.json";
        if (!File::exists($manifestPath)) {
            $manifestPath = "{$modulePath}/module.json";
        }

        if (!File::exists($manifestPath)) {
            throw new \RuntimeException("Manifest file is missing for plugin [{$alias}].");
        }

        return $context;
    }
}
