<?php

namespace App\Plugin\Lifecycle;

use App\Plugin\Events\EventBus;
use App\Plugin\Hooks\HookManager;
use App\Contracts\Plugins\PluginInterface;

/**
 * Class PluginLifecycleManager
 * 
 * Manages plugin lifecycle transitions, state auditing, and event broadcasting.
 */
class PluginLifecycleManager
{
    protected EventBus $eventBus;
    protected HookManager $hooks;

    public function __construct(EventBus $eventBus, HookManager $hooks)
    {
        $this->eventBus = $eventBus;
        $this->hooks = $hooks;
    }

    public function beforeInstall(string $alias): void
    {
        $this->hooks->doAction('plugin.before_install', $alias);
        $this->eventBus->dispatch('plugin.installing', ['alias' => $alias]);
    }

    public function afterInstall(PluginInterface $plugin): void
    {
        $this->hooks->doAction('plugin.after_install', $plugin->getAlias(), $plugin);
        $this->eventBus->dispatch('plugin.installed', ['plugin' => $plugin]);
    }

    public function beforeActivate(PluginInterface $plugin): void
    {
        $this->hooks->doAction('plugin.before_activate', $plugin->getAlias(), $plugin);
        $this->eventBus->dispatch('plugin.activating', ['plugin' => $plugin]);
    }

    public function afterActivate(PluginInterface $plugin): void
    {
        $this->hooks->doAction('plugin.after_activate', $plugin->getAlias(), $plugin);
        $this->eventBus->dispatch('plugin.activated', ['plugin' => $plugin]);
    }

    public function beforeDeactivate(PluginInterface $plugin): void
    {
        $this->hooks->doAction('plugin.before_deactivate', $plugin->getAlias(), $plugin);
        $this->eventBus->dispatch('plugin.deactivating', ['plugin' => $plugin]);
    }

    public function afterDeactivate(PluginInterface $plugin): void
    {
        $this->hooks->doAction('plugin.after_deactivate', $plugin->getAlias(), $plugin);
        $this->eventBus->dispatch('plugin.deactivated', ['plugin' => $plugin]);
    }

    public function beforeUpdate(PluginInterface $plugin, string $newVersion): void
    {
        $this->hooks->doAction('plugin.before_update', $plugin->getAlias(), $plugin, $newVersion);
        $this->eventBus->dispatch('plugin.updating', ['plugin' => $plugin, 'version' => $newVersion]);
    }

    public function afterUpdate(PluginInterface $plugin): void
    {
        $this->hooks->doAction('plugin.after_update', $plugin->getAlias(), $plugin);
        $this->eventBus->dispatch('plugin.updated', ['plugin' => $plugin]);
    }

    public function beforeRepair(PluginInterface $plugin): void
    {
        $this->hooks->doAction('plugin.before_repair', $plugin->getAlias(), $plugin);
        $this->eventBus->dispatch('plugin.repairing', ['plugin' => $plugin]);
    }

    public function afterRepair(PluginInterface $plugin): void
    {
        $this->hooks->doAction('plugin.after_repair', $plugin->getAlias(), $plugin);
        $this->eventBus->dispatch('plugin.repaired', ['plugin' => $plugin]);
    }

    public function beforeRollback(PluginInterface $plugin, string $targetVersion): void
    {
        $this->hooks->doAction('plugin.before_rollback', $plugin->getAlias(), $plugin, $targetVersion);
        $this->eventBus->dispatch('plugin.rolling_back', ['plugin' => $plugin, 'version' => $targetVersion]);
    }

    public function afterRollback(PluginInterface $plugin): void
    {
        $this->hooks->doAction('plugin.after_rollback', $plugin->getAlias(), $plugin);
        $this->eventBus->dispatch('plugin.rolled_back', ['plugin' => $plugin]);
    }

    public function beforeUninstall(PluginInterface $plugin): void
    {
        $this->hooks->doAction('plugin.before_uninstall', $plugin->getAlias(), $plugin);
        $this->eventBus->dispatch('plugin.uninstalling', ['plugin' => $plugin]);
    }

    public function afterUninstall(string $alias): void
    {
        $this->hooks->doAction('plugin.after_uninstall', $alias);
        $this->eventBus->dispatch('plugin.uninstalled', ['alias' => $alias]);
    }
}
