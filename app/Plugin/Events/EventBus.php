<?php

namespace App\Plugin\Events;

use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class EventBus
 * 
 * Centralized Event Bus for cross-plugin and plugin-to-core communication.
 * Wraps Laravel's event dispatcher, enabling wildcards, queuing, and broadcasting.
 */
class EventBus
{
    /**
     * The underlying Laravel event dispatcher.
     */
    protected Dispatcher $dispatcher;

    /**
     * EventBus constructor.
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Publish (dispatch) an event to all registered listeners.
     * 
     * @param string|object $event Event name or event object.
     * @param mixed $payload Array of parameters or single object payload.
     * @param bool $halt Halt execution on first non-null response.
     * @return mixed
     */
    public function dispatch(string|object $event, mixed $payload = [], bool $halt = false): mixed
    {
        return $this->dispatcher->dispatch($event, $payload, $halt);
    }

    /**
     * Subscribe a listener to one or more events.
     * Supports wildcard subscriptions using asterisks (e.g. "employee.*").
     * 
     * @param string|array $events
     * @param mixed $listener Closure, class name, or callable.
     */
    public function listen(string|array $events, mixed $listener): void
    {
        $this->dispatcher->listen($events, $listener);
    }

    /**
     * Check if the dispatcher has any listeners registered for the event.
     */
    public function hasListeners(string $eventName): bool
    {
        return $this->dispatcher->hasListeners($eventName);
    }

    /**
     * Unsubscribe all listeners from a specific event.
     */
    public function forget(string $event): void
    {
        $this->dispatcher->forget($event);
    }
}
