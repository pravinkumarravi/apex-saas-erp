<?php

namespace Modules\Accounting\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface TaxInvoiceRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?TaxInvoice;
    public function findByInvoiceNumber(string $invoiceNumber): ?TaxInvoice;
    public function create(array $data, array $items = []): TaxInvoice;
    public function updateStatus(int $id, string $status): bool;
}
