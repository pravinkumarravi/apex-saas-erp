# Modular Monolith Architecture Overview

## Executive Summary
This document establishes the architecture for the **ApexERP** platform refactored as a **Modular Monolith** running on **Laravel 13** and **PHP 8.4**.

The application transitions from an initial monolithic structure (where controllers directly manipulated Eloquent models across domain lines) to a clean, cohesive, and decoupled modular monolith using:
- **Konekt Concord 1.18+** as the formal module lifecycle manager and module service provider registry.
- **Explicit Domain Bounded Contexts** residing under `modules/<ModuleName>/`.
- **Repository Pattern & Dependency Inversion** for persistence operations, guaranteeing swappable and testable data contracts.
- **Inter-Module Public Contracts & DTOs** preventing circular dependencies and ensuring a Directed Acyclic Graph (DAG).
- **Domain Events & Application Orchestrators** for cross-module multi-step ERP workflows (e.g., manufacturing consumption, procurement delivery, payroll accounting).

## Architectural Principles
1. **Single Deployable Unit**: The system remains one unified Laravel application sharing one database connection, one container, and one deployment pipeline.
2. **Clear Bounded Contexts**: Every business entity is strictly owned by exactly one module. Other modules cannot write directly to another module's database tables or internal Eloquent models.
3. **Directed Acyclic Graph (DAG)**: Cross-module dependencies are strictly one-way. Module A may consume Module B's public contracts, but Module B never references Module A.
4. **Dependency Inversion**: High-level modules define and consume repository interfaces and service contracts; low-level infrastructure implements them.
5. **Preservation of Business Logic & Tenancy**: Multitenancy (`TenantContext`, `BelongsToTenant`, `TenantScope`), API Sanctum tokens, UI Blade templates, and existing database migrations are 100% preserved.
