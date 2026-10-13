<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMainNavItems()
    {
        return [
            [
                'icon' => 'dashboard',
                'name' => 'Dashboard',
                'subItems' => [
                    ['name' => 'Executive Dashboard', 'path' => '/'],
                    ['name' => 'Analytics & KPIs', 'path' => '/analytics'],
                ],
            ],
            [
                'icon' => 'procurement',
                'name' => 'Procurement',
                'subItems' => [
                    ['name' => 'Suppliers & Vendors', 'path' => '/procurement/suppliers'],
                    ['name' => 'Purchase Orders', 'path' => '/procurement/purchase-orders'],
                    ['name' => 'Goods Receipt Notes (GRN)', 'path' => '/procurement/goods-receipts'],
                ],
            ],
            [
                'icon' => 'inventory',
                'name' => 'Inventory & Storage',
                'subItems' => [
                    ['name' => 'Product Catalog', 'path' => '/inventory/products'],
                    ['name' => 'Stock Levels', 'path' => '/inventory/stock-levels'],
                    ['name' => 'Stock Movements Ledger', 'path' => '/inventory/stock-movements'],
                    ['name' => 'Warehouses', 'path' => '/inventory/warehouses'],
                ],
            ],
            [
                'icon' => 'manufacturing',
                'name' => 'Manufacturing & MRP',
                'subItems' => [
                    ['name' => 'Bills of Material (BOM)', 'path' => '/manufacturing/bom'],
                    ['name' => 'Production Work Orders', 'path' => '/manufacturing/work-orders'],
                    ['name' => 'Material Consumption', 'path' => '/manufacturing/material-consumption'],
                    ['name' => 'Quality Control (QC)', 'path' => '/manufacturing/quality-control'],
                    ['name' => 'Work Centers', 'path' => '/manufacturing/work-centers'],
                ],
            ],
            [
                'icon' => 'crm',
                'name' => 'CRM & Sales',
                'subItems' => [
                    ['name' => 'Customers', 'path' => '/crm/customers'],
                    ['name' => 'Leads & Pipeline', 'path' => '/crm/leads'],
                    ['name' => 'Sales Orders', 'path' => '/sales/orders'],
                    ['name' => 'Dispatch & Challans', 'path' => '/sales/delivery-challans'],
                ],
            ],
            [
                'icon' => 'finance',
                'name' => 'Finance & Accounting',
                'subItems' => [
                    ['name' => 'Chart of Accounts', 'path' => '/finance/chart-of-accounts'],
                    ['name' => 'Journal Entries', 'path' => '/finance/journal-entries'],
                    ['name' => 'GST Tax Invoices', 'path' => '/finance/invoices'],
                    ['name' => 'Payments & Receipts', 'path' => '/finance/payments'],
                    ['name' => 'Indian GST Reports', 'path' => '/finance/gst-reports'],
                    ['name' => 'Financial Statements', 'path' => '/finance/financial-statements'],
                ],
            ],
            [
                'icon' => 'hr',
                'name' => 'HR & Payroll',
                'subItems' => [
                    ['name' => 'Employees', 'path' => '/hr/employees'],
                    ['name' => 'Daily Attendance', 'path' => '/hr/attendance'],
                    ['name' => 'Leave Requests', 'path' => '/hr/leaves'],
                    ['name' => 'Payroll & Payslips', 'path' => '/hr/payroll'],
                ],
            ],
            [
                'icon' => 'projects',
                'name' => 'Projects & Assets',
                'subItems' => [
                    ['name' => 'Projects & Tasks', 'path' => '/projects'],
                    ['name' => 'Fixed Asset Register', 'path' => '/assets'],
                ],
            ],
        ];
    }

    public static function getOthersItems()
    {
        return [
            [
                'icon' => 'governance',
                'name' => 'Governance',
                'subItems' => [
                    ['name' => 'Approval Workflows', 'path' => '/governance/approvals'],
                    ['name' => 'Audit Trail Logs', 'path' => '/governance/audit-logs'],
                ],
            ],
            [
                'icon' => 'settings',
                'name' => 'Administration',
                'subItems' => [
                    ['name' => 'Roles & Permissions', 'path' => '/settings/roles'],
                    ['name' => 'Company & Subscription', 'path' => '/settings/tenant'],
                    ['name' => 'API Tokens (Sanctum)', 'path' => '/settings/api-tokens'],
                    ['name' => 'Modular Plugin Manager', 'path' => '/settings/modules'],
                ],
            ],
        ];
    }

    public static function getMenuGroups()
    {
        return app(\App\Services\MenuManager::class)->getMenuGroups();
    }

    public static function isActive($path)
    {
        return request()->is(ltrim($path, '/'));
    }

    public static function getIconSvg($iconName)
    {
        $icons = [
            'dashboard' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>',

            'procurement' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 3H5L6.68 14.39C6.77 15.02 7.31 15.5 7.95 15.5H18.05C18.69 15.5 19.23 15.02 19.32 14.39L20.5 6.5H5.5M9 20C9 20.5523 8.55228 21 8 21C7.44772 21 7 20.5523 7 20C7 19.4477 7.44772 19 8 19C8.55228 19 9 19.4477 9 20ZM18 20C18 20.5523 17.5523 21 17 21C16.4477 21 16 20.5523 16 20C16 19.4477 16.4477 19 17 19C17.5523 19 18 19.4477 18 20Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'inventory' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 7.5V16.5L12 21.5L3 16.5V7.5L12 2.5L21 7.5ZM12 2.5V11.5M12 11.5L21 7.5M12 11.5L3 7.5M12 21.5V11.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'manufacturing' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="1.8"/><path d="M19.4 15A7.5 7.5 0 0 0 20 12A7.5 7.5 0 0 0 19.4 9L21 7.5L19 4L16.8 5A7.5 7.5 0 0 0 15 4.4L14.5 2H9.5L9 4.4A7.5 7.5 0 0 0 7.2 5L5 4L3 7.5L4.6 9A7.5 7.5 0 0 0 4 12A7.5 7.5 0 0 0 4.6 15L3 16.5L5 20L7.2 19A7.5 7.5 0 0 0 9 19.6L9.5 22H14.5L15 19.6A7.5 7.5 0 0 0 16.8 19L19 20L21 16.5L19.4 15Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'crm' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89317 18.7122 8.75608 18.1676 9.45768C17.623 10.1593 16.8604 10.6597 16 10.88M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'finance' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 1V23M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6313 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6313 13.6815 18 14.5717 18 15.5C18 16.4283 17.6313 17.3185 16.9749 17.9749C16.3185 18.6313 15.4283 19 14.5 19H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'hr' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="16" rx="3" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/><path d="M14 9H17M14 13H17M6 16.5C6 15 7.5 14.5 9 14.5C10.5 14.5 12 15 12 16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',

            'projects' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15M9 5C9 6.10457 9.89543 7 11 7H13C14.1046 7 15 6.10457 15 5M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5M9 12L11 14L15 10M9 17H15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'governance' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 22S20 18 20 12V5L12 2L4 5V12C4 18 12 22 12 22Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'settings' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M19.4 15A7.5 7.5 0 0 0 20 12A7.5 7.5 0 0 0 19.4 9L21 7.5L19 4L16.8 5A7.5 7.5 0 0 0 15 4.4L14.5 2H9.5L9 4.4A7.5 7.5 0 0 0 7.2 5L5 4L3 7.5L4.6 9A7.5 7.5 0 0 0 4 12A7.5 7.5 0 0 0 4.6 15L3 16.5L5 20L7.2 19A7.5 7.5 0 0 0 9 19.6L9.5 22H14.5L15 19.6A7.5 7.5 0 0 0 16.8 19L19 20L21 16.5L19.4 15Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        ];

        return $icons[$iconName] ?? '<svg width="1em" height="1em" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor"/></svg>';
    }
}
