@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Quality Control & Inspection Gateways" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Quality Control (QC) & Compliance</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Perform dimensional tolerance checks, defect logging, and pass/fail lot dispositioning.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-qc-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Record Inspection
        </button>
    </div>

    <!-- Inspections Table -->
    <x-common.component-card title="Inspection Audit Records">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Inspection #</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Work Order</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Inspected Qty</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Accepted</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Rejected</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Inspector</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($inspections as $qc)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs font-semibold text-brand-600 dark:text-brand-400">
                                {{ $qc->inspection_number }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs text-gray-700 dark:text-gray-300">
                                {{ $qc->productionOrder->order_number ?? 'Direct Receipt' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-medium text-gray-800 dark:text-white">
                                {{ number_format($qc->quantity_inspected, 0) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-semibold text-success-600 dark:text-success-500">
                                {{ number_format($qc->quantity_passed, 0) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-semibold {{ $qc->quantity_failed > 0 ? 'text-error-600 dark:text-error-500' : 'text-gray-400' }}">
                                {{ number_format($qc->quantity_failed, 0) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($qc->status === 'passed')
                                    <span class="inline-flex rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Passed</span>
                                @elseif($qc->status === 'failed')
                                    <span class="inline-flex rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">Rejected</span>
                                @else
                                    <span class="inline-flex rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">Conditional</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-600 dark:text-gray-300">
                                {{ $qc->inspector->name ?? 'Quality Engineer' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-500 font-mono">
                                {{ $qc->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No quality inspection records logged.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $inspections->links() }}
        </div>
    </x-common.component-card>

    <!-- Record QC Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-qc-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Record Quality Inspection</h3>
                <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <form action="{{ route('manufacturing.quality-control.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Production Work Order *</label>
                    <select name="production_order_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Select Work Order</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->order_number }} - {{ $order->product->name ?? '' }} (Planned: {{ $order->quantity_planned }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Inspected Qty *</label>
                        <input type="number" step="1" name="quantity_inspected" required value="10" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Passed *</label>
                        <input type="number" step="1" name="quantity_passed" required value="10" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Failed</label>
                        <input type="number" step="1" name="quantity_failed" value="0" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Verdict Status *</label>
                    <select name="status" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="passed">Passed (Release to FG Inventory)</option>
                        <option value="failed">Rejected (Move to Quarantine)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Quality Inspection Notes / Caliper Tolerances</label>
                    <textarea name="remarks" rows="2" placeholder="Dimensional variance within ±0.02mm, surface finish acceptable." class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Save QC Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
