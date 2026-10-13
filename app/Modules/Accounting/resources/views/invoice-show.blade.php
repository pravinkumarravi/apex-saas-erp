@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Tax Invoice #{{ $invoice->invoice_number }}" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('finance.invoices') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-800 dark:hover:text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Invoices
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print / Export PDF
            </button>
            @if($invoice->status !== 'paid')
                <button 
                    type="button" 
                    x-data 
                    @click="$dispatch('open-payment-modal')"
                    class="rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white hover:bg-success-600 transition"
                >
                    Record Payment Receipt
                </button>
            @endif
        </div>
    </div>

    <!-- Official Tax Invoice Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between pb-6 border-b border-gray-200 dark:border-gray-800 gap-4">
            <div>
                <span class="rounded bg-brand-50 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    TAX INVOICE
                </span>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white mt-2">{{ $currentTenant->name ?? 'Apex Enterprise Solutions Ltd' }}</h1>
                <p class="text-xs text-gray-500 mt-1">
                    GSTIN: <strong class="text-gray-800 dark:text-gray-200 font-mono">{{ $currentTenant->gstin ?? '27AAACT0000A1Z1' }}</strong> &bull; State Code: {{ $currentTenant->state_code ?? '27' }}
                </p>
                <p class="text-xs text-gray-400">{{ $currentTenant->address ?? 'Industrial Area Phase 2, Pune, Maharashtra' }}</p>
            </div>
            <div class="sm:text-end text-xs space-y-1">
                <div>Invoice Number: <strong class="text-sm font-mono text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</strong></div>
                <div>Date of Issue: <strong class="text-gray-800 dark:text-gray-200 font-mono">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : '' }}</strong></div>
                <div>Payment Terms: <span class="font-medium text-gray-700 dark:text-gray-300">Net 30 Days</span></div>
                <div class="pt-2">
                    <span class="rounded-full px-3 py-1 text-xs font-bold uppercase {{ $invoice->status === 'paid' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' }}">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- B2B Parties -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-gray-200 dark:border-gray-800 text-xs">
            <div>
                <h3 class="font-bold text-gray-400 uppercase tracking-wider mb-2">Billed To (Consignee):</h3>
                <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $invoice->customer->name ?? 'Consumer' }}</div>
                <div class="mt-1 text-gray-600 dark:text-gray-400">{{ $invoice->customer->address ?? 'Corporate Address' }}</div>
                <div class="text-gray-600 dark:text-gray-400">{{ $invoice->customer->city ? $invoice->customer->city . ', ' : '' }}{{ $invoice->customer->state ?? '' }}</div>
                <div class="mt-2 text-gray-800 dark:text-gray-200 font-mono">
                    GSTIN: <strong>{{ $invoice->customer->gstin ?? 'URP / Consumer' }}</strong>
                </div>
            </div>
            <div class="sm:text-end">
                <h3 class="font-bold text-gray-400 uppercase tracking-wider mb-2">Place of Supply:</h3>
                <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $invoice->customer->state ?? 'Intra-State' }}</div>
                <div class="mt-1 text-gray-500">
                    Tax Treatment: 
                    <strong class="text-brand-600 dark:text-brand-400">
                        {{ $invoice->igst > 0 ? 'Inter-State (IGST Applicable)' : 'Intra-State (CGST + SGST Applicable)' }}
                    </strong>
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="py-6 overflow-x-auto">
            <table class="min-w-full text-xs text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-3 py-2 text-start">#</th>
                        <th class="px-3 py-2 text-start">Product Description</th>
                        <th class="px-3 py-2 text-start">HSN Code</th>
                        <th class="px-3 py-2 text-end">Qty</th>
                        <th class="px-3 py-2 text-end">Rate (₹)</th>
                        <th class="px-3 py-2 text-end">Taxable Value</th>
                        <th class="px-3 py-2 text-end">GST %</th>
                        <th class="px-3 py-2 text-end">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($invoice->items as $idx => $item)
                        <tr>
                            <td class="px-3 py-3 text-gray-400">{{ $idx + 1 }}</td>
                            <td class="px-3 py-3">
                                <span class="font-bold text-gray-900 dark:text-white block">{{ $item->product->name ?? 'Service' }}</span>
                                <span class="text-gray-400 text-[11px] font-mono">{{ $item->product->sku ?? '' }}</span>
                            </td>
                            <td class="px-3 py-3 font-mono text-gray-600 dark:text-gray-400">{{ $item->product->hsn_code ?? '8483' }}</td>
                            <td class="px-3 py-3 text-end font-semibold text-gray-800 dark:text-white">{{ number_format($item->quantity, 0) }}</td>
                            <td class="px-3 py-3 text-end font-mono text-gray-700 dark:text-gray-300">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-3 py-3 text-end font-mono text-gray-800 dark:text-white">₹{{ number_format($item->subtotal, 2) }}</td>
                            <td class="px-3 py-3 text-end font-mono text-gray-600">{{ $item->tax_rate }}%</td>
                            <td class="px-3 py-3 text-end font-mono font-bold text-gray-900 dark:text-white">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Summary & Tax Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-200 dark:border-gray-800 text-xs">
            <div class="space-y-2">
                <h4 class="font-bold uppercase tracking-wider text-gray-400">Electronic Bank Transfer Details:</h4>
                <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50 space-y-1">
                    <div>Bank Name: <strong class="text-gray-800 dark:text-gray-200">HDFC Bank Ltd</strong></div>
                    <div>A/C Number: <strong class="text-gray-800 dark:text-gray-200 font-mono">50200088991122</strong></div>
                    <div>IFSC Code: <strong class="text-gray-800 dark:text-gray-200 font-mono">HDFC0000123</strong></div>
                    <div>Branch: <strong class="text-gray-800 dark:text-gray-200">Koregaon Park, Pune</strong></div>
                </div>
            </div>

            <div class="space-y-2 font-mono">
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Taxable Subtotal:</span>
                    <span class="text-gray-900 dark:text-white">₹{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->igst > 0)
                    <div class="flex justify-between text-brand-600 dark:text-brand-400">
                        <span>Integrated GST (IGST):</span>
                        <span>₹{{ number_format($invoice->igst, 2) }}</span>
                    </div>
                @else
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Central GST (CGST):</span>
                        <span>₹{{ number_format($invoice->cgst, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>State GST (SGST):</span>
                        <span>₹{{ number_format($invoice->sgst, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t border-gray-200 pt-2 text-sm font-bold text-gray-900 dark:text-white dark:border-gray-800">
                    <span>Invoice Grand Total:</span>
                    <span class="text-brand-600 dark:text-brand-400 text-base">₹{{ number_format($invoice->grand_total, 2) }}</span>
                </div>
                <div class="flex justify-between text-success-600 pt-1">
                    <span>Total Amount Paid:</span>
                    <span>-₹{{ number_format($invoice->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-100 pt-1 text-xs font-bold text-error-600">
                    <span>Balance Due:</span>
                    <span>₹{{ number_format(max(0, $invoice->grand_total - $invoice->paid_amount), 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Receipt Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-payment-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Record Payment Receipt</h3>
            <p class="text-xs text-gray-500 mt-1">Accept wire transfer, cheque, or NEFT payment against this invoice.</p>
            
            <form action="{{ route('finance.payments.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="tax_invoice_id" value="{{ $invoice->id }}">
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Receipt Amount (₹) *</label>
                    <input type="number" step="0.01" name="amount" value="{{ max(0, $invoice->grand_total - $invoice->paid_amount) }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method *</label>
                        <select name="payment_method" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="bank_transfer">NEFT / RTGS</option>
                            <option value="upi">UPI / QR</option>
                            <option value="cheque">Bank Cheque</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Date *</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Reference / UTR #</label>
                    <input type="text" name="reference_number" placeholder="UTR-HDFC-99283741" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white hover:bg-success-600">Post Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
