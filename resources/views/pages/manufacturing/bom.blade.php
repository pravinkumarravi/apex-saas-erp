@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Bills of Material (BOM) & Engineering Recipes" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Multi-Level Manufacturing BOMs</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Define raw material requirements, scrap tolerances, and assembly structures for finished products.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-bom-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Engineering BOM
        </button>
    </div>

    <!-- BOM List -->
    <div class="space-y-4">
        @forelse($boms as $bom)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-500/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded bg-brand-50 px-2 py-0.5 text-xs font-mono font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                    {{ $bom->bom_number }}
                                </span>
                                <span class="text-xs text-gray-500 font-medium">Rev. {{ $bom->version }}</span>
                                @if($bom->is_active)
                                    <span class="rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Active</span>
                                @endif
                            </div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white mt-1">
                                {{ $bom->product->name ?? 'Finished Good' }} 
                                <span class="text-xs font-normal text-gray-500">({{ $bom->product->sku ?? '' }})</span>
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-gray-600 dark:text-gray-300">
                        <div>
                            <span class="text-gray-400 block">Base Batch Output</span>
                            <span class="font-bold text-sm text-gray-800 dark:text-white">{{ number_format($bom->quantity, 0) }} {{ $bom->product->uom->code ?? 'Units' }}</span>
                        </div>
                        <div class="border-s border-gray-200 ps-4 dark:border-gray-800">
                            <span class="text-gray-400 block">Estimated Unit Cost</span>
                            <span class="font-bold text-sm text-brand-600 dark:text-brand-400">₹{{ number_format($bom->total_cost, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Recipe Items Breakdown -->
                <div class="mt-4">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Required Bill of Materials Components</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs text-start">
                            <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500">
                                <tr>
                                    <th class="px-3 py-2 text-start">Component / Raw Material</th>
                                    <th class="px-3 py-2 text-start">SKU</th>
                                    <th class="px-3 py-2 text-end">Quantity Required</th>
                                    <th class="px-3 py-2 text-end">Est. Unit Cost</th>
                                    <th class="px-3 py-2 text-end">Component Total</th>
                                    <th class="px-3 py-2 text-end">Scrap %</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($bom->items as $item)
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-gray-800 dark:text-white">{{ $item->product->name ?? 'Material' }}</td>
                                        <td class="px-3 py-2 text-gray-500 font-mono">{{ $item->product->sku ?? '' }}</td>
                                        <td class="px-3 py-2 text-end font-semibold text-gray-700 dark:text-gray-300">
                                            {{ number_format($item->quantity, 2) }} {{ $item->product->uom->code ?? '' }}
                                        </td>
                                        <td class="px-3 py-2 text-end text-gray-600 dark:text-gray-400">₹{{ number_format($item->unit_cost, 2) }}</td>
                                        <td class="px-3 py-2 text-end font-bold text-gray-900 dark:text-white">
                                            ₹{{ number_format($item->quantity * $item->unit_cost, 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-end text-gray-500">{{ $item->scrap_percentage ? $item->scrap_percentage . '%' : '0%' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-4 text-center text-gray-400">No components defined for this recipe.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-gray-500 dark:text-gray-400">No manufacturing recipes or BOMs registered.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $boms->links() }}
    </div>

    <!-- Create BOM Modal -->
    <div 
        x-data="{ isOpen: false, items: [{ product_id: '', quantity: 1, unit_cost: 0 }] }" 
        x-show="isOpen" 
        @open-bom-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs overflow-y-auto"
    >
        <div @click.away="isOpen = false" class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800 my-8">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">New Engineering BOM Formula</h3>
                <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
            </div>
            
            <form action="{{ route('manufacturing.bom.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Finished Assembly Product *</label>
                        <select name="product_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select Finished Good</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Revision / Version</label>
                        <input type="text" name="version" value="1.0" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Batch Output Quantity</label>
                    <input type="number" step="0.01" name="quantity" value="1.00" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <!-- Raw Material Items -->
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Raw Material Components</label>
                        <button type="button" @click="items.push({ product_id: '', quantity: 1, unit_cost: 0 })" class="text-xs text-brand-600 hover:text-brand-700 font-medium">+ Add Component</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex items-center gap-2">
                                <select :name="'items[' + index + '][product_id]'" required class="flex-1 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">Select Raw Material</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                    @endforeach
                                </select>
                                <input type="number" step="0.01" :name="'items[' + index + '][quantity]'" x-model="item.quantity" placeholder="Qty" class="w-20 rounded-lg border border-gray-200 px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <input type="number" step="0.01" :name="'items[' + index + '][unit_cost]'" x-model="item.unit_cost" placeholder="Unit Cost" class="w-24 rounded-lg border border-gray-200 px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <button type="button" @click="if(items.length > 1) items.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm">&times;</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Save BOM Formula</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
