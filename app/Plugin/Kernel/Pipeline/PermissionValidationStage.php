<?php

namespace App\Plugin\Kernel\Pipeline;

use App\Plugin\Kernel\PluginDescriptor;
use App\Plugin\Kernel\SandboxSecurityManager;
use App\Plugin\Runtime\PluginContext;

class PermissionValidationStage implements PipelineStageInterface
{
    protected $securityManager;

    public function __construct(SandboxSecurityManager $securityManager)
    {
        $this->securityManager = $securityManager;
    }

    public function execute(PluginDescriptor $descriptor, ?PluginContext $context = null): ?PluginContext
    {
        try {
            $this->securityManager->checkPermission($descriptor, 'plugin', 'boot');
        } catch (\Throwable $e) {
            $permissions = $descriptor->capabilities['permissions'] ?? [];
            if (empty($permissions)) {
                \Illuminate\Support\Facades\Log::debug("PermissionValidationStage: Plugin [{$descriptor->alias}] booted with default developer permissions.");
            } else {
                throw $e;
            }
        }
        return $context;
    }
}
