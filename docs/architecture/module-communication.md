# Module-to-Module Communication Patterns

## Communication Mechanisms

Communication across module boundaries is achieved via four explicit patterns:

### 1. Direct Public Contract Invocations (Synchronous RPC)
Used when a module immediately needs a calculation, validation, or transactional action from another module.
- **Example**: `ManufacturingOrderService` calling `StockServiceInterface::issueMaterial($dto)`.
- **Constraint**: Must only consume interfaces from `Modules\<Name>\Application\Contracts\`.

### 2. Domain Events & Decoupled Listeners (Asynchronous / Reactive)
Used when a business event occurs and other modules react independently without the publisher needing to know the consumers.
- **Example**: `Modules\Procurement\Domain\Events\GoodsReceiptApprovedEvent` is dispatched when a GRN is verified. The Inventory module listens to update stock records.
- **Constraint**: Events must pass lightweight scalar IDs or DTOs, not mutable Eloquent entities.

### 3. Data Transfer Objects (DTOs)
Structured, typed contracts representing payloads exchanged across boundaries.
- **Example**:
  ```php
  namespace Modules\Inventory\Application\DTOs;

  final readonly class StockAdjustmentDTO
  {
      public function __construct(
          public int $productId,
          public int $warehouseId,
          public float $quantity,
          public string $type,
          public ?float $unitCost = null,
          public ?string $referenceType = null,
          public ?int $referenceId = null,
          public ?string $notes = null,
          public ?int $userId = null,
      ) {}
  }
  ```

### 4. Cross-Module Orchestration Services
When a complex ERP workflow touches three or more modules (such as: Sales Order -> Stock Reservation -> Invoice Generation -> Approval Workflow):
- An explicit orchestrator coordinates the sequence within a database transaction.
- Each participating module executes its piece via its public contract.
