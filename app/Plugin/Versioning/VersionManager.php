<?php

namespace App\Plugin\Versioning;

use App\Contracts\Plugins\PluginInterface;
use Exception;

/**
 * Class VersionManager
 * 
 * Evolved SemVer manager resolving plugin dependencies, conflicts, and core compatibility.
 */
class VersionManager
{
    /**
     * Verify if the core system version satisfies the plugin's limits.
     *
     * @throws Exception
     */
    public function checkCoreCompatibility(string $coreVersion, string $minCore, ?string $maxCore = null): bool
    {
        if (version_compare($coreVersion, $minCore, '<')) {
            throw new Exception("Plugin requires core version >= {$minCore}. Current core version is {$coreVersion}.");
        }

        if ($maxCore && version_compare($coreVersion, $maxCore, '>')) {
            throw new Exception("Plugin requires core version <= {$maxCore}. Current core version is {$coreVersion}.");
        }

        return true;
    }

    /**
     * Validate a plugin's dependency specifications against currently active/installed plugins.
     * 
     * @param PluginInterface $plugin
     * @param PluginInterface[] $installedPlugins
     * @throws Exception
     */
    public function validateDependencies(PluginInterface $plugin, array $installedPlugins): bool
    {
        $dependencies = $plugin->getDependencies();
        
        foreach ($dependencies as $depAlias => $constraint) {
            // Support simple flat alias array or structured alias => constraint
            $requiredAlias = is_numeric($depAlias) ? $constraint : $depAlias;
            $versionConstraint = is_numeric($depAlias) ? null : $constraint;

            $found = null;
            foreach ($installedPlugins as $installed) {
                if ($installed->getAlias() === $requiredAlias) {
                    $found = $installed;
                    break;
                }
            }

            if (!$found) {
                throw new Exception("Missing required dependency: '{$requiredAlias}'.");
            }

            if (!$found->isActive()) {
                throw new Exception("Required dependency '{$requiredAlias}' must be activated first.");
            }

            if ($versionConstraint) {
                $this->checkVersionConstraint($found->getVersion(), $versionConstraint, $requiredAlias);
            }
        }

        return true;
    }

    /**
     * Parse and check a semantic version constraint (e.g. ">=1.2.0", "^2.0.0").
     *
     * @throws Exception
     */
    protected function checkVersionConstraint(string $version, string $constraint, string $alias): void
    {
        // Simple operators
        if (preg_match('/^([<>=!]+)\s*([0-9\.]+)/', $constraint, $matches)) {
            $operator = $matches[1];
            $targetVersion = $matches[2];
            if (!version_compare($version, $targetVersion, $operator)) {
                throw new Exception("Dependency '{$alias}' version {$version} does not satisfy constraint '{$constraint}'.");
            }
            return;
        }

        // Caret constraint (e.g. ^1.2.3 -> >=1.2.3 <2.0.0)
        if (str_starts_with($constraint, '^')) {
            $targetVersion = ltrim($constraint, '^');
            $parts = explode('.', $targetVersion);
            $nextMajor = ((int) ($parts[0] ?? 0)) + 1;
            $upperLimit = "{$nextMajor}.0.0";

            if (version_compare($version, $targetVersion, '<') || version_compare($version, $upperLimit, '>=')) {
                throw new Exception("Dependency '{$alias}' version {$version} does not satisfy caret constraint '{$constraint}'.");
            }
            return;
        }

        // Default direct comparison if no operator
        if (version_compare($version, $constraint, '<')) {
            throw new Exception("Dependency '{$alias}' version {$version} must be at least {$constraint}.");
        }
    }
}
