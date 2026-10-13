@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Payment Receipts & Cash Book Ledger" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Customer Payments & Banking Receipts</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Record customer settlements, bank transactions (NEFT, RTGS, Cheque), and reconcile Accounts Receivable.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-payment-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Record Payment Receipt
        </button>
    </div>

    <!-- Payments Ledger Table -->
    <x-common.component-card title="Payment Receipts Journal">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Payment #</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Invoice Reference</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Customer</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Payment Method</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Bank / GL Account</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Amount Received</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($payments as $p)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs font-semibold text-brand-600 dark:text-brand-400">
                                {{ $p->payment_number }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs text-gray-700 dark:text-gray-300">
                                <a href="{{ route('finance.invoices.show', $p->tax_invoice_id) }}" class="hover:underline text-brand-500">
                                    {{ $p->invoice->invoice_number ?? 'Direct' }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-medium text-gray-900 dark:text-white block">{{ $p->invoice->customer->name ?? 'Customer' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-mono uppercase dark:bg-gray-800">{{ str_replace('_', ' ', $p->payment_method) }}</span>
                                <span class="text-xs text-gray-400 block font-mono mt-0.5">{{ $p->reference_number ?? '' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-600 dark:text-gray-300">
                                {{ $p->account->name ?? 'Primary Bank Account' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-mono text-sm font-bold text-success-600 dark:text-success-500">
                                ₹{{ number_format($p->amount, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-500 font-mono">
                                {{ $p->payment_date ? $p->payment_date->format('d M Y') : $p->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No payment receipts recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    </x-common.component-card>

    <!-- Create Payment Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-payment-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Record Payment Receipt</h3>
            <p class="text-xs text-gray-500 mt-1">Accept settlement and credit bank ledger.</p>
            
            <form action="{{ route('finance.payments.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Reference *</label>
                    <select name="tax_invoice_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Select Invoice</option>
                        @foreach($invoices as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->invoice_number }} - {{ $inv->customer->name ?? '' }} (Total: ₹{{ number_format($inv->grand_total, 0) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Deposit To Bank Account</label>
                    <select name="account_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @foreach($bankAccounts as $ba)
                            <option value="{{ $ba->id }}">{{ $ba->code }} - {{ $ba->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Receipt Amount (₹) *</label>
                    <input type="number" step="0.01" name="amount" required placeholder="50000.00" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Method *</label>
                        <select name="payment_method" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="bank_transfer">NEFT / RTGS</option>
                            <option value="upi">UPI / QR</option>
                            <option value="cheque">Bank Cheque</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date *</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Reference / UTR #</label>
                    <input type="text" name="reference_number" placeholder="UTR-998822" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Post Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
