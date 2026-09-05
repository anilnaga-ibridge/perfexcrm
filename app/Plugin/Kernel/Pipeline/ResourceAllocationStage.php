<?php

namespace App\Plugin\Kernel\Pipeline;

use App\Plugin\Kernel\PluginDescriptor;
use App\Plugin\Runtime\PluginContext;

class ResourceAllocationStage implements PipelineStageInterface
{
    public function execute(PluginDescriptor $descriptor, ?PluginContext $context = null): ?PluginContext
    {
        if (!$context) {
            return null;
        }

        // Pre-warm storage folder and logs path
        $storage = $context->storage();
        $logger = $context->logger();

        return $context;
    }
}
