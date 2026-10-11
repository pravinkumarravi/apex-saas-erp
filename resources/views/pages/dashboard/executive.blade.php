@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header & Tenant Profile Banner -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-500 text-white font-bold text-xl shadow-theme-xs">
                        {{ strtoupper(substr($tenant?->name ?? 'A', 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl">{{ $tenant?->name ?? 'Enterprise Central' }}</h1>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-success-50 text-success-700 dark:bg-success-950/60 dark:text-success-300 border border-success-200 dark:border-success-800">Active Tenant</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span>Subdomain: <strong class="text-gray-700 dark:text-gray-300">{{ $tenant?->subdomain }}.erp.com</strong></span>
                            <span>GSTIN: <strong class="text-gray-700 dark:text-gray-300">{{ $tenant?->gstin ?? 'N/A' }}</strong></span>
                            <span>State Code: <strong class="text-gray-700 dark:text-gray-300">{{ $tenant?->state_code }} (Maharashtra)</strong></span>
                            <span>Plan: <strong class="text-brand-600 dark:text-brand-400">{{ $tenant?->plan_name }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="/sales/orders" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-500 text-white text-xs font-medium hover:bg-brand-600 transition-colors shadow-theme-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        New Sales Order
                    </a>
                    <a href="/procurement/purchase-orders" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 text-xs font-medium hover:bg-gray-200 transition-colors">
                        New Purchase Order
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Revenue -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Sales Revenue</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-success-50 dark:bg-success-950/50 text-success-600 dark:text-success-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">₹{{ number_format($totalRevenue, 2) }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Received: ₹{{ number_format($totalPaid, 2) }} · Due: ₹{{ number_format($totalReceivables, 2) }}</p>
                </div>
            </div>

            <!-- Inventory Valuation -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Inventory Valuation</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">₹{{ number_format($inventoryValuation, 2) }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $totalProducts }} Managed SKUs across 2 Warehouses</p>
                </div>
            </div>

            <!-- Active Work Orders -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Manufacturing Work Orders</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $activeWorkOrders }} Active</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">BOM Material Issues & QC Active</p>
                </div>
            </div>

            <!-- Indian GST Net Liability -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Net Indian GST Liability</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-light-50 dark:bg-blue-light-950/50 text-blue-light-600 dark:text-blue-light-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" /></svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">₹{{ number_format($netGstPayable, 2) }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Output: ₹{{ number_format($outputGst, 2) }} | ITC: ₹{{ number_format($inputGst, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Approvals & Low Stock Alerts Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Pending Approvals -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Pending Approval Workflows</h3>
                    <a href="/governance/approvals" class="text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline">View All</a>
                </div>
                @if($pendingApprovals->isEmpty())
                    <p class="text-xs text-gray-400 py-6 text-center">No pending approval requests. All items processed.</p>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($pendingApprovals as $appr)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ class_basename($appr->approvable_type) }} #{{ $appr->approvable_id }}</p>
                                    <p class="text-[11px] text-gray-400">Requested by {{ $appr->requester?->name ?? 'User' }} · Step {{ $appr->current_step }} of {{ $appr->total_steps }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form action="/governance/approvals/{{ $appr->id }}/action" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="px-2.5 py-1 text-xs rounded-lg bg-success-50 text-success-700 hover:bg-success-100 border border-success-200 dark:bg-success-950/60 dark:text-success-300 dark:border-success-800">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="/governance/approvals/{{ $appr->id }}/action" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="px-2.5 py-1 text-xs rounded-lg bg-error-50 text-error-700 hover:bg-error-100 border border-error-200 dark:bg-error-950/60 dark:text-error-300 dark:border-error-800">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Low Stock Alerts -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Critical Stock & Reorder Alerts</h3>
                    <a href="/inventory/stock-levels" class="text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline">Stock Ledger</a>
                </div>
                @if($lowStockItems->isEmpty())
                    <p class="text-xs text-gray-400 py-6 text-center">All inventory stock levels are healthy above safety thresholds.</p>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($lowStockItems as $item)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $item->name }}</p>
                                    <p class="text-[11px] text-gray-400">SKU: {{ $item->sku }} · Reorder Level: {{ $item->reorder_level }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold bg-error-50 text-error-700 dark:bg-error-950/60 dark:text-error-300 border border-error-200 dark:border-error-800">
                                        {{ $item->total_stock_on_hand }} on hand
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Invoices Table -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Recent Tax Invoices & Receivables</h3>
                    <p class="text-xs text-gray-400 mt-0.5">GST Compliant Invoices with HSN and Tax Slabs</p>
                </div>
                <a href="/finance/invoices" class="text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline">View All Invoices</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/40 text-gray-500 uppercase font-semibold text-[10px]">
                        <tr>
                            <th class="py-3 px-4 text-start">Invoice #</th>
                            <th class="py-3 px-4 text-start">Customer / Recipient</th>
                            <th class="py-3 px-4 text-start">Date</th>
                            <th class="py-3 px-4 text-end">Taxable Value</th>
                            <th class="py-3 px-4 text-end">Total Amount</th>
                            <th class="py-3 px-4 text-end">Balance Due</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @foreach($recentInvoices as $inv)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                <td class="py-3 px-4 font-semibold text-brand-600 dark:text-brand-400">{{ $inv->invoice_number }}</td>
                                <td class="py-3 px-4">{{ $inv->customer?->name ?? 'Walk-in' }}</td>
                                <td class="py-3 px-4">{{ $inv->invoice_date->format('d M Y') }}</td>
                                <td class="py-3 px-4 text-end font-mono">₹{{ number_format($inv->subtotal, 2) }}</td>
                                <td class="py-3 px-4 text-end font-mono font-semibold">₹{{ number_format($inv->total_amount, 2) }}</td>
                                <td class="py-3 px-4 text-end font-mono text-error-600">₹{{ number_format($inv->balance_due, 2) }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $inv->payment_status === 'paid' ? 'bg-success-50 text-success-700 border border-success-200' : 'bg-warning-50 text-warning-700 border border-warning-200' }}">
                                        {{ $inv->payment_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="/finance/invoices/{{ $inv->id }}" class="text-xs text-brand-600 hover:underline">View Invoice</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
