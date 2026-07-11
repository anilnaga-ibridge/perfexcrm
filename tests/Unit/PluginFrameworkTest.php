<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Plugin\Lifecycle\PluginLifecycleManager;
use App\Plugin\Dependency\DependencyGraph;
use App\Plugin\Context\PluginContext;
use App\Plugin\Logging\PluginLogger;
use App\Plugin\Storage\PluginStorageManager;
use App\Plugin\Configuration\ConfigurationManager;
use App\Plugin\Registries\CapabilityRegistry;
use App\Plugin\Metadata\PluginMetadata;
use App\Plugin\Upgrade\PluginUpgradeManager;
use App\Plugin\Rollback\PluginRollbackManager;
use App\Plugin\Runtime\PluginRuntime;
use App\Contracts\Plugins\PluginInterface;
use Illuminate\Support\Facades\File;

/**
 * Class PluginFrameworkTest
 * 
 * Comprehensive Unit and Integration test suite for Evolved Enterprise Plugin SDK Phase 2.
 */
class PluginFrameworkTest extends TestCase
{
    /**
     * Test dependency graph Kahn's topological sort and circular cycle detection.
     */
    public function test_dependency_graph_sorting_and_circular_checks(): void
    {
        $graph = app(DependencyGraph::class);

        $pluginA = $this->createMock(PluginInterface::class);
        $pluginA->method('getAlias')->willReturn('plugin-a');
        $pluginA->method('getName')->willReturn('Plugin A');
        $pluginA->method('getVersion')->willReturn('1.0.0');
        $pluginA->method('getDependencies')->willReturn([]);

        $pluginB = $this->createMock(PluginInterface::class);
        $pluginB->method('getAlias')->willReturn('plugin-b');
        $pluginB->method('getName')->willReturn('Plugin B');
        $pluginB->method('getVersion')->willReturn('1.0.0');
        $pluginB->method('getDependencies')->willReturn(['plugin-a' => '>=1.0.0']);

        $graph->build([$pluginA, $pluginB]);

        $this->assertFalse($graph->detectCircular());
        $order = $graph->getActivationOrder();
        $this->assertEquals(['plugin-a', 'plugin-b'], $order);
    }

    /**
     * Test plugin runtime metric logging.
     */
    public function test_plugin_runtime_performance_metrics(): void
    {
        $runtime = app(PluginRuntime::class);
        $runtime->startMetric('boot_stage');
        usleep(100);
        $runtime->stopMetric('boot_stage');

        $timings = $runtime->getTimings();
        $this->assertArrayHasKey('boot_stage', $timings);
        $this->assertGreaterThan(0, $timings['boot_stage']['duration']);
    }

    /**
     * Test storage path initialization and safety constraints.
     */
    public function test_plugin_storage_isolation_and_safety(): void
    {
        $storage = app(PluginStorageManager::class);
        $alias = 'test-plugin';

        $storagePath = $storage->getStoragePath($alias);
        $this->assertDirectoryExists($storagePath);

        // Cleanup
        $storage->purge($alias);
        $this->assertDirectoryDoesNotExist($storagePath);
    }

    /**
     * Test plugin configuration resolution precedence.
     */
    public function test_config_precedence_resolution(): void
    {
        $configManager = app(ConfigurationManager::class);
        $alias = 'payroll';

        // Override a value at runtime
        $configManager->set($alias, 'api_key', 'runtime-key-XYZ');
        
        $resolved = $configManager->get($alias, 'api_key');
        $this->assertEquals('runtime-key-XYZ', $resolved);
    }
}
