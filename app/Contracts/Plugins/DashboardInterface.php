<?php

namespace App\Contracts\Plugins;

/**
 * Interface DashboardInterface
 * 
 * Defines how plugins hook custom panels and charts into the primary dashboard.
 */
interface DashboardInterface
{
    /**
     * Get the default panel/dashboard key identifier.
     */
    public function getDashboardKey(): string;

    /**
     * Get layout cards and widgets associated with this dashboard view.
     */
    public function getWidgets(): array;
}
