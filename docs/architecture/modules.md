# ERP Modules & Ownership Inventory

## Module Roster and Responsibilities

The ERP functionality is divided into 11 core bounded contexts plus a foundation layer and third-party extension support:

| Module | Namespace | Owned Models | Primary Business Responsibilities |
| :--- | :--- | :--- | :--- |
| **Organization** | `Modules\Organization` | `Tenant`, `Subscription`, `TenantModule` | Multi-tenancy metadata, company profiles, subscription plans, module enablement. |
| **Identity** | `Modules\Identity` | `User`, `Role`, `Permission` | Authentication, Sanctum API tokens, user profiles, RBAC security. |
| **Governance** | `Modules\Governance` | `AuditLog`, `ApprovalWorkflow`, `ApprovalRequest` | Multi-level approval workflows and system-wide immutable audit logging. |
| **Inventory** | `Modules\Inventory` | `Product`, `ProductCategory`, `UnitOfMeasure`, `Warehouse`, `StockLevel`, `StockMovement` | Product catalog, multi-warehouse stock levels, stock movement ledger, adjustments. |
| **Procurement** | `Modules\Procurement` | `Supplier`, `PurchaseOrder`, `PurchaseOrderItem`, `GoodsReceiptNote`, `GoodsReceiptItem` | Vendor management, purchasing lifecycle, goods receipt notes (GRN). |
| **Manufacturing**| `Modules\Manufacturing` | `WorkCenter`, `BillOfMaterial`, `BomItem`, `ProductionOrder`, `ProductionMaterialConsumption`, `QualityInspection` | BOM engineering, work center scheduling, production orders, material consumption, QC inspections. |
| **Sales** | `Modules\Sales` | `Lead`, `Customer`, `SalesOrder`, `SalesOrderItem`, `DeliveryChallan` | CRM pipeline, customer master, sales orders, dispatch challans. |
| **Accounting** | `Modules\Accounting` | `ChartOfAccount`, `FiscalYear`, `JournalEntry`, `JournalItem`, `GstTaxRate`, `TaxInvoice`, `TaxInvoiceItem`, `Payment` | Double-entry general ledger, chart of accounts, Indian GST invoicing, payments, financial statements. |
| **HumanResources**| `Modules\HumanResources` | `Department`, `Designation`, `Employee`, `Attendance`, `LeaveApplication`, `PayrollRun`, `Payslip` | Employee master, daily attendance, leave approvals, monthly payroll processing. |
| **Projects** | `Modules\Projects` | `Project`, `ProjectTask` | Project budgeting, task milestones, resource assignments. |
| **Assets** | `Modules\Assets` | `AssetCategory`, `Asset` | Fixed asset register, depreciation tracking, status lifecycle. |

## Internal Module Directory Layout
Every module follows this uniform structure:
```
modules/<ModuleName>/
├── Providers/
│   └── ModuleServiceProvider.php
├── Domain/
│   ├── Models/
│   ├── Contracts/
│   └── Events/
├── Application/
│   ├── Contracts/
│   ├── DTOs/
│   └── Services/
├── Infrastructure/
│   └── Persistence/
│       └── Eloquent/
│           └── Repositories/
├── Http/
│   ├── Controllers/
│   └── Requests/
└── Routes/
    ├── web.php
    └── api.php
```
