@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Inventory Stock Ledger & Movements" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Audit Stock Movements</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Complete immutable transaction log of all inbound receipts, manufacturing issues, and sales dispatches.</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <select name="warehouse_id" onchange="this.form.submit()" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }} ({{ $wh->code }})</option>
                    @endforeach
                </select>
                <select name="type" onchange="this.form.submit()" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                    <option value="">All Types</option>
                    <option value="inward" {{ request('type') == 'inward' ? 'selected' : '' }}>Inward</option>
                    <option value="outward" {{ request('type') == 'outward' ? 'selected' : '' }}>Outward</option>
                    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Movement Ledger Table -->
    <x-common.component-card title="Stock Transaction Journal">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Timestamp</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Product / SKU</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Warehouse</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Movement Type</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Quantity</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Unit Cost</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Reference / Notes</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($movements as $mov)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400 font-mono">
                                {{ $mov->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-medium text-gray-900 dark:text-white block">{{ $mov->product->name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $mov->product->sku ?? '' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-gray-700 dark:text-gray-300">
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-mono dark:bg-gray-800">{{ $mov->warehouse->code ?? 'N/A' }}</span>
                                <span class="ms-1.5 text-xs">{{ $mov->warehouse->name ?? '' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($mov->type === 'inward')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        Inward
                                    </span>
                                @elseif($mov->type === 'outward')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-medium text-orange-600 dark:bg-orange-500/15 dark:text-orange-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        Outward
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-light-50 px-2.5 py-0.5 text-xs font-medium text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                                        {{ ucfirst($mov->type) }}
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-semibold {{ $mov->quantity > 0 ? 'text-success-600 dark:text-success-500' : 'text-error-600 dark:text-error-500' }}">
                                {{ $mov->quantity > 0 ? '+' : '' }}{{ number_format($mov->quantity, 2) }} {{ $mov->product->uom->code ?? '' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end text-xs text-gray-700 dark:text-gray-300 font-mono">
                                ₹{{ number_format($mov->unit_cost, 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-600 dark:text-gray-400">
                                <span class="font-medium text-gray-800 dark:text-gray-200 block">{{ $mov->reference_type ? class_basename($mov->reference_type) . ' #' . $mov->reference_id : 'Direct Adjustment' }}</span>
                                <span class="text-xs text-gray-400">{{ $mov->notes ?? '-' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400">
                                {{ $mov->user->name ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No stock movements recorded matching current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $movements->links() }}
        </div>
    </x-common.component-card>
</div>
@endsection
