<?php

namespace App\Plugin\Kernel\Pipeline;

use App\Plugin\Kernel\PluginDescriptor;
use App\Plugin\Runtime\PluginContext;

interface PipelineStageInterface
{
    /**
     * Execute this lifecycle stage.
     *
     * @param PluginDescriptor $descriptor
     * @param PluginContext|null $context
     * @return PluginContext|null Modified or new context
     */
    public function execute(PluginDescriptor $descriptor, ?PluginContext $context = null): ?PluginContext;
}
