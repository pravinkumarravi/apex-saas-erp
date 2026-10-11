<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void addAction(string $hook, callable $callback, int $priority = 10)
 * @method static void doAction(string $hook, mixed ...$args)
 * @method static void addFilter(string $hook, callable $callback, int $priority = 10)
 * @method static mixed applyFilter(string $hook, mixed $value, mixed ...$args)
 *
 * @see \App\Services\HookManager
 */
class Hook extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'hook';
    }
}
