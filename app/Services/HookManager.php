<?php

namespace App\Services;

class HookManager
{
    protected array $actions = [];
    protected array $filters = [];
    protected array $activationHooks = [];
    protected array $deactivationHooks = [];

    /**
     * Register an action callback.
     */
    public function addAction(string $hook, callable $callback, int $priority = 20): void
    {
        $this->actions[$hook][$priority][] = $callback;
    }

    /**
     * Trigger an action hook.
     */
    public function doAction(string $hook, mixed ...$args): void
    {
        if (!isset($this->actions[$hook])) {
            return;
        }

        ksort($this->actions[$hook]);

        foreach ($this->actions[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }

    /**
     * Check if an action hook has callbacks registered.
     */
    public function hasAction(string $hook, ?callable $callback = null): bool
    {
        if (!isset($this->actions[$hook])) {
            return false;
        }

        if ($callback === null) {
            return !empty($this->actions[$hook]);
        }

        foreach ($this->actions[$hook] as $callbacks) {
            if (in_array($callback, $callbacks, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove an action callback.
     */
    public function removeAction(string $hook, ?callable $callback = null, ?int $priority = null): bool
    {
        if (!isset($this->actions[$hook])) {
            return false;
        }

        if ($callback === null && $priority === null) {
            unset($this->actions[$hook]);
            return true;
        }

        if ($priority !== null && isset($this->actions[$hook][$priority])) {
            if ($callback === null) {
                unset($this->actions[$hook][$priority]);
                return true;
            }

            $key = array_search($callback, $this->actions[$hook][$priority], true);
            if ($key !== false) {
                unset($this->actions[$hook][$priority][$key]);
                return true;
            }
        }

        if ($callback !== null) {
            $removed = false;
            foreach ($this->actions[$hook] as $p => $callbacks) {
                $key = array_search($callback, $callbacks, true);
                if ($key !== false) {
                    unset($this->actions[$hook][$p][$key]);
                    $removed = true;
                }
            }
            return $removed;
        }

        return false;
    }

    /**
     * Register a filter callback.
     */
    public function addFilter(string $hook, callable $callback, int $priority = 20): void
    {
        $this->filters[$hook][$priority][] = $callback;
    }

    /**
     * Apply filter hooks to modify and return a value.
     */
    public function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (!isset($this->filters[$hook])) {
            return $value;
        }

        ksort($this->filters[$hook]);

        foreach ($this->filters[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $value = call_user_func_array($callback, array_merge([$value], $args));
            }
        }

        return $value;
    }

    /**
     * Alias for applyFilters.
     */
    public function applyFilter(string $hook, mixed $value, mixed ...$args): mixed
    {
        return $this->applyFilters($hook, $value, ...$args);
    }

    /**
     * Check if a filter hook has callbacks registered.
     */
    public function hasFilter(string $hook, ?callable $callback = null): bool
    {
        if (!isset($this->filters[$hook])) {
            return false;
        }

        if ($callback === null) {
            return !empty($this->filters[$hook]);
        }

        foreach ($this->filters[$hook] as $callbacks) {
            if (in_array($callback, $callbacks, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove a filter callback.
     */
    public function removeFilter(string $hook, ?callable $callback = null, ?int $priority = null): bool
    {
        if (!isset($this->filters[$hook])) {
            return false;
        }

        if ($callback === null && $priority === null) {
            unset($this->filters[$hook]);
            return true;
        }

        if ($priority !== null && isset($this->filters[$hook][$priority])) {
            if ($callback === null) {
                unset($this->filters[$hook][$priority]);
                return true;
            }

            $key = array_search($callback, $this->filters[$hook][$priority], true);
            if ($key !== false) {
                unset($this->filters[$hook][$priority][$key]);
                return true;
            }
        }

        if ($callback !== null) {
            $removed = false;
            foreach ($this->filters[$hook] as $p => $callbacks) {
                $key = array_search($callback, $callbacks, true);
                if ($key !== false) {
                    unset($this->filters[$hook][$p][$key]);
                    $removed = true;
                }
            }
            return $removed;
        }

        return false;
    }

    /**
     * Register an activation hook for a module.
     */
    public function registerActivationHook(string $module, callable $callback): void
    {
        $this->activationHooks[$module][] = $callback;
    }

    /**
     * Register a deactivation hook for a module.
     */
    public function registerDeactivationHook(string $module, callable $callback): void
    {
        $this->deactivationHooks[$module][] = $callback;
    }

    /**
     * Execute activation hooks for a module.
     */
    public function runActivationHook(string $module): void
    {
        if (isset($this->activationHooks[$module])) {
            foreach ($this->activationHooks[$module] as $callback) {
                call_user_func($callback);
            }
        }
    }

    /**
     * Execute deactivation hooks for a module.
     */
    public function runDeactivationHook(string $module): void
    {
        if (isset($this->deactivationHooks[$module])) {
            foreach ($this->deactivationHooks[$module] as $callback) {
                call_user_func($callback);
            }
        }
    }

    /**
     * Clear all registered actions and filters (useful in tests).
     */
    public function reset(): void
    {
        $this->actions = [];
        $this->filters = [];
        $this->activationHooks = [];
        $this->deactivationHooks = [];
    }
}
