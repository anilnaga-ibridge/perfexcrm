<?php

namespace App\Plugin\Dependency;

use App\Contracts\Plugins\PluginInterface;
use App\Plugin\Versioning\VersionManager;
use Exception;

/**
 * Class DependencyGraph
 * 
 * Evolved Graph resolver that handles plugin dependencies topological sorting,
 * circular reference checks, and validation of active states.
 */
class DependencyGraph
{
    protected array $adjacency = [];
    protected array $inDegree = [];
    protected array $pluginsMap = [];
    protected VersionManager $versionManager;

    public function __construct(VersionManager $versionManager)
    {
        $this->versionManager = $versionManager;
    }

    /**
     * Build the dependency graph from a list of discovered plugins.
     * 
     * @param PluginInterface[] $plugins
     */
    public function build(array $plugins): void
    {
        $this->adjacency = [];
        $this->inDegree = [];
        $this->pluginsMap = [];

        foreach ($plugins as $plugin) {
            $alias = $plugin->getAlias();
            $this->pluginsMap[$alias] = $plugin;
            if (!isset($this->adjacency[$alias])) {
                $this->adjacency[$alias] = [];
            }
            if (!isset($this->inDegree[$alias])) {
                $this->inDegree[$alias] = 0;
            }
        }

        foreach ($plugins as $plugin) {
            $alias = $plugin->getAlias();
            $dependencies = $plugin->getDependencies();

            foreach ($dependencies as $depAlias => $constraint) {
                $required = is_numeric($depAlias) ? $constraint : $depAlias;
                
                // If the dependency exists in our list, add directed edge
                if (isset($this->pluginsMap[$required])) {
                    // Dependency must load before current plugin: $required -> $alias
                    $this->adjacency[$required][] = $alias;
                    $this->inDegree[$alias]++;
                }
            }
        }
    }

    /**
     * Detect if a circular dependency cycle exists in the graph using DFS recursion tracking.
     */
    public function detectCircular(): bool
    {
        $visited = [];
        $recStack = [];

        foreach (array_keys($this->pluginsMap) as $node) {
            if ($this->dfsCycleCheck($node, $visited, $recStack)) {
                return true;
            }
        }

        return false;
    }

    protected function dfsCycleCheck(string $node, array &$visited, array &$recStack): bool
    {
        if (empty($visited[$node])) {
            $visited[$node] = true;
            $recStack[$node] = true;

            foreach ($this->adjacency[$node] ?? [] as $neighbor) {
                if (empty($visited[$neighbor]) && $this->dfsCycleCheck($neighbor, $visited, $recStack)) {
                    return true;
                } elseif (!empty($recStack[$neighbor])) {
                    return true;
                }
            }
        }

        $recStack[$node] = false;
        return false;
    }

    /**
     * Perform topological sorting using Kahn's algorithm to obtain activation order.
     * 
     * @throws Exception if circular dependency detected
     */
    public function getActivationOrder(): array
    {
        if ($this->detectCircular()) {
            throw new Exception("Circular dependency detected. Cannot establish a safe boot order.");
        }

        $inCount = $this->inDegree;
        $queue = [];
        
        foreach ($inCount as $node => $count) {
            if ($count === 0) {
                $queue[] = $node;
            }
        }

        $order = [];
        while (!empty($queue)) {
            $curr = array_shift($queue);
            $order[] = $curr;

            foreach ($this->adjacency[$curr] ?? [] as $neighbor) {
                $inCount[$neighbor]--;
                if ($inCount[$neighbor] === 0) {
                    $queue[] = $neighbor;
                }
            }
        }

        return $order;
    }

    /**
     * Validate graph constraints, missing dependencies, and version conflicts.
     * 
     * @throws Exception
     */
    public function validate(): void
    {
        foreach ($this->pluginsMap as $alias => $plugin) {
            $dependencies = $plugin->getDependencies();

            foreach ($dependencies as $depAlias => $constraint) {
                $required = is_numeric($depAlias) ? $constraint : $depAlias;
                
                if (!isset($this->pluginsMap[$required])) {
                    throw new Exception("Plugin '{$alias}' requires missing dependency '{$required}'.");
                }

                $depPlugin = $this->pluginsMap[$required];
                
                // If both are active, check version constraints
                if ($plugin->isActive() && $depPlugin->isActive()) {
                    if (!is_numeric($depAlias)) {
                        $this->versionManager->validateDependencies($plugin, array_values($this->pluginsMap));
                    }
                }
            }
        }
    }

    /**
     * Get adjacent dependents (outgoing edges).
     */
    public function getDependents(string $alias): array
    {
        return $this->adjacency[$alias] ?? [];
    }

    /**
     * Get parent dependencies (incoming edges).
     */
    public function getDependencies(string $alias): array
    {
        $deps = [];
        foreach ($this->adjacency as $parent => $children) {
            if (in_array($alias, $children)) {
                $deps[] = $parent;
            }
        }
        return $deps;
    }

    /**
     * Expose internal graph structure.
     */
    public function getGraph(): array
    {
        return [
            'nodes' => array_keys($this->pluginsMap),
            'edges' => $this->adjacency,
        ];
    }

    /**
     * Generate textual representation of the dependency tree.
     */
    public function generateTree(): string
    {
        $order = $this->getActivationOrder();
        $roots = [];
        foreach ($this->inDegree as $node => $count) {
            if ($count === 0) {
                $roots[] = $node;
            }
        }

        $output = "Core System\n";
        foreach ($roots as $root) {
            $output .= $this->renderTreeNode($root, " ├── ");
        }

        return rtrim($output, "\n");
    }

    protected function renderTreeNode(string $node, string $prefix): string
    {
        $plugin = $this->pluginsMap[$node] ?? null;
        $label = $plugin ? "{$plugin->getName()} (v{$plugin->getVersion()})" : $node;
        $result = $prefix . $label . "\n";

        $children = $this->adjacency[$node] ?? [];
        $childCount = count($children);
        
        foreach ($children as $index => $child) {
            $isLast = ($index === $childCount - 1);
            $newPrefix = $isLast ? " └── " : " ├── ";
            $result .= $this->renderTreeNode($child, $prefix . $newPrefix);
        }

        return $result;
    }
}
