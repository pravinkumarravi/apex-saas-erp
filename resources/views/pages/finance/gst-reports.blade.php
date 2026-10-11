@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Indian GST Compliance & Return Filing (GSTR-1 / GSTR-3B)" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Goods & Services Tax Returns</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Automated Indian GST reconciliation. Export B2B invoice tables and compute monthly tax liabilities.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-mono font-bold text-gray-800 dark:bg-gray-800 dark:text-white">
                GSTIN: {{ $tenantGstin ?? 'Unregistered' }} (State: {{ $tenantState ?? '27' }})
            </span>
            <button onclick="window.print()" class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-medium text-white hover:bg-brand-600 transition">
                Export Filing JSON / Print
            </button>
        </div>
    </div>

    <!-- GSTR-3B Monthly Return Summary -->
    <x-common.component-card title="GSTR-3B Summary Return Computation (Current Fiscal Month)">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                <span class="text-xs text-gray-500 block">3.1 Outward Taxable Supplies</span>
                <span class="text-lg font-bold font-mono text-gray-900 dark:text-white mt-1 block">₹{{ number_format($gstr3b['outward_taxable'], 2) }}</span>
                <span class="text-[11px] text-gray-400">Total B2B & B2C Revenue</span>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                <span class="text-xs text-gray-500 block">Outward Tax Liability (Output GST)</span>
                <span class="text-lg font-bold font-mono text-error-600 dark:text-error-500 mt-1 block">₹{{ number_format($gstr3b['outward_tax'], 2) }}</span>
                <span class="text-[11px] text-gray-400">CGST + SGST + IGST</span>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                <span class="text-xs text-gray-500 block">4. Eligible ITC (Input Tax Credit)</span>
                <span class="text-lg font-bold font-mono text-success-600 dark:text-success-500 mt-1 block">₹{{ number_format($gstr3b['itc_available'], 2) }}</span>
                <span class="text-[11px] text-gray-400">From Inward Supplier POs</span>
            </div>
            <div class="rounded-xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-500/20 dark:bg-brand-500/10">
                <span class="text-xs text-brand-700 dark:text-brand-300 block font-semibold">5. Net GST Payable in Cash</span>
                <span class="text-xl font-black font-mono text-brand-600 dark:text-brand-400 mt-1 block">₹{{ number_format($gstr3b['net_payable'], 2) }}</span>
                <span class="text-[11px] text-brand-500/80">Electronic Cash Ledger Payment</span>
            </div>
        </div>
    </x-common.component-card>

    <!-- GSTR-1 B2B Invoices Section -->
    <x-common.component-card title="GSTR-1 Table 4A: B2B Inward/Outward Tax Invoices">
        <div class="flex items-center justify-between pb-4 text-xs font-mono">
            <div>
                Total Taxable Value: <strong class="text-gray-900 dark:text-white">₹{{ number_format($gstr1['total_taxable'], 2) }}</strong>
            </div>
            <div class="flex gap-4">
                <span>CGST: <strong class="text-gray-800 dark:text-gray-200">₹{{ number_format($gstr1['total_cgst'], 2) }}</strong></span>
                <span>SGST: <strong class="text-gray-800 dark:text-gray-200">₹{{ number_format($gstr1['total_sgst'], 2) }}</strong></span>
                <span>IGST: <strong class="text-gray-800 dark:text-gray-200">₹{{ number_format($gstr1['total_igst'], 2) }}</strong></span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Buyer GSTIN</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Customer Legal Name</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Invoice #</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Invoice Date</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Taxable Value</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">CGST</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">SGST</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">IGST</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Invoice Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($gstr1['b2b_invoices'] as $inv)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition text-xs font-mono">
                            <td class="whitespace-nowrap px-4 py-3 text-brand-600 dark:text-brand-400 font-bold">
                                {{ $inv->customer->gstin ?? 'URP' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-sans text-gray-800 dark:text-gray-200">
                                {{ $inv->customer->name ?? 'Customer' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                {{ $inv->invoice_number }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-500">
                                {{ $inv->invoice_date ? $inv->invoice_date->format('d-m-Y') : '' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end text-gray-800 dark:text-gray-200">
                                ₹{{ number_format($inv->subtotal, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end text-gray-600 dark:text-gray-400">
                                ₹{{ number_format($inv->cgst, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end text-gray-600 dark:text-gray-400">
                                ₹{{ number_format($inv->sgst, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end text-brand-600 dark:text-brand-400">
                                ₹{{ number_format($inv->igst, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($inv->grand_total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500 font-sans">
                                No B2B outward supplies registered for this GST return period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>
@endsection
