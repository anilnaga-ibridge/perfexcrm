<?php

namespace App\Plugin\Kernel\Pipeline;

use App\Plugin\Kernel\PluginDescriptor;
use App\Plugin\Kernel\DependencyResolver;
use App\Plugin\Runtime\PluginContext;

class ResolveDependenciesStage implements PipelineStageInterface
{
    protected $resolver;

    public function __construct(DependencyResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function execute(PluginDescriptor $descriptor, ?PluginContext $context = null): ?PluginContext
    {
        // Manifest dependencies validation is performed globally by RuntimeKernel before executing pipeline
        return $context;
    }
}
