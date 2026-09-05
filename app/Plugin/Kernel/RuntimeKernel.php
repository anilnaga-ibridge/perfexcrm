<?php

namespace App\Plugin\Kernel;

use App\Models\Module;
use App\Plugin\Kernel\Pipeline\PipelineStageInterface;
use App\Plugin\Runtime\PluginContext;
use App\Plugin\Runtime\RuntimeContext;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class RuntimeKernel
{
    protected array $descriptors = [];
    protected array $contexts = [];
    protected array $bootedPlugins = [];

    protected $dependencyResolver;
    protected $securityManager;
    protected $serviceRegistry;
    protected $runtimeContext;
    protected array $pipeline = [];

    public function __construct(
        DependencyResolver $dependencyResolver,
        SandboxSecurityManager $securityManager,
        ServiceRegistry $serviceRegistry,
        \App\Plugin\Security\SignatureVerifier $signatureVerifier
    ) {
        $this->dependencyResolver = $dependencyResolver;
        $this->securityManager = $securityManager;
        $this->serviceRegistry = $serviceRegistry;
        
        $this->runtimeContext = new RuntimeContext(
            config('app.version', '1.0.0'),
            config('app.env', 'production'),
            null,
            config('app.locale', 'en')
        );

        // Build the Boot Pipeline (Laravel provider registration is handled by ModuleServiceProvider)
        $this->pipeline = [
            new Pipeline\ValidateManifestStage(),
            new Pipeline\VerifySignatureStage($signatureVerifier),
            new Pipeline\ResolveDependenciesStage($dependencyResolver),
            new Pipeline\PermissionValidationStage($securityManager),
            new Pipeline\ContextCreationStage($this->runtimeContext),
            new Pipeline\ContainerFactoryStage(),
            new Pipeline\ResourceAllocationStage(),
            new Pipeline\CapabilityRegistrationStage(),
            new Pipeline\ServiceRegistrationStage($serviceRegistry),
            new Pipeline\HealthCheckStage(),
        ];
    }

    /**
     * Bootstrap the plugin runtime kernel and all active plugins.
     */
    public function bootstrap(): void
    {
        $activeModules = Module::where('status', 'active')->get();
        $descriptorsRaw = [];

        foreach ($activeModules as $module) {
            $alias = $module->alias;
            $modulePath = base_path("Modules/{$alias}");
            
            if (!File::isDirectory($modulePath)) {
                continue;
            }

            $manifestPath = "{$modulePath}/manifest.json";
            if (!File::exists($manifestPath)) {
                $manifestPath = "{$modulePath}/module.json";
            }

            if (File::exists($manifestPath)) {
                try {
                    $manifest = json_decode(File::get($manifestPath), true) ?? [];
                    $checksum = md5_file($manifestPath);
                    $descriptor = new PluginDescriptor($manifest, $checksum);
                    $descriptorsRaw[$alias] = $descriptor;
                } catch (\Throwable $e) {
                    Log::error("RuntimeKernel: Failed parsing manifest for [{$alias}]: " . $e->getMessage());
                }
            }
        }

        // Sort active plugins topologically using Kahn's algorithm
        try {
            $orderedAliases = $this->dependencyResolver->resolve($descriptorsRaw);
        } catch (\Throwable $e) {
            Log::error("RuntimeKernel: Dependency resolution failed: " . $e->getMessage());
            return;
        }

        // Boot active plugins sequentially through the Pipeline
        foreach ($orderedAliases as $alias) {
            $descriptor = $descriptorsRaw[$alias];
            
            try {
                $context = null;
                foreach ($this->pipeline as $stage) {
                    $context = $stage->execute($descriptor, $context);
                }

                if ($context) {
                    $this->contexts[$alias] = $context;
                    $this->descriptors[$alias] = $descriptor;
                    $this->bootedPlugins[] = $alias;
                    Log::info("RuntimeKernel: Successfully booted plugin [{$alias}] through lifecycle pipeline");
                }
            } catch (\Throwable $e) {
                Log::error("RuntimeKernel: Failed booting plugin [{$alias}] in lifecycle pipeline: " . $e->getMessage());
            }
        }
    }

    public function getDescriptor(string $alias): ?PluginDescriptor
    {
        return $this->descriptors[$alias] ?? null;
    }

    public function getContext(string $alias): ?PluginContext
    {
        return $this->contexts[$alias] ?? null;
    }

    public function getBootedPlugins(): array
    {
        return $this->bootedPlugins;
    }
}
