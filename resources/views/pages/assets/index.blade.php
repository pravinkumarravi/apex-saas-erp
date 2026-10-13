@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Fixed Asset Registry & Depreciation Schedules" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Fixed Assets Master</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track high-value capital assets, machinery serials, custodianship, and book depreciation values.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-asset-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Register Capital Asset
        </button>
    </div>

    <!-- Assets Table -->
    <x-common.component-card title="Registered Capital Equipment & Hardware">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Asset Tag / Code</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Asset Description</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Category</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Purchase Cost</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Current Net Book Value</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Custodian</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($assets as $ast)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs font-semibold text-brand-600 dark:text-brand-400">
                                {{ $ast->asset_code }}
                                <span class="block text-gray-400 font-normal text-[11px]">{{ $ast->serial_number ?? 'S/N: N/A' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-medium text-gray-900 dark:text-white">
                                {{ $ast->name }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-600 dark:text-gray-300">
                                <span class="rounded bg-gray-100 px-2 py-0.5 font-medium dark:bg-gray-800">{{ $ast->category->name ?? 'Hardware' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-mono text-xs text-gray-700 dark:text-gray-300">
                                ₹{{ number_format($ast->purchase_cost, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-mono text-xs font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($ast->current_value, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-600 dark:text-gray-300">
                                {{ $ast->custodian->name ?? 'Unassigned' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="inline-flex rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                    {{ ucfirst(str_replace('_', ' ', $ast->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No fixed assets logged yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $assets->links() }}
        </div>
    </x-common.component-card>

    <!-- Create Asset Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-asset-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Register Fixed Asset</h3>
            
            <form action="{{ route('assets.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Asset Name *</label>
                    <input type="text" name="name" required placeholder="5-Axis CNC Milling Center VMC-800" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                        <select name="category_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Serial Number</label>
                        <input type="text" name="serial_number" placeholder="SN-2026-998822" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Purchase Cost (₹) *</label>
                        <input type="number" step="0.01" name="purchase_cost" required value="2500000.00" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Purchase Date</label>
                        <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Custodian Personnel</label>
                    <select name="custodian_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Unassigned</option>
                        @foreach(($users ?? []) as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach

                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Register Asset</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
