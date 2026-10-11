# ApexERP — Multi-Tenant Modular SaaS ERP Application

[![Laravel 13](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS v4](https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Livewire v4](https://img.shields.io/badge/Livewire-v4-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

> A production-ready, multi-tenant enterprise resource planning (SaaS ERP) application engineered with **Laravel 13**, **MariaDB/MySQL**, **Tailwind CSS v4**, **Livewire v4**, **Blade**, **Alpine.js v3**, and a **WordPress-like ZIP installable module engine**.

---

## 🌟 Key Features

### 🏢 1. Multi-Tenancy & Domain-Driven Design (DDD)
- **Subdomain Routing**: Dynamic tenant resolution (`companya.erp.com`, `companyb.erp.com`, or `?tenant=companya`).
- **Strict Data Isolation**: Automatic query isolation enforced at the Eloquent and database level via `BelongsToTenant` and `TenantScope`.
- **Subscription Management**: Tenant seat allocations, feature gating, NVMe storage caps, and billing cycles.

### 📦 2. Extensible Modular Architecture & ZIP Plugin Engine
- **Modular Repository Pattern**: Standardized `RepositoryInterface` and `BaseRepository` decoupling queries from business logic.
- **WordPress-Style Module System**: External modules located in root `modules/` with `module.json` manifests.
- **Dynamic Navigation Registration**: Newly installed modules inject their own sidebar menu, pages, routes, views, and migrations without modifying core code.
- **ZIP Package Installer**: Upload `.zip` plugins via the web UI (`/settings/modules`) or CLI (`php artisan module:install`).
- **Module Generator**: Scaffold new modules instantly with `php artisan module:make <Name>`.
- **Hook & Event Bus**: WordPress-style action and filter hooks (`Hook::addAction`, `Hook::applyFilter`) for seamless inter-module communication.

### 🏭 3. Manufacturing & MRP Execution
- **Multi-Level Bills of Material (BOM)**: Engineering recipes with raw materials and scrap percentage tolerances.
- **Shop-Floor Work Orders**: Order scheduling with raw material availability checks, automatic stock deduction on start, and finished good receipt on completion.
- **Material Consumption Tracking**: Compare actual shop-floor usage against BOM expectations to prevent scrap variance.
- **Quality Control (QC)**: Caliper tolerance checks, lot dispositioning, and defect logging.

### 💰 4. Finance, Double-Entry Accounting & Indian GST
- **General Ledger**: Strict double-entry balance equality ($\sum \text{Debit} = \sum \text{Credit}$).
- **Indian GST Engine**:
  - Intra-state ($50\%$ CGST + $50\%$ SGST)
  - Inter-state ($100\%$ IGST)
- **Statutory Return Filing**: B2B schedule table for **GSTR-1** (Table 4A) and summary calculations for **GSTR-3B** (Outward Liability vs. Inward ITC).
- **Financial Statements**: Balance Sheet and Profit & Loss (P&L) statements.

### 📦 5. Inventory & Multi-Warehouse Management
- Central warehouses, shop-floor staging racks, and quarantine zones.
- Real-time SKU stock levels (on-hand, reserved, available).
- Immutable stock transaction journal recording inward receipts, production issues, and sales dispatches.

### 🛒 6. Procurement & Vendor Management
- Supplier master directory with GSTIN tracking.
- 3-way matching Purchase Orders (PO).
- Goods Receipt Notes (GRN) with automated stock increases and ledger entries.

### 🤝 7. CRM, Sales & Logistics
- Sales opportunity leads pipeline with one-click customer conversion.
- Sales order contracts with dynamic Indian GST tax line calculation.
- Delivery Challans (E-Way bills, transporter names, vehicle registrations).
- GST Tax Invoices and customer payment receipts with bank ledger reconciliation.

### 👥 8. HR & Indian Statutory Payroll
- Staff master directory with PF UAN and PAN tax identification.
- Biometric check-in/out attendance logging and shift timesheets.
- Leave requests with manager approval workflows.
- Monthly payroll runs with automated statutory deductions (**PF 12%**, **PT ₹200**, **TDS**) and automatic General Ledger posting.

### 🛡️ 9. Governance, RBAC & Mobile REST APIs
- Multi-tier approval workflows for purchase orders and expenditures.
- Immutable audit trail logs for all entity mutations.
- Granular Role-Based Access Control (RBAC).
- OAuth 2.0 / Laravel Sanctum Bearer token REST API endpoints for future iOS & Android mobile applications.

---

## 🛠️ Technology Stack

| Layer | Technology |
| :--- | :--- |
| **Backend Framework** | Laravel 13.x |
| **Language Runtime** | PHP 8.4+ |
| **Database** | MariaDB 12+ / MySQL 8.0+ |
| **CSS Engine** | Tailwind CSS v4 (configured via `@theme` in `app.css`) |
| **Frontend Reactivity**| Livewire v4 & Alpine.js v3 |
| **Asset Pipeline** | Vite 7 (`@tailwindcss/vite`) |
| **Authentication** | Laravel Sanctum |
| **Testing** | Pest PHP & PHPUnit (12 passed, 50 assertions) |

---

## 🚀 Quick Start & Installation

### 1. Clone the Repository
```bash
git clone https://github.com/pravinkumarravi/apex-saas-erp.git
cd apex-saas-erp
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure your MariaDB/MySQL credentials in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_saas
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Migrations & Seeders
```bash
php artisan migrate --seed
```

### 5. Compile Frontend Assets
```bash
npm run build
```

### 6. Run the Application
```bash
php artisan serve
```

---

## 🔑 Demo Tenants & Credentials

The database seeder pre-configures two enterprise organizations:

### Tenant A: Tata Precision Engineering Ltd (Maharashtra, GST: 27)
- **URL**: `http://localhost:8000/?tenant=companya`
- **Email**: `admin@companya.erp.com`
- **Password**: `password`

### Tenant B: Zenith Robotics Technologies (Karnataka, GST: 29)
- **URL**: `http://localhost:8000/?tenant=companyb`
- **Email**: `admin@companyb.erp.com`
- **Password**: `password`

---

## 🧩 Working With Modules

### Listing Discovered Modules
```bash
php artisan module:list
```

### Scaffolding a New Module
```bash
php artisan module:make BarcodeScanner
```

### Installing an External Module from ZIP
```bash
php artisan module:install /path/to/my-module.zip
```
Or upload via the Web UI at **Administration &rarr; Modular Plugin Manager** (`/settings/modules`).

---

## 🧪 Running Automated Tests

Run the comprehensive Pest test suite:
```bash
php artisan test
```

```
   PASS  Tests\Unit\ExampleTest
   PASS  Tests\Feature\ApiSanctumTest
   PASS  Tests\Feature\ExampleTest
   PASS  Tests\Feature\GstAndAccountingTest
   PASS  Tests\Feature\ManufacturingWorkflowTest
   PASS  Tests\Feature\ModularPluginSystemTest
   PASS  Tests\Feature\TenancyIsolationTest

  Tests:    12 passed (50 assertions)
  Duration: 1.00s
```

---

## 📄 License
This application is open-sourced software licensed under the [MIT license](LICENSE).
