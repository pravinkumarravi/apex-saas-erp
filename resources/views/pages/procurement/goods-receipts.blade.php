@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{ 
        openModal: false,
        selectedPoId: '{{ $eligiblePOs->first()?->id ?? '' }}'
    }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <x-common.page-breadcrumb pageTitle="Goods Receipt Notes (GRN)" />
            <button @click="openModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-500 text-white text-xs font-semibold hover:bg-brand-600 transition-colors shadow-theme-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Inward Materials via GRN
            </button>
        </div>

        <!-- GRN Table -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Material Inward & Inspection Receipts</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Automated stock increment, batch lot logging, and immutable stock movement ledgers</p>
                </div>
                <div class="text-xs text-gray-400">Total: {{ $goodsReceipts->total() }} GRNs</div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/40 text-gray-500 uppercase font-semibold text-[10px]">
                        <tr>
                            <th class="py-3 px-4 text-start">GRN Number</th>
                            <th class="py-3 px-4 text-start">Purchase Order</th>
                            <th class="py-3 px-4 text-start">Supplier</th>
                            <th class="py-3 px-4 text-start">Warehouse</th>
                            <th class="py-3 px-4 text-start">Receipt Date</th>
                            <th class="py-3 px-4 text-start">Items Received</th>
                            <th class="py-3 px-4 text-start">Received By</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse($goodsReceipts as $grn)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                <td class="py-3.5 px-4 font-semibold text-brand-600 dark:text-brand-400">{{ $grn->grn_number }}</td>
                                <td class="py-3.5 px-4 font-mono font-medium">{{ $grn->purchaseOrder?->po_number }}</td>
                                <td class="py-3.5 px-4">{{ $grn->purchaseOrder?->supplier?->company_name ?? 'Vendor' }}</td>
                                <td class="py-3.5 px-4">{{ $grn->warehouse?->name }}</td>
                                <td class="py-3.5 px-4">{{ $grn->receipt_date->format('d M Y') }}</td>
                                <td class="py-3.5 px-4">
                                    @foreach($grn->items as $item)
                                        <div class="text-[11px]">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $item->product?->name }}:</span>
                                            <span class="text-success-600 font-bold font-mono">{{ $item->accepted_quantity }} accepted</span>
                                            @if($item->rejected_quantity > 0)
                                                <span class="text-error-600 font-bold font-mono">({{ $item->rejected_quantity }} rejected)</span>
                                            @endif
                                            <span class="text-gray-400 font-mono">· Lot: {{ $item->batch_number }}</span>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="py-3.5 px-4">{{ $grn->receiver?->name ?? 'Storekeeper' }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-success-50 text-success-700 border border-success-200 dark:bg-success-950/60 dark:text-success-300 dark:border-success-800">
                                        Received & Verified
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-400">No goods receipt notes recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $goodsReceipts->links() }}
            </div>
        </div>

        <!-- Inward Materials Modal -->
        <div x-show="openModal" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center bg-black/60 p-4">
            <div @click.outside="openModal = false" class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Process Inward Goods Receipt (GRN)</h3>
                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                @if($eligiblePOs->isEmpty())
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500">There are no approved purchase orders awaiting receipt.</p>
                        <p class="text-xs text-gray-400 mt-1">Please raise or approve a Purchase Order first.</p>
                    </div>
                @else
                    <form action="/procurement/goods-receipts" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Select Purchase Order *</label>
                                <select name="purchase_order_id" x-model="selectedPoId" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200">
                                    @foreach($eligiblePOs as $epo)
                                        <option value="{{ $epo->id }}">{{ $epo->po_number }} - {{ $epo->supplier?->company_name }} (₹{{ number_format($epo->total_amount, 2) }})</option>
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
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Receipt Date *</label>
                                <input type="date" name="receipt_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Vendor Delivery Challan / L.R. #</label>
                                <input type="text" name="delivery_challan_no" placeholder="DC-..." class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200" />
                            </div>
                        </div>

                        <!-- PO Items Inward Breakdown -->
                        <div class="pt-2">
                            <h4 class="text-xs font-semibold uppercase text-gray-500 mb-2">PO Items to Inward & Quality Inspection</h4>
                            @foreach($eligiblePOs as $epo)
                                <div x-show="selectedPoId == '{{ $epo->id }}'" class="space-y-3">
                                    @foreach($epo->items as $idx => $pitem)
                                        @php
                                            $pendingQty = max(0, (float) $pitem->quantity - (float) $pitem->received_quantity);
                                        @endphp
                                        <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                                            <input type="hidden" name="items[{{ $idx }}][po_item_id]" value="{{ $pitem->id }}" />
                                            <div class="flex items-center justify-between mb-2">
                                                <div>
                                                    <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $pitem->product?->name }}</span>
                                                    <span class="text-[11px] text-gray-400 ms-2">Ordered: {{ $pitem->quantity }} · Already Recvd: {{ $pitem->received_quantity }} · Pending: {{ $pendingQty }}</span>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                                <div>
                                                    <label class="block text-[10px] text-gray-500 uppercase">Received Qty</label>
                                                    <input type="number" step="0.01" name="items[{{ $idx }}][received_quantity]" value="{{ $pendingQty }}" required class="w-full text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-1.5 px-2 text-end text-gray-800 dark:text-gray-200 font-mono" />
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] text-gray-500 uppercase">Accepted Qty (Stock)</label>
                                                    <input type="number" step="0.01" name="items[{{ $idx }}][accepted_quantity]" value="{{ $pendingQty }}" required class="w-full text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-1.5 px-2 text-end text-success-600 font-mono font-bold" />
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] text-gray-500 uppercase">Rejected Qty</label>
                                                    <input type="number" step="0.01" name="items[{{ $idx }}][rejected_quantity]" value="0" class="w-full text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-1.5 px-2 text-end text-error-600 font-mono" />
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] text-gray-500 uppercase">Batch / Heat #</label>
                                                    <input type="text" name="items[{{ $idx }}][batch_number]" value="LOT-{{ date('ymd') }}-{{ rand(100, 999) }}" class="w-full text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-1.5 px-2 text-gray-800 dark:text-gray-200 font-mono" />
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Receiving Remarks / Certificate of Analysis (CoA)</label>
                            <textarea name="remarks" rows="2" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200" placeholder="Physical inspection remarks, test reports verified..."></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <button type="button" @click="openModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-brand-500 text-xs font-semibold text-white hover:bg-brand-600 transition-colors shadow-theme-xs">Confirm & Inward to Stock</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
