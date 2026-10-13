<?php

// Module Initialization for RestApi

// 1. Activation Hook
register_activation_hook('rest-api', function () {
    // Run module migrations on activation
    \Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path' => 'app/Modules/RestApi/database/migrations',
        '--force' => true,
    ]);
});

// 2. Action Hooks
add_action('admin_init', function () {
    // Custom menu registration or permissions check
});

// 3. Filter Hooks
add_filter('api_key_scopes', function ($scopes) {
    return array_merge($scopes, [
        'reports:read' => 'Read Financial & Executive Reports',
    ]);
});
