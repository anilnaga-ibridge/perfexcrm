<?php

namespace App\Plugin\Kernel;

class DependencyResolver
{
    /**
     * Build and resolve topological dependency order for plugins using Kahn's algorithm.
     *
     * @param array<string, PluginDescriptor> $descriptors Map of alias => PluginDescriptor
     * @return array<string> List of plugin aliases in order of dependency boot sequence.
     * @throws \Exception if circular dependency, version mismatch, or missing dependency detected.
     */
    public function resolve(array $descriptors): array
    {
        $adjacencyList = [];
        $inDegree = [];

        foreach ($descriptors as $alias => $desc) {
            $adjacencyList[$alias] = [];
            $inDegree[$alias] = 0;
        }

        foreach ($descriptors as $alias => $desc) {
            $dependencies = $desc->dependencies ?? [];
            foreach ($dependencies as $depAlias => $constraint) {
                if (isset($adjacencyList[$depAlias])) {
                    // Validate version compatibility
                    $depDesc = $descriptors[$depAlias];
                    if (!$this->matchesConstraint($depDesc->version, $constraint)) {
                        throw new \Exception("Plugin [{$alias}] requires dependency [{$depAlias}] version {$constraint}, but version {$depDesc->version} is installed.");
                    }

                    // $depAlias must boot BEFORE $alias
                    $adjacencyList[$depAlias][] = $alias;
                    $inDegree[$alias]++;
                } else {
                    throw new \Exception("Required dependency [{$depAlias}] for plugin [{$alias}] is not active or installed.");
                }
            }
        }

        // Run Kahn's Algorithm
        $queue = [];
        foreach ($inDegree as $alias => $degree) {
            if ($degree === 0) {
                $queue[] = $alias;
            }
        }

        $sortedOrder = [];
        while (!empty($queue)) {
            $u = array_shift($queue);
            $sortedOrder[] = $u;

            foreach ($adjacencyList[$u] as $v) {
                $inDegree[$v]--;
                if ($inDegree[$v] === 0) {
                    $queue[] = $v;
                }
            }
        }

        if (count($sortedOrder) !== count($descriptors)) {
            throw new \Exception("Circular dependency detected among active plugins!");
        }

        return $sortedOrder;
    }

    /**
     * Parse and check if a version matches a semver constraint.
     */
    public function matchesConstraint(string $version, string $constraint): bool
    {
        $constraint = trim($constraint);
        if ($constraint === '*' || $constraint === '' || $constraint === 'any') {
            return true;
        }

        $operators = ['>=', '<=', '>', '<', '!=', '==', '^', '~'];
        $operator = '==';
        $constraintVersion = $constraint;

        foreach ($operators as $op) {
            if (str_starts_with($constraint, $op)) {
                $operator = $op;
                $constraintVersion = trim(substr($constraint, strlen($op)));
                break;
            }
        }

        if ($operator === '^') {
            return version_compare($version, $constraintVersion, '>=') && 
                   version_compare($version, $this->getNextMajorVersion($constraintVersion), '<');
        }

        if ($operator === '~') {
            return version_compare($version, $constraintVersion, '>=') && 
                   version_compare($version, $this->getNextMinorVersion($constraintVersion), '<');
        }

        return version_compare($version, $constraintVersion, $operator);
    }

    protected function getNextMajorVersion(string $version): string
    {
        $parts = explode('.', $version);
        $major = (int)($parts[0] ?? 0);
        return ($major + 1) . '.0.0';
    }

    protected function getNextMinorVersion(string $version): string
    {
        $parts = explode('.', $version);
        $major = (int)($parts[0] ?? 0);
        $minor = (int)($parts[1] ?? 0);
        return $major . '.' . ($minor + 1) . '.0';
    }
}
