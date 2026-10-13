@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{ 
        openModal: false,
        items: [
            { product_id: '{{ $products->first()?->id ?? 1 }}', quantity: 10, unit_price: {{ $products->first()?->purchase_price ?? 100 }} }
        ],
        addItem() {
            this.items.push({ product_id: '{{ $products->first()?->id ?? 1 }}', quantity: 1, unit_price: 100 });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        }
    }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <x-common.page-breadcrumb pageTitle="Purchase Orders & Procurement" />
            <button @click="openModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-500 text-white text-xs font-semibold hover:bg-brand-600 transition-colors shadow-theme-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Raise Purchase Order
            </button>
        </div>

        <!-- Purchase Orders Table -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Active & Fulfilled Purchase Orders</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Automated Indian GST (CGST/SGST vs IGST) calculations based on supplier state</p>
                </div>
                <div class="text-xs text-gray-400">Total: {{ $purchaseOrders->total() }} Orders</div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/40 text-gray-500 uppercase font-semibold text-[10px]">
                        <tr>
                            <th class="py-3 px-4 text-start">PO Number</th>
                            <th class="py-3 px-4 text-start">Supplier</th>
                            <th class="py-3 px-4 text-start">Receiving WH</th>
                            <th class="py-3 px-4 text-start">Order Date</th>
                            <th class="py-3 px-4 text-end">Subtotal</th>
                            <th class="py-3 px-4 text-end">GST Amount</th>
                            <th class="py-3 px-4 text-end">Total Amount</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse($purchaseOrders as $po)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                <td class="py-3.5 px-4 font-semibold text-brand-600 dark:text-brand-400">{{ $po->po_number }}</td>
                                <td class="py-3.5 px-4">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $po->supplier?->company_name }}</p>
                                    <p class="text-[11px] text-gray-400">State: {{ $po->supplier?->state_code }} · GSTIN: {{ $po->supplier?->gstin ?? 'N/A' }}</p>
                                </td>
                                <td class="py-3.5 px-4">{{ $po->warehouse?->name ?? 'Main Plant' }}</td>
                                <td class="py-3.5 px-4">{{ $po->order_date->format('d M Y') }}</td>
                                <td class="py-3.5 px-4 text-end font-mono">₹{{ number_format($po->subtotal, 2) }}</td>
                                <td class="py-3.5 px-4 text-end font-mono">
                                    <span class="block">₹{{ number_format($po->tax_amount, 2) }}</span>
                                    @if($po->igst_amount > 0)
                                        <span class="text-[10px] text-brand-500 font-sans">IGST</span>
                                    @else
                                        <span class="text-[10px] text-gray-400 font-sans">CGST+SGST</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-end font-mono font-bold text-gray-900 dark:text-white">₹{{ number_format($po->total_amount, 2) }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    @php
                                        $badgeClass = match($po->status) {
                                            'received' => 'bg-success-50 text-success-700 border-success-200',
                                            'partially_received' => 'bg-blue-light-50 text-blue-light-700 border-blue-light-200',
                                            'approved', 'ordered' => 'bg-brand-50 text-brand-700 border-brand-200',
                                            'pending_approval' => 'bg-warning-50 text-warning-700 border-warning-200',
                                            default => 'bg-gray-50 text-gray-700 border-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $badgeClass }}">
                                        {{ str_replace('_', ' ', $po->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($po->status === 'pending_approval')
                                        <form action="/procurement/purchase-orders/{{ $po->id }}/approve" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-xs rounded-lg bg-success-50 text-success-700 hover:bg-success-100 border border-success-200">
                                                Approve
                                            </button>
                                        </form>
                                    @elseif(in_array($po->status, ['approved', 'ordered', 'partially_received']))
                                        <a href="/procurement/goods-receipts" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">
                                            Inward GRN
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs">Completed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-gray-400">No purchase orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $purchaseOrders->links() }}
            </div>
        </div>

        <!-- Create Purchase Order Modal -->
        <div x-show="openModal" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center bg-black/60 p-4">
            <div @click.outside="openModal = false" class="relative w-full max-w-3xl rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Raise New Purchase Order</h3>
                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="/procurement/purchase-orders" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Select Supplier *</label>
                            <select name="supplier_id" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200">
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->company_name }} ({{ $sup->state_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Receiving Warehouse *</label>
                            <select name="warehouse_id" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Order Date *</label>
                            <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200" />
                        </div>
                    </div>

                    <!-- Dynamic Line Items -->
                    <div class="pt-2">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-xs font-semibold uppercase text-gray-500">Order Items & Quantities</h4>
                            <button type="button" @click="addItem()" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">+ Add Another Item</button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                                    <div class="flex-1">
                                        <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" required class="w-full text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-2 px-2.5 text-gray-800 dark:text-gray-200">
                                            @foreach($products as $prod)
                                                <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }}) - GST {{ $prod->gst_rate }}%</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-24">
                                        <input type="number" step="0.01" :name="'items[' + index + '][quantity]'" x-model="item.quantity" placeholder="Qty" required class="w-full text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-2 px-2.5 text-end text-gray-800 dark:text-gray-200" />
                                    </div>
                                    <div class="w-32">
                                        <input type="number" step="0.01" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" placeholder="Price (₹)" required class="w-full text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-2 px-2.5 text-end text-gray-800 dark:text-gray-200 font-mono" />
                                    </div>
                                    <button type="button" @click="removeItem(index)" class="text-error-500 hover:text-error-700 p-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Special Terms / Procurement Notes</label>
                        <textarea name="notes" rows="2" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200" placeholder="Payment terms, delivery schedules, freight terms..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-500 text-xs font-semibold text-white hover:bg-brand-600 transition-colors shadow-theme-xs">Submit Purchase Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
