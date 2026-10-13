<?php

// Module Initialization for HR & Payroll

register_activation_hook('human-resources', function () {
    // Initial activation tasks
});

add_action('admin_init', function () {
    // Menu or admin initialization
});

// Provide employee options for other modules (e.g. Projects)
add_filter('projects_employee_options', function ($default = null) {
    return \Modules\HumanResources\Models\Employee::where('status', 'active')->get();
});

