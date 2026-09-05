<?php

namespace App\Plugin\Kernel\Pipeline;

use App\Plugin\Kernel\PluginDescriptor;
use App\Plugin\Runtime\PluginContext;
use App\Plugin\Runtime\RuntimeContext;

class ContextCreationStage implements PipelineStageInterface
{
    protected $runtimeContext;

    public function __construct(RuntimeContext $runtimeContext)
    {
        $this->runtimeContext = $runtimeContext;
    }

    public function execute(PluginDescriptor $descriptor, ?PluginContext $context = null): ?PluginContext
    {
        return new PluginContext($descriptor, $this->runtimeContext);
    }
}
