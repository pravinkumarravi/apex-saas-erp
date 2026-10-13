<?php

namespace Database\Seeders;

use Modules\Governance\Domain\Models\ApprovalRequest;
use Modules\Governance\Domain\Models\ApprovalWorkflow;
use Modules\Assets\Domain\Models\Asset;
use Modules\Assets\Domain\Models\AssetCategory;
use Modules\HumanResources\Domain\Models\Attendance;
use Modules\Governance\Domain\Models\AuditLog;
use Modules\Manufacturing\Domain\Models\BillOfMaterial;
use Modules\Manufacturing\Domain\Models\BomItem;
use Modules\Accounting\Domain\Models\ChartOfAccount;
use Modules\Sales\Domain\Models\Customer;
use Modules\Sales\Domain\Models\DeliveryChallan;
use Modules\HumanResources\Domain\Models\Department;
use Modules\HumanResources\Domain\Models\Designation;
use Modules\HumanResources\Domain\Models\Employee;
use Modules\Accounting\Domain\Models\FiscalYear;
use Modules\Procurement\Domain\Models\GoodsReceiptItem;
use Modules\Procurement\Domain\Models\GoodsReceiptNote;
use Modules\Accounting\Domain\Models\GstTaxRate;
use Modules\Accounting\Domain\Models\JournalEntry;
use Modules\Accounting\Domain\Models\JournalItem;
use Modules\Sales\Domain\Models\Lead;
use Modules\HumanResources\Domain\Models\LeaveApplication;
use Modules\Accounting\Domain\Models\Payment;
use Modules\HumanResources\Domain\Models\PayrollRun;
use Modules\HumanResources\Domain\Models\Payslip;
use Modules\Identity\Domain\Models\Permission;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Models\ProductCategory;
use Modules\Manufacturing\Domain\Models\ProductionMaterialConsumption;
use Modules\Manufacturing\Domain\Models\ProductionOrder;
use Modules\Projects\Domain\Models\Project;
use Modules\Projects\Domain\Models\ProjectTask;
use Modules\Procurement\Domain\Models\PurchaseOrder;
use Modules\Procurement\Domain\Models\PurchaseOrderItem;
use Modules\Manufacturing\Domain\Models\QualityInspection;
use Modules\Identity\Domain\Models\Role;
use Modules\Sales\Domain\Models\SalesOrder;
use Modules\Sales\Domain\Models\SalesOrderItem;
use Modules\Inventory\Domain\Models\StockLevel;
use Modules\Inventory\Domain\Models\StockMovement;
use Modules\Organization\Domain\Models\Subscription;
use Modules\Procurement\Domain\Models\Supplier;
use Modules\Accounting\Domain\Models\TaxInvoice;
use Modules\Accounting\Domain\Models\TaxInvoiceItem;
use Modules\Organization\Domain\Models\Tenant;
use Modules\Inventory\Domain\Models\UnitOfMeasure;
use Modules\Identity\Domain\Models\User;
use Modules\Inventory\Domain\Models\Warehouse;
use Modules\Manufacturing\Domain\Models\WorkCenter;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Super Admin (Apex Platform User)
        $superAdmin = User::create([
            'name' => 'System SuperAdmin',
            'email' => 'superadmin@erp.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status' => 'active',
            'phone' => '+91 98200 11223',
        ]);

        // 2. Create Permissions
        $modules = [
            'tenancy' => ['view-tenants', 'manage-tenants', 'manage-subscriptions'],
            'procurement' => ['view-suppliers', 'manage-suppliers', 'view-pos', 'create-pos', 'approve-pos', 'view-grn', 'create-grn'],
            'inventory' => ['view-inventory', 'manage-products', 'manage-warehouses', 'adjust-stock', 'transfer-stock'],
            'manufacturing' => ['view-bom', 'manage-bom', 'view-production', 'create-work-orders', 'record-qc'],
            'sales' => ['view-customers', 'manage-customers', 'view-leads', 'manage-leads', 'view-so', 'create-so', 'manage-logistics'],
            'finance' => ['view-coa', 'manage-coa', 'view-journals', 'post-journals', 'view-invoices', 'create-invoices', 'record-payments', 'view-gst-reports'],
            'hr' => ['view-employees', 'manage-employees', 'record-attendance', 'approve-leaves', 'process-payroll'],
            'projects' => ['view-projects', 'manage-projects', 'manage-tasks'],
            'assets' => ['view-assets', 'manage-assets', 'record-depreciation'],
            'settings' => ['view-audit-logs', 'manage-roles', 'manage-settings'],
        ];

        $permissionModels = [];
        foreach ($modules as $module => $slugs) {
            foreach ($slugs as $slug) {
                $permissionModels[$slug] = Permission::create([
                    'name' => ucwords(str_replace('-', ' ', $slug)),
                    'slug' => $slug,
                    'module' => $module,
                ]);
            }
        }

        // 3. Create Primary Tenant: Tata Precision Engineering Ltd (companya)
        $tenantA = Tenant::create([
            'name' => 'Tata Precision Engineering Ltd',
            'subdomain' => 'companya',
            'domain' => 'companya.localhost',
            'plan_name' => 'Enterprise Cloud',
            'status' => 'active',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'gstin' => '27AABCT3518Q1ZV',
            'pan' => 'AABCT3518Q',
            'state_code' => '27', // Maharashtra
            'address' => 'Plot C-14, MIDC Industrial Area, Bhosari, Pune - 411026, Maharashtra',
            'settings' => [
                'fiscal_year_start' => '04-01',
                'auto_generate_invoice_no' => true,
                'default_tax_rate' => 18,
                'allow_negative_stock' => false,
                'eway_bill_threshold' => 50000,
            ],
        ]);

        Subscription::create([
            'tenant_id' => $tenantA->id,
            'plan_name' => 'Enterprise Cloud',
            'amount' => 14999.00,
            'billing_cycle' => 'monthly',
            'starts_at' => now()->startOfMonth(),
            'ends_at' => now()->addYear(),
            'status' => 'active',
            'payment_reference' => 'SUB-RAZOR-2026-9921',
        ]);

        // 4. Create Secondary Tenant: Zenith Robotics (companyb) for multi-tenancy verification
        $tenantB = Tenant::create([
            'name' => 'Zenith Robotics Technologies',
            'subdomain' => 'companyb',
            'domain' => 'companyb.localhost',
            'plan_name' => 'Professional',
            'status' => 'active',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'gstin' => '29AAACZ1234F1Z8',
            'pan' => 'AAACZ1234F',
            'state_code' => '29', // Karnataka
            'address' => 'Electronic City Phase 1, Bengaluru - 560100, Karnataka',
            'settings' => [
                'fiscal_year_start' => '04-01',
                'auto_generate_invoice_no' => true,
                'default_tax_rate' => 18,
            ],
        ]);

        Subscription::create([
            'tenant_id' => $tenantB->id,
            'plan_name' => 'Professional',
            'amount' => 7999.00,
            'billing_cycle' => 'monthly',
            'starts_at' => now()->startOfMonth(),
            'ends_at' => now()->addMonths(6),
            'status' => 'active',
            'payment_reference' => 'SUB-RAZOR-2026-4412',
        ]);

        // Now seed rich operational data for Tenant A (Tata Precision Engineering)
        TenantContext::setTenant($tenantA);

        // A. Roles for Tenant A
        $adminRole = Role::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Company Administrator',
            'slug' => 'admin',
            'description' => 'Full administrative access to all ERP modules within this tenant',
            'is_system' => true,
        ]);
        $adminRole->permissions()->attach(Permission::pluck('id'));

        $accountantRole = Role::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Chief Accountant',
            'slug' => 'accountant',
            'description' => 'Accounting, Financial Reports, Tax Invoices and Payments',
            'is_system' => false,
        ]);
        $accountantRole->permissions()->attach(Permission::where('module', 'finance')->pluck('id'));

        $plantManagerRole = Role::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Plant Operations Manager',
            'slug' => 'plant-manager',
            'description' => 'Manufacturing, Work Orders, Inventory and Quality Control',
            'is_system' => false,
        ]);
        $plantManagerRole->permissions()->attach(Permission::whereIn('module', ['inventory', 'manufacturing', 'procurement'])->pluck('id'));

        // B. Users for Tenant A
        $adminUserA = User::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Rajesh Sharma',
            'email' => 'admin@companya.erp.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'phone' => '+91 98220 44556',
        ]);
        $adminUserA->roles()->attach($adminRole);

        $accountantUser = User::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Pooja Kulkarni',
            'email' => 'accountant@companya.erp.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'status' => 'active',
            'phone' => '+91 98220 77889',
        ]);
        $accountantUser->roles()->attach($accountantRole);

        $managerUser = User::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Vikram Deshmukh',
            'email' => 'manager@companya.erp.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'status' => 'active',
            'phone' => '+91 98220 33221',
        ]);
        $managerUser->roles()->attach($plantManagerRole);

        // User for Tenant B
        User::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Karthik Raman',
            'email' => 'admin@companyb.erp.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'phone' => '+91 98450 12345',
        ]);

        // C. GST Tax Rates
        $gstRates = [
            ['code' => 'GST 0%', 'rate' => 0.00, 'cgst_rate' => 0.00, 'sgst_rate' => 0.00, 'igst_rate' => 0.00, 'description' => 'Exempt / Zero Rated Goods'],
            ['code' => 'GST 5%', 'rate' => 5.00, 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'description' => 'Concessional Goods'],
            ['code' => 'GST 12%', 'rate' => 12.00, 'cgst_rate' => 6.00, 'sgst_rate' => 6.00, 'igst_rate' => 12.00, 'description' => 'Standard Rate Slab 1'],
            ['code' => 'GST 18%', 'rate' => 18.00, 'cgst_rate' => 9.00, 'sgst_rate' => 9.00, 'igst_rate' => 18.00, 'description' => 'Standard Rate Slab 2 (Engineering & Electronics)'],
            ['code' => 'GST 28%', 'rate' => 28.00, 'cgst_rate' => 14.00, 'sgst_rate' => 14.00, 'igst_rate' => 28.00, 'description' => 'Luxury / De-merit Goods'],
        ];
        foreach ($gstRates as $gst) {
            GstTaxRate::create(array_merge($gst, ['tenant_id' => $tenantA->id]));
            GstTaxRate::create(array_merge($gst, ['tenant_id' => $tenantB->id]));
        }

        // D. Fiscal Year
        FiscalYear::create([
            'tenant_id' => $tenantA->id,
            'title' => 'FY 2026-27',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'is_closed' => false,
        ]);

        // E. Chart of Accounts (COA) for Tenant A
        $coaData = [
            // Assets
            ['code' => '1000', 'name' => 'Current Assets', 'type' => 'asset', 'subtype' => 'current_asset'],
            ['code' => '1100', 'name' => 'Cash on Hand', 'type' => 'asset', 'subtype' => 'bank', 'parent_code' => '1000'],
            ['code' => '1110', 'name' => 'HDFC Bank Current A/c (50200034921)', 'type' => 'asset', 'subtype' => 'bank', 'parent_code' => '1000'],
            ['code' => '1200', 'name' => 'Accounts Receivable (Debtors)', 'type' => 'asset', 'subtype' => 'accounts_receivable', 'parent_code' => '1000'],
            ['code' => '1300', 'name' => 'Raw Materials Inventory', 'type' => 'asset', 'subtype' => 'inventory', 'parent_code' => '1000'],
            ['code' => '1310', 'name' => 'Work-In-Progress (WIP) Inventory', 'type' => 'asset', 'subtype' => 'inventory', 'parent_code' => '1000'],
            ['code' => '1320', 'name' => 'Finished Goods Inventory', 'type' => 'asset', 'subtype' => 'inventory', 'parent_code' => '1000'],
            ['code' => '1400', 'name' => 'Input CGST Receivable', 'type' => 'asset', 'subtype' => 'tax', 'parent_code' => '1000'],
            ['code' => '1410', 'name' => 'Input SGST Receivable', 'type' => 'asset', 'subtype' => 'tax', 'parent_code' => '1000'],
            ['code' => '1420', 'name' => 'Input IGST Receivable', 'type' => 'asset', 'subtype' => 'tax', 'parent_code' => '1000'],
            ['code' => '1500', 'name' => 'Plant Machinery & Equipment', 'type' => 'asset', 'subtype' => 'fixed_asset'],

            // Liabilities
            ['code' => '2000', 'name' => 'Current Liabilities', 'type' => 'liability', 'subtype' => 'current_liability'],
            ['code' => '2100', 'name' => 'Accounts Payable (Creditors)', 'type' => 'liability', 'subtype' => 'accounts_payable', 'parent_code' => '2000'],
            ['code' => '2200', 'name' => 'Output CGST Payable', 'type' => 'liability', 'subtype' => 'tax', 'parent_code' => '2000'],
            ['code' => '2210', 'name' => 'Output SGST Payable', 'type' => 'liability', 'subtype' => 'tax', 'parent_code' => '2000'],
            ['code' => '2220', 'name' => 'Output IGST Payable', 'type' => 'liability', 'subtype' => 'tax', 'parent_code' => '2000'],
            ['code' => '2300', 'name' => 'Salaries & Wages Payable', 'type' => 'liability', 'subtype' => 'payroll', 'parent_code' => '2000'],
            ['code' => '2400', 'name' => 'Provident Fund (PF) Payable', 'type' => 'liability', 'subtype' => 'payroll', 'parent_code' => '2000'],
            ['code' => '2500', 'name' => 'ESI Payable', 'type' => 'liability', 'subtype' => 'payroll', 'parent_code' => '2000'],
            ['code' => '2600', 'name' => 'TDS Payable', 'type' => 'liability', 'subtype' => 'tax', 'parent_code' => '2000'],

            // Equity
            ['code' => '3000', 'name' => 'Shareholders Capital', 'type' => 'equity', 'subtype' => 'equity'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity', 'subtype' => 'equity'],

            // Revenue
            ['code' => '4000', 'name' => 'Revenue from Operations', 'type' => 'revenue', 'subtype' => 'operating_revenue'],
            ['code' => '4100', 'name' => 'Sales Revenue - Domestic (Intra-state)', 'type' => 'revenue', 'subtype' => 'operating_revenue', 'parent_code' => '4000'],
            ['code' => '4110', 'name' => 'Sales Revenue - Inter-state', 'type' => 'revenue', 'subtype' => 'operating_revenue', 'parent_code' => '4000'],
            ['code' => '4200', 'name' => 'Engineering & Assembly Services', 'type' => 'revenue', 'subtype' => 'operating_revenue', 'parent_code' => '4000'],

            // Expense
            ['code' => '5000', 'name' => 'Cost of Goods Sold (COGS)', 'type' => 'expense', 'subtype' => 'direct_expense'],
            ['code' => '5100', 'name' => 'Raw Material Consumption', 'type' => 'expense', 'subtype' => 'direct_expense', 'parent_code' => '5000'],
            ['code' => '5200', 'name' => 'Direct Factory Labor & Machining', 'type' => 'expense', 'subtype' => 'direct_expense', 'parent_code' => '5000'],
            ['code' => '6000', 'name' => 'Operating & Administrative Expenses', 'type' => 'expense', 'subtype' => 'indirect_expense'],
            ['code' => '6100', 'name' => 'Employee Salaries & Allowances', 'type' => 'expense', 'subtype' => 'payroll', 'parent_code' => '6000'],
            ['code' => '6200', 'name' => 'Factory Power, Fuel & Water', 'type' => 'expense', 'subtype' => 'utility', 'parent_code' => '6000'],
            ['code' => '6300', 'name' => 'Freight & Logistics Outward', 'type' => 'expense', 'subtype' => 'logistics', 'parent_code' => '6000'],
            ['code' => '6400', 'name' => 'Depreciation on Plant Assets', 'type' => 'expense', 'subtype' => 'depreciation', 'parent_code' => '6000'],
        ];

        $accountMap = [];
        foreach ($coaData as $row) {
            $parentId = isset($row['parent_code']) && isset($accountMap[$row['parent_code']]) ? $accountMap[$row['parent_code']]->id : null;
            $account = ChartOfAccount::create([
                'tenant_id' => $tenantA->id,
                'code' => $row['code'],
                'name' => $row['name'],
                'type' => $row['type'],
                'subtype' => $row['subtype'],
                'parent_id' => $parentId,
                'is_active' => true,
            ]);
            $accountMap[$row['code']] = $account;
        }

        // F. Units of Measure (UOM)
        $uomPcs = UnitOfMeasure::create(['tenant_id' => $tenantA->id, 'name' => 'Pieces', 'code' => 'PCS', 'symbol' => 'pc']);
        $uomKg = UnitOfMeasure::create(['tenant_id' => $tenantA->id, 'name' => 'Kilograms', 'code' => 'KG', 'symbol' => 'kg']);
        $uomMtr = UnitOfMeasure::create(['tenant_id' => $tenantA->id, 'name' => 'Meters', 'code' => 'MTR', 'symbol' => 'm']);
        $uomBox = UnitOfMeasure::create(['tenant_id' => $tenantA->id, 'name' => 'Boxes', 'code' => 'BOX', 'symbol' => 'bx']);

        // G. Product Categories
        $catRaw = ProductCategory::create(['tenant_id' => $tenantA->id, 'name' => 'Raw Materials & Metals']);
        $catElectronics = ProductCategory::create(['tenant_id' => $tenantA->id, 'name' => 'Electronics & Actuators']);
        $catFasteners = ProductCategory::create(['tenant_id' => $tenantA->id, 'name' => 'Fasteners & Hardware']);
        $catFinished = ProductCategory::create(['tenant_id' => $tenantA->id, 'name' => 'Precision Machinery & Robotics']);

        // H. Warehouses
        $whMain = Warehouse::create([
            'tenant_id' => $tenantA->id,
            'code' => 'WH-MAIN',
            'name' => 'Bhosari Plant Central Warehouse',
            'address' => 'Plot C-14, Sector 7, Bhosari MIDC, Pune',
            'contact_person' => 'Ganesh Patil',
            'contact_phone' => '+91 98221 00112',
            'is_active' => true,
        ]);

        $whFinished = Warehouse::create([
            'tenant_id' => $tenantA->id,
            'code' => 'WH-HUB',
            'name' => 'Western Logistics & Distribution Hub',
            'address' => 'Gala 102, Logistics Park, Bhiwandi, Thane',
            'contact_person' => 'Sunil Jadhav',
            'contact_phone' => '+91 98221 00334',
            'is_active' => true,
        ]);

        // I. Products
        $steelSheet = Product::create([
            'tenant_id' => $tenantA->id,
            'category_id' => $catRaw->id,
            'uom_id' => $uomKg->id,
            'sku' => 'RAW-STL-001',
            'barcode' => '8901234001',
            'name' => 'Industrial Grade Steel Sheet 2mm (CRCA)',
            'description' => 'Cold Rolled Close Annealed mild steel sheet for CNC enclosures',
            'hsn_code' => '7208',
            'product_type' => 'raw_material',
            'purchase_price' => 480.00,
            'sales_price' => 0.00,
            'gst_rate' => 18.00,
            'reorder_level' => 200.00,
            'safety_stock' => 100.00,
            'is_active' => true,
        ]);

        $servoMotor = Product::create([
            'tenant_id' => $tenantA->id,
            'category_id' => $catElectronics->id,
            'uom_id' => $uomPcs->id,
            'sku' => 'RAW-SRV-002',
            'barcode' => '8901234002',
            'name' => 'High-Torque AC Servo Motor 750W',
            'description' => 'Precision brushless servo motor with 23-bit optical encoder',
            'hsn_code' => '8501',
            'product_type' => 'raw_material',
            'purchase_price' => 4200.00,
            'sales_price' => 0.00,
            'gst_rate' => 18.00,
            'reorder_level' => 20.00,
            'safety_stock' => 10.00,
            'is_active' => true,
        ]);

        $mcuController = Product::create([
            'tenant_id' => $tenantA->id,
            'category_id' => $catElectronics->id,
            'uom_id' => $uomPcs->id,
            'sku' => 'RAW-MCU-003',
            'barcode' => '8901234003',
            'name' => 'Industrial CNC Motion Controller Board v4',
            'description' => '6-axis digital EtherCAT motion controller with optoisolated I/O',
            'hsn_code' => '8542',
            'product_type' => 'raw_material',
            'purchase_price' => 12500.00,
            'sales_price' => 0.00,
            'gst_rate' => 18.00,
            'reorder_level' => 15.00,
            'safety_stock' => 5.00,
            'is_active' => true,
        ]);

        $hexBolts = Product::create([
            'tenant_id' => $tenantA->id,
            'category_id' => $catFasteners->id,
            'uom_id' => $uomPcs->id,
            'sku' => 'RAW-BLT-004',
            'barcode' => '8901234004',
            'name' => 'M8x30mm High-Tensile Hex Socket Bolts (Grade 10.9)',
            'description' => 'Black oxide coated high tensile alloy steel socket head cap screws',
            'hsn_code' => '7318',
            'product_type' => 'raw_material',
            'purchase_price' => 14.50,
            'sales_price' => 0.00,
            'gst_rate' => 18.00,
            'reorder_level' => 500.00,
            'safety_stock' => 200.00,
            'is_active' => true,
        ]);

        $cncFg = Product::create([
            'tenant_id' => $tenantA->id,
            'category_id' => $catFinished->id,
            'uom_id' => $uomPcs->id,
            'sku' => 'FG-CNC-500',
            'barcode' => '8901234500',
            'name' => 'Apex 5-Axis Precision CNC Machining Center 500',
            'description' => 'High speed 24,000 RPM spindle CNC cell with automated tool changer',
            'hsn_code' => '8458',
            'product_type' => 'finished_good',
            'purchase_price' => 280000.00,
            'sales_price' => 450000.00,
            'gst_rate' => 18.00,
            'reorder_level' => 2.00,
            'safety_stock' => 1.00,
            'is_active' => true,
        ]);

        $robotArmFg = Product::create([
            'tenant_id' => $tenantA->id,
            'category_id' => $catFinished->id,
            'uom_id' => $uomPcs->id,
            'sku' => 'FG-ROB-010',
            'barcode' => '8901234010',
            'name' => 'Apex 6-Axis Collaborative Robotic Arm X-10',
            'description' => 'Payload 10kg, reach 1300mm with integrated collision detection',
            'hsn_code' => '8479',
            'product_type' => 'finished_good',
            'purchase_price' => 175000.00,
            'sales_price' => 295000.00,
            'gst_rate' => 18.00,
            'reorder_level' => 3.00,
            'safety_stock' => 1.00,
            'is_active' => true,
        ]);

        // J. Stock Levels and Inventory Movements
        $initialStocks = [
            [$steelSheet->id, $whMain->id, 850.00, 480.00],
            [$servoMotor->id, $whMain->id, 65.00, 4200.00],
            [$mcuController->id, $whMain->id, 28.00, 12500.00],
            [$hexBolts->id, $whMain->id, 2500.00, 14.50],
            [$cncFg->id, $whFinished->id, 4.00, 280000.00],
            [$robotArmFg->id, $whFinished->id, 6.00, 175000.00],
        ];

        foreach ($initialStocks as [$prodId, $whId, $qty, $cost]) {
            StockLevel::create([
                'tenant_id' => $tenantA->id,
                'product_id' => $prodId,
                'warehouse_id' => $whId,
                'quantity_on_hand' => $qty,
                'quantity_reserved' => 0,
                'quantity_available' => $qty,
            ]);

            StockMovement::create([
                'tenant_id' => $tenantA->id,
                'product_id' => $prodId,
                'warehouse_id' => $whId,
                'movement_type' => 'adjustment_in',
                'reference_type' => 'opening_balance',
                'reference_id' => 1,
                'quantity' => $qty,
                'unit_cost' => $cost,
                'total_cost' => $qty * $cost,
                'notes' => 'Opening stock verification',
                'created_by' => $adminUserA->id,
            ]);
        }

        // K. Suppliers
        $supplierJindal = Supplier::create([
            'tenant_id' => $tenantA->id,
            'code' => 'SUP-JIN-001',
            'company_name' => 'Jindal Steel & Power Ltd',
            'contact_person' => 'Amitabh Saxena',
            'email' => 'sales@jindalsteel.in',
            'phone' => '+91 22 6677 8899',
            'gstin' => '27AAACJ2234N1Z1', // Maharashtra (Intra-state)
            'pan' => 'AAACJ2234N',
            'state_code' => '27',
            'address' => 'Jindal Center, Nariman Point, Mumbai - 400021',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400021',
            'bank_details' => [
                'bank_name' => 'State Bank of India',
                'account_number' => '10293847561',
                'ifsc_code' => 'SBIN0000300',
                'branch' => 'Nariman Point, Mumbai',
            ],
            'status' => 'active',
        ]);

        $supplierAbb = Supplier::create([
            'tenant_id' => $tenantA->id,
            'code' => 'SUP-ABB-002',
            'company_name' => 'ABB Industrial Automation India Pvt Ltd',
            'contact_person' => 'K. Ramanathan',
            'email' => 'orders@in.abb.com',
            'phone' => '+91 80 4455 6677',
            'gstin' => '29AAACA1122D1Z4', // Karnataka (Inter-state IGST)
            'pan' => 'AAACA1122D',
            'state_code' => '29',
            'address' => 'Plot 5 & 6, Peenya Industrial Area, Bengaluru - 560058',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'pincode' => '560058',
            'bank_details' => [
                'bank_name' => 'Citibank N.A.',
                'account_number' => '50392817441',
                'ifsc_code' => 'CITI0000002',
                'branch' => 'M.G. Road, Bengaluru',
            ],
            'status' => 'active',
        ]);

        // L. Purchase Orders (Procurement)
        $po1 = PurchaseOrder::create([
            'tenant_id' => $tenantA->id,
            'po_number' => 'PO-2026-001',
            'supplier_id' => $supplierJindal->id,
            'warehouse_id' => $whMain->id,
            'order_date' => now()->subDays(15),
            'expected_delivery_date' => now()->subDays(3),
            'status' => 'received',
            'subtotal' => 240000.00,
            'tax_amount' => 43200.00,
            'cgst_amount' => 21600.00,
            'sgst_amount' => 21600.00,
            'igst_amount' => 0.00,
            'total_amount' => 283200.00,
            'notes' => 'Urgent steel delivery for Q3 production run',
            'created_by' => $managerUser->id,
            'approved_by' => $adminUserA->id,
        ]);

        $poItem1 = PurchaseOrderItem::create([
            'purchase_order_id' => $po1->id,
            'product_id' => $steelSheet->id,
            'quantity' => 500.00,
            'unit_price' => 480.00,
            'discount_percent' => 0.00,
            'hsn_code' => '7208',
            'gst_rate' => 18.00,
            'tax_amount' => 43200.00,
            'total_amount' => 283200.00,
            'received_quantity' => 500.00,
        ]);

        // Goods Receipt Note (GRN) for PO1
        $grn1 = GoodsReceiptNote::create([
            'tenant_id' => $tenantA->id,
            'grn_number' => 'GRN-2026-001',
            'purchase_order_id' => $po1->id,
            'warehouse_id' => $whMain->id,
            'receipt_date' => now()->subDays(4),
            'delivery_challan_no' => 'DC-JIN-9941',
            'remarks' => 'Material received in excellent condition with Mill Test Certificate (MTC)',
            'received_by' => $managerUser->id,
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_note_id' => $grn1->id,
            'purchase_order_item_id' => $poItem1->id,
            'product_id' => $steelSheet->id,
            'received_quantity' => 500.00,
            'accepted_quantity' => 500.00,
            'rejected_quantity' => 0.00,
            'batch_number' => 'HEAT-JIN-88912',
        ]);

        // Inter-state PO for Servo Motors (ABB Karnataka -> Maharashtra = IGST)
        $po2 = PurchaseOrder::create([
            'tenant_id' => $tenantA->id,
            'po_number' => 'PO-2026-002',
            'supplier_id' => $supplierAbb->id,
            'warehouse_id' => $whMain->id,
            'order_date' => now()->subDays(5),
            'expected_delivery_date' => now()->addDays(5),
            'status' => 'approved',
            'subtotal' => 126000.00,
            'tax_amount' => 22680.00,
            'cgst_amount' => 0.00,
            'sgst_amount' => 0.00,
            'igst_amount' => 22680.00, // Inter-state
            'total_amount' => 148680.00,
            'notes' => 'Batch of 30 AC Servo Motors for CNC Assembly',
            'created_by' => $managerUser->id,
            'approved_by' => $adminUserA->id,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po2->id,
            'product_id' => $servoMotor->id,
            'quantity' => 30.00,
            'unit_price' => 4200.00,
            'discount_percent' => 0.00,
            'hsn_code' => '8501',
            'gst_rate' => 18.00,
            'tax_amount' => 22680.00,
            'total_amount' => 148680.00,
            'received_quantity' => 0.00,
        ]);

        // M. Bill of Materials (BOM) & Manufacturing
        $bomCnc = BillOfMaterial::create([
            'tenant_id' => $tenantA->id,
            'product_id' => $cncFg->id,
            'bom_number' => 'BOM-CNC-500-v1',
            'version' => '1.0',
            'quantity' => 1.0000,
            'estimated_cost' => 268500.00,
            'routing_instructions' => '1. Sheet metal cutting & bending -> 2. Frame welding & heat treatment -> 3. CNC bed precision grinding -> 4. Spindle & Servo motor mounting -> 5. Motion controller electrical wiring -> 6. 72-hour burn-in laser calibration.',
            'is_active' => true,
        ]);

        BomItem::create(['bom_id' => $bomCnc->id, 'product_id' => $steelSheet->id, 'quantity' => 120.00, 'scrap_percentage' => 3.00, 'unit_cost' => 480.00]);
        BomItem::create(['bom_id' => $bomCnc->id, 'product_id' => $servoMotor->id, 'quantity' => 5.00, 'scrap_percentage' => 0.00, 'unit_cost' => 4200.00]);
        BomItem::create(['bom_id' => $bomCnc->id, 'product_id' => $mcuController->id, 'quantity' => 1.00, 'scrap_percentage' => 0.00, 'unit_cost' => 12500.00]);
        BomItem::create(['bom_id' => $bomCnc->id, 'product_id' => $hexBolts->id, 'quantity' => 200.00, 'scrap_percentage' => 2.00, 'unit_cost' => 14.50]);

        $workCenterMilling = WorkCenter::create([
            'tenant_id' => $tenantA->id,
            'code' => 'WC-MILL-01',
            'name' => 'Heavy CNC Bed Machining Station',
            'hourly_rate' => 850.00,
            'capacity_per_day_hours' => 16.00,
            'is_active' => true,
        ]);

        $workCenterAssembly = WorkCenter::create([
            'tenant_id' => $tenantA->id,
            'code' => 'WC-ASSY-02',
            'name' => 'Robotics & Control Integration Bay',
            'hourly_rate' => 650.00,
            'capacity_per_day_hours' => 8.00,
            'is_active' => true,
        ]);

        // Production Order / Work Order
        $workOrder = ProductionOrder::create([
            'tenant_id' => $tenantA->id,
            'order_number' => 'WO-2026-001',
            'product_id' => $cncFg->id,
            'bom_id' => $bomCnc->id,
            'source_warehouse_id' => $whMain->id,
            'target_warehouse_id' => $whFinished->id,
            'target_quantity' => 2.00,
            'produced_quantity' => 2.00,
            'start_date' => now()->subDays(10),
            'due_date' => now()->subDays(1),
            'status' => 'completed',
            'notes' => 'Batch completed successfully for Mahindra Aerospace contract',
            'created_by' => $managerUser->id,
        ]);

        ProductionMaterialConsumption::create([
            'production_order_id' => $workOrder->id,
            'product_id' => $steelSheet->id,
            'warehouse_id' => $whMain->id,
            'planned_quantity' => 240.00,
            'actual_consumed_quantity' => 245.00,
            'unit_cost' => 480.00,
            'total_cost' => 117600.00,
        ]);

        ProductionMaterialConsumption::create([
            'production_order_id' => $workOrder->id,
            'product_id' => $servoMotor->id,
            'warehouse_id' => $whMain->id,
            'planned_quantity' => 10.00,
            'actual_consumed_quantity' => 10.00,
            'unit_cost' => 4200.00,
            'total_cost' => 42000.00,
        ]);

        // Quality Inspection (QC)
        QualityInspection::create([
            'tenant_id' => $tenantA->id,
            'inspection_number' => 'QC-2026-001',
            'inspectable_type' => ProductionOrder::class,
            'inspectable_id' => $workOrder->id,
            'inspector_id' => $managerUser->id,
            'sample_size' => 2.00,
            'passed_quantity' => 2.00,
            'rejected_quantity' => 0.00,
            'inspection_date' => now()->subDays(1),
            'status' => 'pass',
            'checkpoints' => [
                ['parameter' => 'Positional Accuracy (ISO 230-2)', 'tolerance' => '±0.005 mm', 'observed' => '0.003 mm', 'result' => 'Pass'],
                ['parameter' => 'Spindle Runout at 24k RPM', 'tolerance' => '< 0.002 mm', 'observed' => '0.001 mm', 'result' => 'Pass'],
                ['parameter' => 'Emergency Stop Response Time', 'tolerance' => '< 50 ms', 'observed' => '32 ms', 'result' => 'Pass'],
            ],
            'remarks' => 'Exceeds aerospace calibration benchmarks. Certified for dispatch.',
        ]);

        // N. Customers & CRM
        $customerMahindra = Customer::create([
            'tenant_id' => $tenantA->id,
            'code' => 'CUST-MAH-001',
            'name' => 'Mahindra Aerospace Aerostructures',
            'company_name' => 'Mahindra Aerospace Pvt Ltd',
            'email' => 'procurement@mahindra-aerospace.com',
            'phone' => '+91 22 2846 8800',
            'gstin' => '27AAACM4455P1ZX', // Maharashtra (Intra-state)
            'pan' => 'AAACM4455P',
            'state_code' => '27',
            'billing_address' => 'Mahindra Towers, Road No. 13, Worli, Mumbai - 400018',
            'shipping_address' => 'Aero Park, Narsapura Industrial Area, Kolar Road',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400018',
            'credit_limit' => 2500000.00,
            'status' => 'active',
        ]);

        $customerBhel = Customer::create([
            'tenant_id' => $tenantA->id,
            'code' => 'CUST-BHEL-002',
            'name' => 'Bharat Heavy Electricals Ltd (BHEL)',
            'company_name' => 'Bharat Heavy Electricals Ltd',
            'email' => 'heavy.engg@bhel.in',
            'phone' => '+91 11 6633 1100',
            'gstin' => '07AAACB1100D1Z9', // Delhi (Inter-state IGST)
            'pan' => 'AAACB1100D',
            'state_code' => '07',
            'billing_address' => 'BHEL House, Siri Fort, New Delhi - 110049',
            'shipping_address' => 'Heavy Electrical Plant, Ranipur, Haridwar',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'pincode' => '110049',
            'credit_limit' => 5000000.00,
            'status' => 'active',
        ]);

        Lead::create([
            'tenant_id' => $tenantA->id,
            'title' => 'Supply of 8 Collaborative Robotic Arms for EV Battery Pack Line',
            'contact_name' => 'Dr. S. K. Nambiar',
            'company_name' => 'Ola Electric Technologies',
            'email' => 'snambiar@olaelectric.com',
            'phone' => '+91 80 9988 7766',
            'stage' => 'proposal',
            'expected_value' => 2360000.00,
            'expected_close_date' => now()->addDays(20),
            'assigned_to' => $adminUserA->id,
            'notes' => 'Technical demo completed at Hosur Gigafactory. Proposal under commercial review.',
        ]);

        // O. Sales Orders & Order Fulfillment
        $so1 = SalesOrder::create([
            'tenant_id' => $tenantA->id,
            'so_number' => 'SO-2026-001',
            'customer_id' => $customerMahindra->id,
            'warehouse_id' => $whFinished->id,
            'order_date' => now()->subDays(7),
            'delivery_date' => now()->addDays(3),
            'status' => 'shipped',
            'subtotal' => 450000.00,
            'tax_amount' => 81000.00,
            'cgst_amount' => 40500.00,
            'sgst_amount' => 40500.00,
            'igst_amount' => 0.00, // Intra-state
            'total_amount' => 531000.00,
            'notes' => 'CNC 500 delivery with on-site installation and 1 year AMC included.',
            'created_by' => $adminUserA->id,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so1->id,
            'product_id' => $cncFg->id,
            'quantity' => 1.00,
            'unit_price' => 450000.00,
            'discount_percent' => 0.00,
            'hsn_code' => '8458',
            'gst_rate' => 18.00,
            'tax_amount' => 81000.00,
            'total_amount' => 531000.00,
            'shipped_quantity' => 1.00,
        ]);

        // Delivery Challan & Dispatch Logistics
        DeliveryChallan::create([
            'tenant_id' => $tenantA->id,
            'challan_number' => 'DC-2026-001',
            'sales_order_id' => $so1->id,
            'warehouse_id' => $whFinished->id,
            'vehicle_number' => 'MH-12-RN-8821',
            'driver_name' => 'Santosh Gaikwad',
            'driver_phone' => '+91 98901 22334',
            'eway_bill_number' => '341098227651',
            'dispatch_date' => now()->subDays(2),
            'status' => 'in_transit',
            'notes' => 'Hydraulic air-suspension low-bed trailer used for machine safety.',
            'dispatched_by' => $managerUser->id,
        ]);

        // Tax Invoice (Sales Invoice)
        $inv1 = TaxInvoice::create([
            'tenant_id' => $tenantA->id,
            'invoice_number' => 'INV-2026-001',
            'invoice_type' => 'sales',
            'party_type' => 'customer',
            'party_id' => $customerMahindra->id,
            'sales_order_id' => $so1->id,
            'invoice_date' => now()->subDays(2),
            'due_date' => now()->addDays(28),
            'state_of_supply' => '27',
            'is_interstate' => false,
            'subtotal' => 450000.00,
            'cgst_amount' => 40500.00,
            'sgst_amount' => 40500.00,
            'igst_amount' => 0.00,
            'tax_amount' => 81000.00,
            'total_amount' => 531000.00,
            'paid_amount' => 200000.00,
            'balance_due' => 331000.00,
            'payment_status' => 'partially_paid',
            'gst_breakup' => [
                'cgst_rate' => 9.0,
                'sgst_rate' => 9.0,
                'cgst' => 40500.0,
                'sgst' => 40500.0,
            ],
            'notes' => 'GST Tax Invoice in accordance with Section 31 of CGST Act, 2017',
            'created_by' => $accountantUser->id,
        ]);

        TaxInvoiceItem::create([
            'tax_invoice_id' => $inv1->id,
            'product_id' => $cncFg->id,
            'item_name' => $cncFg->name,
            'hsn_code' => '8458',
            'quantity' => 1.00,
            'unit_price' => 450000.00,
            'discount_percent' => 0.00,
            'gst_rate' => 18.00,
            'taxable_value' => 450000.00,
            'cgst_amount' => 40500.00,
            'sgst_amount' => 40500.00,
            'igst_amount' => 0.00,
            'total_amount' => 531000.00,
        ]);

        // Customer Advance Payment (Payment Record)
        Payment::create([
            'tenant_id' => $tenantA->id,
            'payment_number' => 'REC-2026-001',
            'tax_invoice_id' => $inv1->id,
            'party_type' => 'customer',
            'party_id' => $customerMahindra->id,
            'payment_date' => now()->subDay(),
            'amount' => 200000.00,
            'payment_type' => 'receive',
            'payment_method' => 'bank_transfer',
            'reference_number' => 'NEFT-MAH-9921820',
            'account_id' => $accountMap['1110']->id, // HDFC Bank
            'notes' => 'Advance milestone payment for CNC 500 order',
            'recorded_by' => $accountantUser->id,
        ]);

        // P. Accounting Journal Entries (Double-Entry Verification)
        // Journal Entry 1: Sales Invoice Posting
        $je1 = JournalEntry::create([
            'tenant_id' => $tenantA->id,
            'entry_number' => 'JV-2026-001',
            'entry_date' => now()->subDays(2),
            'reference' => 'INV-2026-001',
            'description' => 'Sales Revenue & Tax recognition for Mahindra Aerospace (INV-2026-001)',
            'source_type' => 'invoice',
            'source_id' => $inv1->id,
            'status' => 'posted',
            'posted_at' => now()->subDays(2),
            'posted_by' => $accountantUser->id,
            'created_by' => $accountantUser->id,
        ]);

        JournalItem::create([
            'journal_entry_id' => $je1->id,
            'account_id' => $accountMap['1200']->id, // Accounts Receivable
            'debit' => 531000.00,
            'credit' => 0.00,
            'description' => 'Receivable from Mahindra Aerospace',
            'party_type' => 'customer',
            'party_id' => $customerMahindra->id,
        ]);

        JournalItem::create([
            'journal_entry_id' => $je1->id,
            'account_id' => $accountMap['4100']->id, // Sales Revenue - Domestic
            'debit' => 0.00,
            'credit' => 450000.00,
            'description' => 'Sales Revenue - CNC 500',
        ]);

        JournalItem::create([
            'journal_entry_id' => $je1->id,
            'account_id' => $accountMap['2200']->id, // Output CGST
            'debit' => 0.00,
            'credit' => 40500.00,
            'description' => 'Output CGST 9%',
        ]);

        JournalItem::create([
            'journal_entry_id' => $je1->id,
            'account_id' => $accountMap['2210']->id, // Output SGST
            'debit' => 0.00,
            'credit' => 40500.00,
            'description' => 'Output SGST 9%',
        ]);

        // Journal Entry 2: Customer Payment Receipt
        $je2 = JournalEntry::create([
            'tenant_id' => $tenantA->id,
            'entry_number' => 'JV-2026-002',
            'entry_date' => now()->subDay(),
            'reference' => 'REC-2026-001',
            'description' => 'Bank receipt from Mahindra Aerospace against INV-2026-001',
            'source_type' => 'payment',
            'source_id' => $inv1->id,
            'status' => 'posted',
            'posted_at' => now()->subDay(),
            'posted_by' => $accountantUser->id,
            'created_by' => $accountantUser->id,
        ]);

        JournalItem::create([
            'journal_entry_id' => $je2->id,
            'account_id' => $accountMap['1110']->id, // HDFC Bank
            'debit' => 200000.00,
            'credit' => 0.00,
            'description' => 'Funds credited via NEFT',
        ]);

        JournalItem::create([
            'journal_entry_id' => $je2->id,
            'account_id' => $accountMap['1200']->id, // Accounts Receivable
            'debit' => 0.00,
            'credit' => 200000.00,
            'description' => 'Receivable reduction for Mahindra Aerospace',
            'party_type' => 'customer',
            'party_id' => $customerMahindra->id,
        ]);

        // Q. HR, Payroll & Employees
        $deptEngg = Department::create(['tenant_id' => $tenantA->id, 'name' => 'Engineering & Robotics R&D']);
        $deptMfg = Department::create(['tenant_id' => $tenantA->id, 'name' => 'Manufacturing & Operations']);
        $deptFinance = Department::create(['tenant_id' => $tenantA->id, 'name' => 'Finance & Legal']);

        $desigCto = Designation::create(['tenant_id' => $tenantA->id, 'title' => 'Chief Technology Officer', 'department_id' => $deptEngg->id]);
        $desigLead = Designation::create(['tenant_id' => $tenantA->id, 'title' => 'Senior Robotics Architect', 'department_id' => $deptEngg->id]);
        $desigPlant = Designation::create(['tenant_id' => $tenantA->id, 'title' => 'Operations Manager', 'department_id' => $deptMfg->id]);
        $desigAcc = Designation::create(['tenant_id' => $tenantA->id, 'title' => 'Head of Accounts', 'department_id' => $deptFinance->id]);

        $emp1 = Employee::create([
            'tenant_id' => $tenantA->id,
            'emp_code' => 'EMP-001',
            'user_id' => $adminUserA->id,
            'department_id' => $deptEngg->id,
            'designation_id' => $desigCto->id,
            'first_name' => 'Rajesh',
            'last_name' => 'Sharma',
            'email' => 'admin@companya.erp.com',
            'phone' => '+91 98220 44556',
            'date_of_birth' => '1984-06-15',
            'date_of_joining' => '2021-04-01',
            'basic_salary' => 90000.00,
            'hra' => 36000.00,
            'special_allowance' => 24000.00,
            'pan' => 'AABPS1234K',
            'aadhar' => '987654321012',
            'pf_number' => 'MH/PUN/0091823/001',
            'esi_number' => '3100982341001',
            'bank_account' => '50100239481',
            'bank_ifsc' => 'HDFC0000120',
            'status' => 'active',
        ]);

        $emp2 = Employee::create([
            'tenant_id' => $tenantA->id,
            'emp_code' => 'EMP-002',
            'user_id' => $accountantUser->id,
            'department_id' => $deptFinance->id,
            'designation_id' => $desigAcc->id,
            'first_name' => 'Pooja',
            'last_name' => 'Kulkarni',
            'email' => 'accountant@companya.erp.com',
            'phone' => '+91 98220 77889',
            'date_of_birth' => '1991-09-22',
            'date_of_joining' => '2022-08-16',
            'basic_salary' => 55000.00,
            'hra' => 22000.00,
            'special_allowance' => 13000.00,
            'pan' => 'CKLPK9981M',
            'aadhar' => '876543210987',
            'pf_number' => 'MH/PUN/0091823/002',
            'esi_number' => '3100982341002',
            'bank_account' => '10293847562',
            'bank_ifsc' => 'SBIN0000450',
            'status' => 'active',
        ]);

        $emp3 = Employee::create([
            'tenant_id' => $tenantA->id,
            'emp_code' => 'EMP-003',
            'user_id' => $managerUser->id,
            'department_id' => $deptMfg->id,
            'designation_id' => $desigPlant->id,
            'first_name' => 'Vikram',
            'last_name' => 'Deshmukh',
            'email' => 'manager@companya.erp.com',
            'phone' => '+91 98220 33221',
            'date_of_birth' => '1987-12-04',
            'date_of_joining' => '2022-01-10',
            'basic_salary' => 65000.00,
            'hra' => 26000.00,
            'special_allowance' => 19000.00,
            'pan' => 'BHDVD4432Q',
            'aadhar' => '765432109876',
            'pf_number' => 'MH/PUN/0091823/003',
            'esi_number' => '3100982341003',
            'bank_account' => '91827364501',
            'bank_ifsc' => 'ICIC0000041',
            'status' => 'active',
        ]);

        // Attendance
        foreach ([$emp1, $emp2, $emp3] as $emp) {
            Attendance::create([
                'tenant_id' => $tenantA->id,
                'employee_id' => $emp->id,
                'date' => now()->toDateString(),
                'status' => 'present',
                'clock_in' => '09:02:00',
                'clock_out' => '18:15:00',
                'notes' => 'Biometric thumbprint logged',
            ]);
        }

        // Leave Application
        LeaveApplication::create([
            'tenant_id' => $tenantA->id,
            'employee_id' => $emp2->id,
            'leave_type' => 'casual',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(6),
            'total_days' => 2.0,
            'reason' => 'Family wedding in Kolhapur',
            'status' => 'approved',
            'approved_by' => $adminUserA->id,
            'actioned_at' => now()->subDay(),
        ]);

        // Monthly Payroll Run
        $payrollRun = PayrollRun::create([
            'tenant_id' => $tenantA->id,
            'month' => (int) now()->subMonth()->format('m'),
            'year' => (int) now()->subMonth()->format('Y'),
            'total_gross' => 350000.00,
            'total_deductions' => 38500.00,
            'total_net' => 311500.00,
            'status' => 'paid',
            'processed_by' => $accountantUser->id,
        ]);

        foreach ([$emp1, $emp2, $emp3] as $emp) {
            $gross = $emp->gross_salary;
            $pf = round($emp->basic_salary * 0.12, 2);
            $pt = 200.00;
            $tds = round($gross * 0.05, 2);
            $totalDed = $pf + $pt + $tds;
            $net = $gross - $totalDed;

            Payslip::create([
                'payroll_run_id' => $payrollRun->id,
                'employee_id' => $emp->id,
                'basic_pay' => $emp->basic_salary,
                'hra' => $emp->hra,
                'special_allowance' => $emp->special_allowance,
                'gross_salary' => $gross,
                'pf_deduction' => $pf,
                'esi_deduction' => 0.00,
                'pt_deduction' => $pt,
                'tds_deduction' => $tds,
                'total_deductions' => $totalDed,
                'net_salary' => $net,
                'payment_status' => 'paid',
            ]);
        }

        // R. Projects & Tasks
        $project1 = Project::create([
            'tenant_id' => $tenantA->id,
            'code' => 'PRJ-2026-AERO',
            'name' => 'Smart Aerospace CNC Automation Cell',
            'customer_id' => $customerMahindra->id,
            'manager_id' => $emp1->id,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(4),
            'budget' => 1500000.00,
            'status' => 'active',
            'description' => 'Development and deployment of customized high-speed 5-axis titanium milling system.',
        ]);

        ProjectTask::create([
            'project_id' => $project1->id,
            'title' => 'Structural Finite Element Analysis (FEA) on Machine Bed',
            'assigned_to' => $emp1->id,
            'start_date' => now()->subDays(20),
            'due_date' => now()->subDays(5),
            'status' => 'completed',
            'priority' => 'high',
            'description' => 'Vibration attenuation verification under 3000 Hz resonance.',
        ]);

        ProjectTask::create([
            'project_id' => $project1->id,
            'title' => 'EtherCAT Fieldbus Master Integration & Testing',
            'assigned_to' => $emp3->id,
            'start_date' => now()->subDays(4),
            'due_date' => now()->addDays(10),
            'status' => 'in_progress',
            'priority' => 'urgent',
            'description' => 'Synchronize 6-axis servos with sub-microsecond cycle jitter.',
        ]);

        // S. Fixed Assets & Depreciation
        $assetCatHeavy = AssetCategory::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Industrial Plant & Heavy Machinery',
            'depreciation_rate' => 15.00,
            'method' => 'straight_line',
        ]);

        Asset::create([
            'tenant_id' => $tenantA->id,
            'category_id' => $assetCatHeavy->id,
            'asset_tag' => 'AST-PLANT-001',
            'name' => 'Haas VF-4SS Super Speed CNC Vertical Machining Center',
            'purchase_date' => '2023-05-10',
            'purchase_cost' => 6200000.00,
            'salvage_value' => 600000.00,
            'current_value' => 3800000.00,
            'location' => 'Bay 3, Bhosari Machine Shop',
            'status' => 'in_use',
            'notes' => 'Annual maintenance contract active with Haas Factory Outlet Pune.',
        ]);

        // T. Multi-Tier Approval Workflows
        ApprovalWorkflow::create([
            'tenant_id' => $tenantA->id,
            'module' => 'purchase_order',
            'min_amount' => 50000.00,
            'max_amount' => 5000000.00,
            'steps' => [
                ['step' => 1, 'role' => 'manager', 'title' => 'Plant Operations Review'],
                ['step' => 2, 'role' => 'admin', 'title' => 'Executive Financial Approval'],
            ],
            'is_active' => true,
        ]);

        ApprovalRequest::create([
            'tenant_id' => $tenantA->id,
            'approvable_type' => PurchaseOrder::class,
            'approvable_id' => $po2->id,
            'current_step' => 2,
            'total_steps' => 2,
            'status' => 'approved',
            'comments' => 'Verified against quarterly servo budget. Approved.',
            'requested_by' => $managerUser->id,
            'actioned_by' => $adminUserA->id,
            'actioned_at' => now()->subDays(4),
        ]);

        // U. Audit Log Trail
        AuditLog::create([
            'tenant_id' => $tenantA->id,
            'user_id' => $adminUserA->id,
            'action' => 'approved',
            'auditable_type' => PurchaseOrder::class,
            'auditable_id' => $po2->id,
            'old_values' => ['status' => 'pending_approval'],
            'new_values' => ['status' => 'approved'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ApexERP',
            'created_at' => now()->subDays(4),
        ]);

        AuditLog::create([
            'tenant_id' => $tenantA->id,
            'user_id' => $accountantUser->id,
            'action' => 'posted',
            'auditable_type' => JournalEntry::class,
            'auditable_id' => $je1->id,
            'old_values' => ['status' => 'draft'],
            'new_values' => ['status' => 'posted'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ApexERP',
            'created_at' => now()->subDays(2),
        ]);
    }
}
