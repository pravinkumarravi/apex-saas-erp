<?php

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Contracts\TaxInvoice;
use Modules\Accounting\Domain\Contracts\TaxInvoiceRepositoryInterface;
use Modules\Accounting\Domain\Models\TaxInvoiceProxy;
use Modules\Accounting\Domain\Models\TaxInvoiceItemProxy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentTaxInvoiceRepository implements TaxInvoiceRepositoryInterface
{
    public function all(): Collection
    {
        return TaxInvoiceProxy::with('items')->get();
    }

    public function find(int $id): ?TaxInvoice
    {
        return TaxInvoiceProxy::with('items')->find($id);
    }

    public function findByInvoiceNumber(string $invoiceNumber): ?TaxInvoice
    {
        return TaxInvoiceProxy::where('invoice_number', $invoiceNumber)->with('items')->first();
    }

    public function create(array $data, array $items = []): TaxInvoice
    {
        return DB::transaction(function () use ($data, $items) {
            $invoice = TaxInvoiceProxy::create($data);
            foreach ($items as $item) {
                $item['tax_invoice_id'] = $invoice->id;
                TaxInvoiceItemProxy::create($item);
            }
            return $invoice->load('items');
        });
    }

    public function updateStatus(int $id, string $status): bool
    {
        $invoice = $this->find($id);
        return $invoice ? $invoice->update(['payment_status' => $status]) : false;
    }
}
