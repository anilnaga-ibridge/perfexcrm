<?php

namespace App\Contracts\Plugins;

/**
 * Interface MenuInterface
 * 
 * Defines custom sidebar and navigation menu structures registered by plugins.
 */
interface MenuInterface
{
    /**
     * Get the menu item's unique key.
     */
    public function getKey(): string;

    /**
     * Get the display title/label.
     */
    public function getTitle(): string;

    /**
     * Get the route or link URL destination.
     */
    public function getRoute(): string;

    /**
     * Get the icon name (Ant Design or FontAwesome compatible).
     */
    public function getIcon(): ?string;

    /**
     * Get the permission required to see this menu item.
     */
    public function getPermission(): ?string;

    /**
     * Get children menu nodes.
     * 
     * @return MenuInterface[]
     */
    public function getChildren(): array;
}
