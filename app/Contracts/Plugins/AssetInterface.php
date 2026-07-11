<?php

namespace App\Contracts\Plugins;

/**
 * Interface AssetInterface
 * 
 * Defines public CSS/JS/Vue/image assets associated with a plugin.
 */
interface AssetInterface
{
    /**
     * Get the source directory path of the assets.
     */
    public function getSourcePath(): string;

    /**
     * Get the target public publish directory alias (e.g. "payroll").
     */
    public function getPublishAlias(): string;

    /**
     * Get list of assets to automatically load/inject on views.
     */
    public function getInjectableAssets(): array;
}
