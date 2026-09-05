<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Plugin\Kernel\RuntimeKernel;
use App\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\App;

/**
 * Verifies that PluginServiceProvider registration is owned exclusively
 * by ModuleServiceProvider, not RuntimeKernel.
 */
class ProviderRegistrationTest extends TestCase
{
    /**
     * Test that ProviderRegistrationStage is no longer in the RuntimeKernel pipeline.
     */
    public function test_runtime_kernel_pipeline_does_not_contain_provider_registration_stage(): void
    {
        $kernel = app(RuntimeKernel::class);

        $reflection = new \ReflectionClass($kernel);
        $pipelineProperty = $reflection->getProperty('pipeline');
        $pipelineProperty->setAccessible(true);
        $pipeline = $pipelineProperty->getValue($kernel);

        $stageClassNames = array_map(fn($stage) => get_class($stage), $pipeline);

        $this->assertNotContains(
            'App\Plugin\Kernel\Pipeline\ProviderRegistrationStage',
            $stageClassNames,
            'RuntimeKernel pipeline must not contain ProviderRegistrationStage'
        );
    }

    /**
     * Test that RuntimeKernel pipeline still contains the essential runtime stages.
     */
    public function test_runtime_kernel_pipeline_still_contains_essential_stages(): void
    {
        $kernel = app(RuntimeKernel::class);

        $reflection = new \ReflectionClass($kernel);
        $pipelineProperty = $reflection->getProperty('pipeline');
        $pipelineProperty->setAccessible(true);
        $pipeline = $pipelineProperty->getValue($kernel);

        $stageClassNames = array_map(fn($stage) => get_class($stage), $pipeline);

        $expectedStages = [
            'App\Plugin\Kernel\Pipeline\ValidateManifestStage',
            'App\Plugin\Kernel\Pipeline\ContextCreationStage',
            'App\Plugin\Kernel\Pipeline\ContainerFactoryStage',
            'App\Plugin\Kernel\Pipeline\ResourceAllocationStage',
            'App\Plugin\Kernel\Pipeline\ServiceRegistrationStage',
        ];

        foreach ($expectedStages as $expected) {
            $this->assertContains(
                $expected,
                $stageClassNames,
                "RuntimeKernel pipeline must contain {$expected}"
            );
        }
    }

    /**
     * Test that ProviderRegistrationStage class file no longer exists.
     */
    public function test_provider_registration_stage_file_does_not_exist(): void
    {
        $this->assertFileDoesNotExist(
            app_path('Plugin/Kernel/Pipeline/ProviderRegistrationStage.php'),
            'ProviderRegistrationStage.php should be deleted'
        );
    }

    /**
     * Test that ModuleServiceProvider is registered in the application.
     */
    public function test_module_service_provider_is_registered(): void
    {
        $registeredProviders = App::getLoadedProviders();

        $this->assertArrayHasKey(
            ModuleServiceProvider::class,
            $registeredProviders,
            'ModuleServiceProvider must be registered in the application'
        );
    }
}
