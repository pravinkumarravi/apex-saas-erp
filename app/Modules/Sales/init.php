<?php

// Module Initialization for Sales & CRM

// 1. Activation Hook
register_activation_hook('sales', function () {
    // Code executed upon module activation (e.g. initial setup)
});

// 2. Action Hooks
// Hook into admin/menu initialization
add_action('admin_init', function () {
    // Custom menu registration or permissions check
});

// Action executed after a sales order is created
add_action('after_sales_order_created', function ($salesOrder) {
    // Inter-module events, audit logging, webhook dispatch, etc.
});

// Action executed after a customer is created
add_action('after_customer_created', function ($customer) {
    // Trigger customer welcome notifications, syncs, etc.
});

// 3. Filter Hooks
add_filter('sales_orders_list', function ($orders) {
    // Intercept and modify or annotate orders list before rendering
    return $orders;
});

// Provide customer options for external modules (e.g. Accounting, Projects)
add_filter('accounting_customer_options', function ($default = null) {
    return \Modules\Sales\Models\Customer::where('status', 'active')->get();
});

add_filter('projects_customer_options', function ($default = null) {
    return \Modules\Sales\Models\Customer::where('status', 'active')->get();
});


