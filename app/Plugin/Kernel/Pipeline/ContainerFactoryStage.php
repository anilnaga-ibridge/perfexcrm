<?php

namespace App\Plugin\Kernel\Pipeline;

use App\Plugin\Kernel\PluginDescriptor;
use App\Plugin\Runtime\PluginContainer;
use App\Plugin\Runtime\PluginContext;

class ContainerFactoryStage implements PipelineStageInterface
{
    public function execute(PluginDescriptor $descriptor, ?PluginContext $context = null): ?PluginContext
    {
        if (!$context) {
            return null;
        }

        $container = new PluginContainer(app(), $context);
        
        // Bind the child container into itself
        $container->instance(PluginContainer::class, $container);
        $container->instance(PluginContext::class, $context);

        // Bind child container as a service locator in context
        $context->container = $container;

        return $context;
    }
}
