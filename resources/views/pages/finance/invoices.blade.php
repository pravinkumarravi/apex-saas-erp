@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="GST Tax Invoices & Accounts Receivable" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Tax Invoices (GST Compliant)</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Generate B2B GST tax invoices, calculate HSN-level CGST, SGST, IGST, and monitor collection statuses.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-invoice-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Tax Invoice
        </button>
    </div>

    <!-- Invoices Table -->
    <x-common.component-card title="Registered Tax Invoices">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Invoice #</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Client</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Taxable Value</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">GST</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Total Invoice</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Amount Paid</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Payment Status</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="font-mono text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">
                                    {{ $inv->invoice_number }}
                                </a>
                                <span class="block text-gray-400 text-[11px] font-mono">{{ $inv->invoice_date ? $inv->invoice_date->format('d M Y') : '' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-bold text-gray-900 dark:text-white block">{{ $inv->customer->name ?? 'Direct Customer' }}</span>
                                <span class="text-xs text-gray-500 font-mono">GSTIN: {{ $inv->customer->gstin ?? 'Unregistered' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-mono text-xs text-gray-700 dark:text-gray-300">
                                ₹{{ number_format($inv->subtotal, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end text-xs font-mono">
                                ₹{{ number_format($inv->tax_amount, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-mono text-sm font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($inv->grand_total, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-mono text-xs font-semibold text-success-600 dark:text-success-500">
                                ₹{{ number_format($inv->paid_amount, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($inv->status === 'paid')
                                    <span class="inline-flex rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Paid in Full</span>
                                @elseif($inv->status === 'partially_paid')
                                    <span class="inline-flex rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600">Partial</span>
                                @else
                                    <span class="inline-flex rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">Pending</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end text-xs font-medium">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="rounded bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-100 dark:bg-brand-500/15 dark:text-brand-400">
                                    View Tax Invoice
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No tax invoices issued yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $invoices->links() }}
        </div>
    </x-common.component-card>

    <!-- Create Invoice Modal -->
    <div 
        x-data="{ isOpen: false, items: [{ product_id: '', quantity: 1, unit_price: 0, tax_rate: 18 }] }" 
        x-show="isOpen" 
        @open-invoice-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs overflow-y-auto"
    >
        <div @click.away="isOpen = false" class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800 my-8">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Issue Direct GST Tax Invoice</h3>
                <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <form action="{{ route('finance.invoices.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Customer / Consignee *</label>
                        <select name="customer_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->state ?? 'General' }}) - GSTIN: {{ $c->gstin ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Date *</label>
                        <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <!-- Invoice Line Items -->
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Billable Products & Services</label>
                        <button type="button" @click="items.push({ product_id: '', quantity: 1, unit_price: 0, tax_rate: 18 })" class="text-xs text-brand-600 hover:text-brand-700 font-medium">+ Add Line</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex items-center gap-2">
                                <select :name="'items[' + index + '][product_id]'" required class="flex-1 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">Select SKU</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} (HSN: {{ $p->hsn_code ?? 'None' }}) - ₹{{ $p->selling_price }}</option>
                                    @endforeach
                                </select>
                                <input type="number" step="1" :name="'items[' + index + '][quantity]'" x-model="item.quantity" placeholder="Qty" class="w-16 rounded-lg border border-gray-200 px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <input type="number" step="0.01" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" placeholder="Price" class="w-24 rounded-lg border border-gray-200 px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <select :name="'items[' + index + '][tax_rate]'" x-model="item.tax_rate" class="w-20 rounded-lg border border-gray-200 px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="0">0%</option>
                                    <option value="5">5%</option>
                                    <option value="12">12%</option>
                                    <option value="18">18%</option>
                                    <option value="28">28%</option>
                                </select>
                                <button type="button" @click="if(items.length > 1) items.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm">&times;</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Post Tax Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
