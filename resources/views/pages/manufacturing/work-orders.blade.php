@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Shop Floor Work Orders & Production Execution" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Production & Assembly Orders</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Schedule shop floor execution, track real-time yield vs planned target, and trigger automated stock consumption.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-wo-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Schedule Work Order
        </button>
    </div>

    <!-- Production Orders Table -->
    <x-common.component-card title="Active Work Orders">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Order #</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Finished Product</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Hubs (Source &rarr; Target)</th>
                        <th class="px-4 py-3.5 text-center font-semibold text-gray-600 dark:text-gray-300">Progress (Yield)</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Timeline</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($workOrders as $order)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs font-semibold text-brand-600 dark:text-brand-400">
                                {{ $order->order_number }}
                                <span class="block text-gray-400 font-sans font-normal text-[11px]">BOM: {{ $order->bom->bom_number ?? 'Direct' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-medium text-gray-900 dark:text-white block">{{ $order->product->name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $order->product->sku ?? '' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300 font-mono">
                                <div>Src: <span class="font-bold text-gray-900 dark:text-white">{{ $order->sourceWarehouse->code ?? 'WH' }}</span></div>
                                <div>Dst: <span class="font-bold text-brand-600 dark:text-brand-400">{{ $order->targetWarehouse->code ?? 'WH' }}</span></div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @php
                                    $percent = $order->target_quantity > 0 ? min(100, round(($order->produced_quantity / $order->target_quantity) * 100)) : 0;
                                @endphp
                                <div class="w-full max-w-xs mx-auto">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="font-bold text-gray-800 dark:text-white">{{ number_format($order->produced_quantity, 0) }} / {{ number_format($order->target_quantity, 0) }}</span>
                                        <span class="text-gray-500">{{ $percent }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                        <div class="bg-brand-500 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($order->status === 'completed')
                                    <span class="inline-flex rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Completed</span>
                                @elseif($order->status === 'in_progress')
                                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 animate-pulse">In Progress</span>
                                @elseif($order->status === 'planned')
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Planned</span>
                                @else
                                    <span class="inline-flex rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400">
                                <div>Start: {{ $order->start_date ? $order->start_date->format('d M') : 'Pending' }}</div>
                                <div>Due: {{ $order->due_date ? $order->due_date->format('d M Y') : 'Open' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end text-xs font-medium space-x-1">
                                @if($order->status === 'planned')
                                    <form action="{{ route('manufacturing.work-orders.start', $order->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="rounded bg-brand-500 px-2.5 py-1 text-xs text-white hover:bg-brand-600 transition">Start Run</button>
                                    </form>
                                @elseif($order->status === 'in_progress')
                                    <button 
                                        type="button" 
                                        x-data 
                                        @click="$dispatch('open-complete-modal', { id: {{ $order->id }}, order: '{{ $order->order_number }}', planned: {{ $order->target_quantity }} })"
                                        class="rounded bg-success-500 px-2.5 py-1 text-xs text-white hover:bg-success-600 transition"
                                    >
                                        Record Completion
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No shop floor work orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $workOrders->links() }}
        </div>
    </x-common.component-card>

    <!-- Create Work Order Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-wo-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Schedule Work Order</h3>
                <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <form action="{{ route('manufacturing.work-orders.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Manufacturing BOM Recipe *</label>
                    <select name="bom_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Select BOM</option>
                        @foreach($boms as $bom)
                            <option value="{{ $bom->id }}">{{ $bom->bom_number }} - {{ $bom->product->name ?? 'Assembly' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Raw Material Warehouse *</label>
                        <select name="source_warehouse_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">FG Receiving Warehouse *</label>
                        <select name="target_warehouse_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Target Production Units *</label>
                    <input type="number" step="1" name="target_quantity" required value="10" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Scheduled Start Date *</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Create Work Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Complete Modal -->
    <div 
        x-data="{ isOpen: false, orderId: null, orderNum: '', plannedQty: 0 }" 
        x-show="isOpen" 
        @open-complete-modal.window="isOpen = true; orderId = $event.detail.id; orderNum = $event.detail.order; plannedQty = $event.detail.planned"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Complete Work Order Run</h3>
            <p class="text-xs text-gray-500 mt-1">Recording completion will receive finished goods in inventory and post completion movements.</p>
            
            <form :action="'/manufacturing/work-orders/' + orderId + '/complete'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Final Produced Units *</label>
                    <input type="number" step="1" name="produced_quantity" :value="plannedQty" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white hover:bg-success-600">Post Production</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
