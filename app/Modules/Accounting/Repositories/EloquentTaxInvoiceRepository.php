<?php

namespace Modules\Accounting\Repositories;

use Modules\Accounting\Contracts\TaxInvoiceRepositoryInterface;
use Modules\Accounting\Models\TaxInvoice;
use Modules\Accounting\Models\TaxInvoiceItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentTaxInvoiceRepository implements TaxInvoiceRepositoryInterface
{
    public function all(): Collection
    {
        return TaxInvoice::with('items')->get();
    }

    public function find(int $id): ?TaxInvoice
    {
        return TaxInvoice::with('items')->find($id);
    }

    public function findByInvoiceNumber(string $invoiceNumber): ?TaxInvoice
    {
        return TaxInvoice::where('invoice_number', $invoiceNumber)->with('items')->first();
    }

    public function create(array $data, array $items = []): TaxInvoice
    {
        return DB::transaction(function () use ($data, $items) {
            $invoice = TaxInvoice::create($data);
            foreach ($items as $item) {
                $item['tax_invoice_id'] = $invoice->id;
                TaxInvoiceItem::create($item);
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
