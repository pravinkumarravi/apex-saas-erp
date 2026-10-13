@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{ openModal: false }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <x-common.page-breadcrumb pageTitle="Product Catalog & Master Data" />
            <button @click="openModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-500 text-white text-xs font-semibold hover:bg-brand-600 transition-colors shadow-theme-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Create Product Item
            </button>
        </div>

        <!-- Product Table -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="/inventory/products" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('type') ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-bold' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400' }}">All Types</a>
                    <a href="/inventory/products?type=raw_material" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('type') === 'raw_material' ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-bold' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400' }}">Raw Materials</a>
                    <a href="/inventory/products?type=finished_good" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('type') === 'finished_good' ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-bold' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400' }}">Finished Goods</a>
                    <a href="/inventory/products?type=work_in_progress" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('type') === 'work_in_progress' ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-bold' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400' }}">WIP Assemblies</a>
                </div>

                <form method="GET" action="/inventory/products" class="relative max-w-xs w-full">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, SKU, HSN..." class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200 placeholder:text-gray-400" />
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/40 text-gray-500 uppercase font-semibold text-[10px]">
                        <tr>
                            <th class="py-3 px-4 text-start">SKU</th>
                            <th class="py-3 px-4 text-start">Item Name</th>
                            <th class="py-3 px-4 text-start">Type / Category</th>
                            <th class="py-3 px-4 text-start">HSN & GST</th>
                            <th class="py-3 px-4 text-end">Purchase Cost</th>
                            <th class="py-3 px-4 text-end">Sales Price</th>
                            <th class="py-3 px-4 text-end">Stock On Hand</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse($products as $p)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                <td class="py-3.5 px-4 font-semibold text-brand-600 dark:text-brand-400">{{ $p->sku }}</td>
                                <td class="py-3.5 px-4">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $p->name }}</p>
                                    <p class="text-[11px] text-gray-400">Barcode: {{ $p->barcode ?? 'N/A' }} · UOM: {{ $p->uom?->code ?? 'PCS' }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ str_replace('_', ' ', $p->product_type) }}
                                    </span>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $p->category?->name ?? 'General' }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-mono text-gray-900 dark:text-white">HSN {{ $p->hsn_code ?? '—' }}</p>
                                    <p class="text-[11px] text-brand-600 dark:text-brand-400 font-semibold">{{ $p->gst_rate }}% GST</p>
                                </td>
                                <td class="py-3.5 px-4 text-end font-mono">₹{{ number_format($p->purchase_price, 2) }}</td>
                                <td class="py-3.5 px-4 text-end font-mono font-semibold">₹{{ number_format($p->sales_price, 2) }}</td>
                                <td class="py-3.5 px-4 text-end font-mono font-bold">
                                    @php
                                        $stockQty = $p->total_stock_on_hand;
                                    @endphp
                                    <span class="{{ $stockQty <= $p->reorder_level ? 'text-error-600' : 'text-success-600' }}">
                                        {{ $stockQty }} {{ $p->uom?->symbol }}
                                    </span>
                                    @if($stockQty <= $p->reorder_level)
                                        <span class="block text-[10px] text-error-500 font-sans font-normal">Reorder alert!</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p->is_active ? 'bg-success-50 text-success-700 border border-success-200' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $p->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-400">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $products->links() }}
            </div>
        </div>

        <!-- Add Product Modal -->
        <div x-show="openModal" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center bg-black/60 p-4">
            <div @click.outside="openModal = false" class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Create New Catalog Product / SKU</h3>
                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="/inventory/products" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Product / Item Name *</label>
                            <input type="text" name="name" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200" placeholder="e.g. Servo Motor 750W" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">SKU Code *</label>
                            <input type="text" name="sku" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200 font-mono" placeholder="e.g. RAW-SRV-005" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Product Type *</label>
                            <select name="product_type" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200">
                                <option value="raw_material">Raw Material / Component</option>
                                <option value="work_in_progress">Work In Progress (Subassembly)</option>
                                <option value="finished_good">Finished Good (Saleable)</option>
                                <option value="consumable">Consumable & Factory Supply</option>
                                <option value="service">Service & Labor</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Category</label>
                            <select name="category_id" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200">
                                <option value="">None</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Unit of Measure (UOM)</label>
                            <select name="uom_id" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200">
                                @foreach($uoms as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">HSN / SAC Code</label>
                            <input type="text" name="hsn_code" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200 font-mono" placeholder="e.g. 8458" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Purchase / Standard Cost (₹) *</label>
                            <input type="number" step="0.01" name="purchase_price" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200 font-mono" placeholder="0.00" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Selling Price (₹) *</label>
                            <input type="number" step="0.01" name="sales_price" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200 font-mono" placeholder="0.00" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">GST Tax Slab (%) *</label>
                            <select name="gst_rate" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200">
                                <option value="0">0% (Nil / Exempt)</option>
                                <option value="5">5%</option>
                                <option value="12">12%</option>
                                <option value="18" selected>18% (Standard Engineering)</option>
                                <option value="28">28% (Luxury / High Slab)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Reorder Level *</label>
                            <input type="number" step="0.01" name="reorder_level" value="10" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200 font-mono" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Safety Stock *</label>
                            <input type="number" step="0.01" name="safety_stock" value="5" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200 font-mono" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Barcode / EAN</label>
                            <input type="text" name="barcode" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200 font-mono" placeholder="e.g. 8901234..." />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Item Description / Technical Specifications</label>
                        <textarea name="description" rows="2" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-500 text-xs font-semibold text-white hover:bg-brand-600 transition-colors shadow-theme-xs">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
