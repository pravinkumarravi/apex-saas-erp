<?php

use App\Services\HookManager;
use App\Services\MenuManager;

if (!function_exists('hooks')) {
    /**
     * Get the HookManager singleton instance.
     */
    function hooks(): HookManager
    {
        return app('hook');
    }
}

if (!function_exists('add_action')) {
    /**
     * Register an action callback.
     */
    function add_action(string $hook, callable $callback, int $priority = 20): void
    {
        hooks()->addAction($hook, $callback, $priority);
    }
}

if (!function_exists('do_action')) {
    /**
     * Trigger an action hook.
     */
    function do_action(string $hook, mixed ...$args): void
    {
        hooks()->doAction($hook, ...$args);
    }
}

if (!function_exists('add_filter')) {
    /**
     * Register a filter callback.
     */
    function add_filter(string $hook, callable $callback, int $priority = 20): void
    {
        hooks()->addFilter($hook, $callback, $priority);
    }
}

if (!function_exists('apply_filters')) {
    /**
     * Apply a filter hook.
     */
    function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
    {
        return hooks()->applyFilters($hook, $value, ...$args);
    }
}

if (!function_exists('register_activation_hook')) {
    /**
     * Register module activation hook.
     */
    function register_activation_hook(string $module, callable $callback): void
    {
        hooks()->registerActivationHook($module, $callback);
    }
}

if (!function_exists('register_deactivation_hook')) {
    /**
     * Register module deactivation hook.
     */
    function register_deactivation_hook(string $module, callable $callback): void
    {
        hooks()->registerDeactivationHook($module, $callback);
    }
}

if (!function_exists('has_action')) {
    function has_action(string $hook, ?callable $callback = null): bool
    {
        return hooks()->hasAction($hook, $callback);
    }
}

if (!function_exists('has_filter')) {
    function has_filter(string $hook, ?callable $callback = null): bool
    {
        return hooks()->hasFilter($hook, $callback);
    }
}

if (!function_exists('app_menu')) {
    /**
     * Get MenuManager instance.
     */
    function app_menu(): MenuManager
    {
        return app(MenuManager::class);
    }
}
