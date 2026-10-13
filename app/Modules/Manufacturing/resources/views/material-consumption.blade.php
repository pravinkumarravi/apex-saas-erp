@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Material Consumption Ledger & Issue Slips" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Shop Floor Raw Material Consumption</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track actual materials issued and consumed against planned BOM expectations to detect scrap variance.</p>
        </div>
    </div>

    <!-- Consumption Table -->
    <x-common.component-card title="Recorded Raw Material Issues">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Timestamp</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Work Order #</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Raw Material SKU</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Source Warehouse</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Planned Qty</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Actual Consumed</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Unit Cost</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Total Material Cost</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($consumptions as $c)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400 font-mono">
                                {{ $c->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs font-semibold text-brand-600 dark:text-brand-400">
                                {{ $c->productionOrder->order_number ?? 'WO-N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-medium text-gray-900 dark:text-white block">{{ $c->product->name ?? 'Material' }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $c->product->sku ?? '' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300">
                                <span class="rounded bg-gray-100 px-2 py-0.5 font-mono dark:bg-gray-800">{{ $c->warehouse->code ?? 'WH' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end text-xs text-gray-500">
                                {{ number_format($c->quantity_planned, 2) }} {{ $c->product->uom->code ?? '' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-semibold text-orange-600 dark:text-orange-400">
                                {{ number_format($c->quantity_consumed, 2) }} {{ $c->product->uom->code ?? '' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end text-xs font-mono text-gray-700 dark:text-gray-300">
                                ₹{{ number_format($c->unit_cost, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($c->quantity_consumed * $c->unit_cost, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No raw material consumption entries recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $consumptions->links() }}
        </div>
    </x-common.component-card>
</div>
@endsection
