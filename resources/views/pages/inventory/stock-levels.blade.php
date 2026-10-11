@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{ openAdjustModal: false }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <x-common.page-breadcrumb pageTitle="Warehouse Stock Balances & Levels" />
            <button @click="openAdjustModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-500 text-white text-xs font-semibold hover:bg-brand-600 transition-colors shadow-theme-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                Stock Adjustment / Scrap
            </button>
        </div>

        <!-- Stock Levels Table -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <form method="GET" action="/inventory/stock-levels" class="flex items-center gap-2">
                        <select name="warehouse_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product..." class="text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200 placeholder:text-gray-400" />
                    </form>
                </div>
                <div class="text-xs text-gray-400">Total Entries: {{ $stockLevels->total() }}</div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/40 text-gray-500 uppercase font-semibold text-[10px]">
                        <tr>
                            <th class="py-3 px-4 text-start">Product Item</th>
                            <th class="py-3 px-4 text-start">SKU</th>
                            <th class="py-3 px-4 text-start">Warehouse Location</th>
                            <th class="py-3 px-4 text-end">On-Hand Qty</th>
                            <th class="py-3 px-4 text-end">Reserved Qty</th>
                            <th class="py-3 px-4 text-end">Available Qty</th>
                            <th class="py-3 px-4 text-end">Inventory Value</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse($stockLevels as $sl)
                            @php
                                $val = $sl->quantity_on_hand * ($sl->product?->purchase_price ?? 0);
                                $isLow = $sl->quantity_available <= ($sl->product?->reorder_level ?? 0);
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                <td class="py-3.5 px-4 font-medium text-gray-900 dark:text-white">{{ $sl->product?->name }}</td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-brand-600 dark:text-brand-400">{{ $sl->product?->sku }}</td>
                                <td class="py-3.5 px-4">{{ $sl->warehouse?->name }}</td>
                                <td class="py-3.5 px-4 text-end font-mono font-bold">{{ $sl->quantity_on_hand }} {{ $sl->product?->uom?->symbol }}</td>
                                <td class="py-3.5 px-4 text-end font-mono text-gray-400">{{ $sl->quantity_reserved }}</td>
                                <td class="py-3.5 px-4 text-end font-mono font-bold {{ $isLow ? 'text-error-600' : 'text-success-600' }}">
                                    {{ $sl->quantity_available }}
                                </td>
                                <td class="py-3.5 px-4 text-end font-mono font-bold">₹{{ number_format($val, 2) }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($isLow)
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-error-50 text-error-700 border border-error-200">
                                            Low Stock
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-success-50 text-success-700 border border-success-200">
                                            Optimal
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-400">No stock records found for this criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $stockLevels->links() }}
            </div>
        </div>

        <!-- Stock Adjustment Modal -->
        <div x-show="openAdjustModal" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center bg-black/60 p-4">
            <div @click.outside="openAdjustModal = false" class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Record Stock Adjustment / Scrap</h3>
                    <button @click="openAdjustModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="/inventory/adjust-stock" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Select Product SKU *</label>
                        <select name="product_id" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200">
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Target Warehouse *</label>
                        <select name="warehouse_id" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Adjustment Type *</label>
                            <select name="type" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200">
                                <option value="adjustment_in">Adjustment In (Stock Increase)</option>
                                <option value="adjustment_out">Adjustment Out (Stock Reduction)</option>
                                <option value="scrap">Scrap / Write-off</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Quantity *</label>
                            <input type="number" step="0.0001" name="quantity" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200 font-mono" placeholder="Qty" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Reason / Physical Audit Reference *</label>
                        <input type="text" name="notes" required placeholder="e.g. Annual stock audit reconciliation variance" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openAdjustModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-500 text-xs font-semibold text-white hover:bg-brand-600 transition-colors shadow-theme-xs">Submit Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
