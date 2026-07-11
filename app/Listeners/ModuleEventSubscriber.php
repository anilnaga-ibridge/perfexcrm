<?php

namespace App\Listeners;

use App\Events\ModuleInstalled;
use App\Events\ModuleActivated;
use App\Events\ModuleDeactivated;
use App\Events\ModuleUpgraded;
use App\Events\ModuleUninstalled;
use App\Models\ModuleEvent;
use Illuminate\Events\Dispatcher;

class ModuleEventSubscriber
{
    /**
     * Log a module lifecycle event.
     */
    protected function logEvent(string $eventName, $module, array $extraPayload = []): void
    {
        ModuleEvent::create([
            'module_id' => $module->id ?? null,
            'module_alias' => $module->alias ?? $module->name ?? 'unknown',
            'event_name' => $eventName,
            'payload' => array_merge([
                'name' => $module->name ?? null,
                'version' => $module->version ?? null,
                'timestamp' => now()->toIso8601String(),
            ], $extraPayload),
        ]);
    }

    public function onInstalled(ModuleInstalled $event): void
    {
        $this->logEvent('ModuleInstalled', $event->module);
    }

    public function onActivated(ModuleActivated $event): void
    {
        $this->logEvent('ModuleActivated', $event->module);
    }

    public function onDeactivated(ModuleDeactivated $event): void
    {
        $this->logEvent('ModuleDeactivated', $event->module);
    }

    public function onUpgraded(ModuleUpgraded $event): void
    {
        $this->logEvent('ModuleUpgraded', $event->module);
    }

    public function onUninstalled(ModuleUninstalled $event): void
    {
        $this->logEvent('ModuleUninstalled', $event->module);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            ModuleInstalled::class => 'onInstalled',
            ModuleActivated::class => 'onActivated',
            ModuleDeactivated::class => 'onDeactivated',
            ModuleUpgraded::class => 'onUpgraded',
            ModuleUninstalled::class => 'onUninstalled',
        ];
    }
}
