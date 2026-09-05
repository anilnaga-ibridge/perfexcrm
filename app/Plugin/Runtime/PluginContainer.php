<?php

namespace App\Plugin\Runtime;

use Illuminate\Container\Container;

class PluginContainer extends Container
{
    protected Container $parentContainer;
    protected PluginContext $context;

    public function __construct(Container $parentContainer, PluginContext $context)
    {
        $this->parentContainer = $parentContainer;
        $this->context = $context;
    }

    /**
     * Resolve bindings from the child container, falling back to Laravel's parent container.
     */
    public function make($abstract, array $parameters = [])
    {
        if ($this->bound($abstract)) {
            return parent::make($abstract, $parameters);
        }

        return $this->parentContainer->make($abstract, $parameters);
    }
}
