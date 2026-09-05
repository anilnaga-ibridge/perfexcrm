<?php

namespace App\Plugin\Kernel\Pipeline;

use App\Plugin\Kernel\PluginDescriptor;
use App\Plugin\Kernel\ServiceRegistry;
use App\Plugin\Runtime\PluginContext;

class ServiceRegistrationStage implements PipelineStageInterface
{
    protected $serviceRegistry;

    public function __construct(ServiceRegistry $serviceRegistry)
    {
        $this->serviceRegistry = $serviceRegistry;
    }

    public function execute(PluginDescriptor $descriptor, ?PluginContext $context = null): ?PluginContext
    {
        if (!$context) {
            return null;
        }

        foreach ($descriptor->services as $contract => $implClass) {
            if (class_exists($implClass)) {
                // If the child container exists, resolve it through container, else instantiate
                $instance = $context->container ? $context->container->make($implClass) : new $implClass($context);
                $this->serviceRegistry->register($contract, $instance);
            }
        }

        return $context;
    }
}
