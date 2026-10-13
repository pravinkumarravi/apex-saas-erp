# Repository Pattern Conventions

## Architecture & Responsibilities

The Repository Pattern isolates data-access and Eloquent querying logic from business workflows.

```text
HTTP Controller / Command / Job
       │
       ▼
Application Action / Service
       │
       ▼
Repository Interface (Contract in Domain\Contracts\ or Application\Contracts\)
       │ [Laravel Service Container Binding in ModuleServiceProvider]
       ▼
Eloquent Repository (Infrastructure\Persistence\Eloquent\Repositories\)
       │
       ▼
Eloquent Model (Domain\Models\)
       │
       ▼
Database
```

## Rules
1. **Interface Placement**: Repository interfaces reside under `Modules\<ModuleName>\Domain\Contracts\` or `Application\Contracts\`.
2. **Implementation Placement**: Concrete Eloquent repositories reside under `Modules\<ModuleName>\Infrastructure\Persistence\Eloquent\Repositories\`.
3. **Container Binding**: Every repository interface is bound to its implementation in the module's `ModuleServiceProvider::register()` method.
4. **No God Repositories**: Repositories must define domain-specific queries (e.g. `findBySku()`, `getAvailableStock()`, `getPostedEntries()`) rather than generic CRUD dumps.
5. **Transactions in Application Layer**: Atomic operations spanning multiple steps or repositories belong in Application Services wrapped in `DB::transaction()`.
