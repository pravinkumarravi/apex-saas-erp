@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="General Ledger Chart of Accounts (COA)" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Chart of Accounts Hierarchy</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Strict multi-tier accounting tree supporting Asset, Liability, Equity, Revenue, and Expense classes.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-coa-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Ledger Account
        </button>
    </div>

    <!-- COA Table Grid -->
    <x-common.component-card title="Standard Accounts Ledger Structure">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Account Code</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Account Title</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Category</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Normal Balance</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($accounts as $acc)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition {{ $acc->parent_id ? '' : 'bg-gray-50/30 dark:bg-gray-800/20 font-semibold' }}">
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs text-brand-600 dark:text-brand-400">
                                {{ $acc->code }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="{{ $acc->parent_id ? 'ps-6' : '' }} flex items-center gap-1.5 text-gray-900 dark:text-white">
                                    @if($acc->parent_id)
                                        <span class="text-gray-400 font-normal">&bull;</span>
                                    @endif
                                    {{ $acc->name }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($acc->type === 'asset')
                                    <span class="rounded bg-blue-light-50 px-2 py-0.5 text-xs font-medium text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">Asset</span>
                                @elseif($acc->type === 'liability')
                                    <span class="rounded bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-600 dark:bg-orange-500/15 dark:text-orange-400">Liability</span>
                                @elseif($acc->type === 'equity')
                                    <span class="rounded bg-theme-purple-500/10 px-2 py-0.5 text-xs font-medium text-theme-purple-500">Equity</span>
                                @elseif($acc->type === 'revenue')
                                    <span class="rounded bg-success-50 px-2 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Revenue</span>
                                @else
                                    <span class="rounded bg-error-50 px-2 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">Expense</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs uppercase font-mono text-gray-500">
                                {{ $acc->normal_balance }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $acc->is_active ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $acc->is_active ? 'Active' : 'Archived' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                No chart of accounts configured.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>

    <!-- Create COA Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-coa-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Create General Ledger Account</h3>
                <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <form action="{{ route('finance.chart-of-accounts.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Account Code *</label>
                        <input type="text" name="code" required placeholder="1050" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Account Type *</label>
                        <select name="type" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white capitalize">
                            @foreach($accountTypes as $t)
                                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Account Name *</label>
                    <input type="text" name="name" required placeholder="Prepaid Corporate Insurance" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Parent Account</label>
                        <select name="parent_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">None (Top Level Root)</option>
                            @foreach($accounts->whereNull('parent_id') as $p)
                                <option value="{{ $p->id }}">{{ $p->code }} - {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Normal Balance *</label>
                        <select name="normal_balance" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="debit">Debit (Dr)</option>
                            <option value="credit">Credit (Cr)</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
