@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Manufacturing Work Centers & Production Routing" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Work Centers & Machinery Lines</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Configure machine capacity, labor overhead rates, and operational efficiency factors.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-wc-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Work Center
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($workCenters as $wc)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 transition hover:border-brand-500/50">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="rounded bg-brand-50 px-2 py-0.5 text-xs font-mono font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            {{ $wc->code }}
                        </span>
                        <h3 class="mt-2 font-bold text-gray-900 dark:text-white text-base">{{ $wc->name }}</h3>
                    </div>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $wc->is_active ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-gray-100 text-gray-500' }}">
                        {{ $wc->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                    <div>
                        <span class="text-gray-400 block">Cost Per Hour</span>
                        <span class="font-bold text-gray-900 dark:text-white text-sm">₹{{ number_format($wc->cost_per_hour, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Daily Capacity</span>
                        <span class="font-bold text-gray-900 dark:text-white text-sm">{{ number_format($wc->capacity_hours_per_day, 1) }} hrs</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                    <span class="text-gray-500">Efficiency: <strong class="text-gray-800 dark:text-gray-200">{{ $wc->efficiency_factor * 100 }}%</strong></span>
                    <span class="text-brand-600 font-medium">Ready for Scheduling</span>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-gray-500">
                No work centers configured yet.
            </div>
        @endforelse
    </div>

    <!-- Create Work Center Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-wc-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Configure Work Center</h3>
                <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <form action="{{ route('manufacturing.work-centers.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
                        <input type="text" name="code" required placeholder="WC-02" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                        <input type="text" name="name" required placeholder="CNC Milling Machine #2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cost Per Hour (₹) *</label>
                        <input type="number" step="0.01" name="cost_per_hour" required value="450.00" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Daily Capacity (Hours)</label>
                        <input type="number" step="0.1" name="capacity_hours_per_day" value="8.0" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Save Work Center</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
