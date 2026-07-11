<?php

namespace App\Plugin\Hooks;

use App\Contracts\Plugins\HookInterface;

/**
 * Class HookManager
 * 
 * Manages the registration and execution of WordPress-style Actions and Filters.
 */
class HookManager
{
    /**
     * Registered action listeners grouped by action tag and priority.
     */
    protected array $actions = [];

    /**
     * Registered filter listeners grouped by filter tag and priority.
     */
    protected array $filters = [];

    /**
     * Register an action listener.
     */
    public function addAction(string $tag, $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->actions[$tag][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $acceptedArgs,
            'id' => $this->getCallbackId($callback)
        ];
        ksort($this->actions[$tag]);
    }

    /**
     * Remove an action listener.
     */
    public function removeAction(string $tag, $callback, int $priority = 10): void
    {
        if (!isset($this->actions[$tag][$priority])) {
            return;
        }

        $id = $this->getCallbackId($callback);
        foreach ($this->actions[$tag][$priority] as $index => $listener) {
            if ($listener['id'] === $id) {
                unset($this->actions[$tag][$priority][$index]);
            }
        }

        if (empty($this->actions[$tag][$priority])) {
            unset($this->actions[$tag][$priority]);
        }
    }

    /**
     * Fire the action listeners for the given tag.
     */
    public function doAction(string $tag, ...$args): void
    {
        if (!isset($this->actions[$tag])) {
            return;
        }

        foreach ($this->actions[$tag] as $priority => $listeners) {
            foreach ($listeners as $listener) {
                $callback = $listener['callback'];
                $acceptedArgs = $listener['accepted_args'];

                // Slice arguments to pass only the accepted amount
                $slicedArgs = array_slice($args, 0, $acceptedArgs);

                if ($callback instanceof HookInterface) {
                    $callback->execute(...$slicedArgs);
                } else {
                    call_user_func_array($callback, $slicedArgs);
                }
            }
        }
    }

    /**
     * Register a filter listener.
     */
    public function addFilter(string $tag, $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->filters[$tag][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $acceptedArgs,
            'id' => $this->getCallbackId($callback)
        ];
        ksort($this->filters[$tag]);
    }

    /**
     * Remove a filter listener.
     */
    public function removeFilter(string $tag, $callback, int $priority = 10): void
    {
        if (!isset($this->filters[$tag][$priority])) {
            return;
        }

        $id = $this->getCallbackId($callback);
        foreach ($this->filters[$tag][$priority] as $index => $listener) {
            if ($listener['id'] === $id) {
                unset($this->filters[$tag][$priority][$index]);
            }
        }

        if (empty($this->filters[$tag][$priority])) {
            unset($this->filters[$tag][$priority]);
        }
    }

    /**
     * Pass the initial value through all filter listeners for the given tag.
     */
    public function applyFilters(string $tag, $value, ...$args)
    {
        if (!isset($this->filters[$tag])) {
            return $value;
        }

        foreach ($this->filters[$tag] as $priority => $listeners) {
            foreach ($listeners as $listener) {
                $callback = $listener['callback'];
                $acceptedArgs = $listener['accepted_args'];

                // The first argument to the callback is always the filtered value
                $allArgs = array_merge([$value], $args);
                $slicedArgs = array_slice($allArgs, 0, $acceptedArgs);

                if ($callback instanceof HookInterface) {
                    $value = $callback->execute(...$slicedArgs);
                } else {
                    $value = call_user_func_array($callback, $slicedArgs);
                }
            }
        }

        return $value;
    }

    /**
     * Get a unique string identifier for any PHP callable type.
     */
    protected function getCallbackId($callback): string
    {
        if (is_string($callback)) {
            return $callback;
        }
        if (is_array($callback)) {
            if (is_object($callback[0])) {
                return spl_object_hash($callback[0]) . '::' . $callback[1];
            }
            return $callback[0] . '::' . $callback[1];
        }
        if (is_object($callback)) {
            return spl_object_hash($callback);
        }
        return '';
    }
}
