@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Analytics & Business Intelligence" />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Product Portfolio & Standard Margins -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Finished Goods Portfolio Margins</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 uppercase font-semibold text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3 text-start">Product</th>
                                <th class="py-2.5 px-3 text-end">Standard Cost</th>
                                <th class="py-2.5 px-3 text-end">Selling Price</th>
                                <th class="py-2.5 px-3 text-end">Gross Margin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($products as $p)
                                @php
                                    $cost = (float) $p->purchase_price;
                                    $price = (float) $p->sales_price;
                                    $margin = $price > 0 ? round((($price - $cost) / $price) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="py-3 px-3 font-medium">{{ $p->name }}</td>
                                    <td class="py-3 px-3 text-end font-mono">₹{{ number_format($cost, 2) }}</td>
                                    <td class="py-3 px-3 text-end font-mono">₹{{ number_format($price, 2) }}</td>
                                    <td class="py-3 px-3 text-end font-mono font-bold text-success-600">{{ $margin }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Strategic Customer Relationships -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Strategic Customer Engagements</h3>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($topCustomers as $c)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ $c->name }}</p>
                                <p class="text-[11px] text-gray-400">GSTIN: {{ $c->gstin }} · Credit Limit: ₹{{ number_format($c->credit_limit, 2) }}</p>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300">
                                {{ $c->sales_orders_count }} Orders Placed
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
