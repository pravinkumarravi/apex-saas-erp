@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Double-Entry General Journal Entries" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">General Journal Entries</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Strict double-entry bookkeeping ledger. Every voucher enforces Debit = Credit balance equality.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-journal-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Journal Voucher
        </button>
    </div>

    <!-- Journal Vouchers List -->
    <div class="space-y-4">
        @forelse($entries as $entry)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-brand-50 px-2 py-0.5 text-xs font-mono font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ $entry->entry_number }}
                            </span>
                            <span class="text-xs text-gray-500 font-mono">{{ $entry->entry_date ? $entry->entry_date->format('d M Y') : '' }}</span>
                            @if($entry->status === 'posted')
                                <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Posted</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Draft</span>
                            @endif
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ $entry->description ?? 'Journal transaction voucher' }}</h4>
                        <span class="text-xs text-gray-400">Reference: {{ $entry->reference_type ? class_basename($entry->reference_type) . ' #' . $entry->reference_id : 'Manual Entry' }} &bull; Author: {{ $entry->creator->name ?? 'System' }}</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-end">
                            <span class="text-xs text-gray-400 block">Total Balance</span>
                            <span class="font-bold text-sm text-gray-900 dark:text-white font-mono">₹{{ number_format($entry->items->sum('debit'), 2) }}</span>
                        </div>
                        @if($entry->status === 'draft')
                            <form action="{{ route('finance.journal-entries.post', $entry->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="rounded bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600 transition">Post to GL</button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Debits & Credits Breakdown -->
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-xs text-start">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-start">Account</th>
                                <th class="px-3 py-2 text-start">Narration</th>
                                <th class="px-3 py-2 text-end">Debit (Dr)</th>
                                <th class="px-3 py-2 text-end">Credit (Cr)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($entry->items as $item)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-gray-800 dark:text-white">
                                        <span class="font-mono text-gray-500 me-1">{{ $item->account->code ?? '' }}</span>
                                        {{ $item->account->name ?? 'Account' }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-500">{{ $item->narration ?? '-' }}</td>
                                    <td class="px-3 py-2 text-end font-mono font-semibold {{ $item->debit > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">
                                        {{ $item->debit > 0 ? '₹' . number_format($item->debit, 2) : '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-end font-mono font-semibold {{ $item->credit > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">
                                        {{ $item->credit > 0 ? '₹' . number_format($item->credit, 2) : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-gray-500 dark:text-gray-400">No journal vouchers created yet.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $entries->links() }}
    </div>

    <!-- Create Journal Modal -->
    <div 
        x-data="{ 
            isOpen: false, 
            items: [
                { account_id: '', debit: 0, credit: 0, narration: '' },
                { account_id: '', debit: 0, credit: 0, narration: '' }
            ],
            totalDebit() { return this.items.reduce((s, i) => s + parseFloat(i.debit || 0), 0); },
            totalCredit() { return this.items.reduce((s, i) => s + parseFloat(i.credit || 0), 0); },
            isBalanced() { return Math.abs(this.totalDebit() - this.totalCredit()) < 0.01 && this.totalDebit() > 0; }
        }" 
        x-show="isOpen" 
        @open-journal-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs overflow-y-auto"
    >
        <div @click.away="isOpen = false" class="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800 my-8">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Record Double-Entry Journal Voucher</h3>
                <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <form action="{{ route('finance.journal-entries.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Voucher Date *</label>
                        <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">General Description *</label>
                        <input type="text" name="description" required placeholder="End of Month Factory Overhead Allocation" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <!-- Ledger Lines -->
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Journal Line Splits</label>
                        <button type="button" @click="items.push({ account_id: '', debit: 0, credit: 0, narration: '' })" class="text-xs text-brand-600 hover:text-brand-700 font-medium">+ Add Split</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex items-center gap-2">
                                <select :name="'items[' + index + '][account_id]'" required class="flex-1 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">Select Ledger Account</option>
                                    @foreach($accounts as $a)
                                        <option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }} ({{ ucfirst($a->type) }})</option>
                                    @endforeach
                                </select>
                                <input type="number" step="0.01" :name="'items[' + index + '][debit]'" x-model="item.debit" placeholder="Debit (Dr)" class="w-28 rounded-lg border border-gray-200 px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                                <input type="number" step="0.01" :name="'items[' + index + '][credit]'" x-model="item.credit" placeholder="Credit (Cr)" class="w-28 rounded-lg border border-gray-200 px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                                <button type="button" @click="if(items.length > 2) items.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm">&times;</button>
                            </div>
                        </template>
                    </div>

                    <!-- Balance Status -->
                    <div class="mt-3 flex items-center justify-between rounded-lg bg-gray-50 p-3 text-xs dark:bg-gray-800">
                        <div class="space-x-4">
                            <span>Total Debit: <strong class="font-mono text-gray-900 dark:text-white" x-text="'₹' + totalDebit().toFixed(2)"></strong></span>
                            <span>Total Credit: <strong class="font-mono text-gray-900 dark:text-white" x-text="'₹' + totalCredit().toFixed(2)"></strong></span>
                        </div>
                        <div>
                            <span x-show="isBalanced()" class="text-success-600 font-bold">&check; In Balance (0.00 Variance)</span>
                            <span x-show="!isBalanced()" class="text-error-600 font-bold">&cross; Out of Balance (Difference: <span x-text="'₹' + Math.abs(totalDebit() - totalCredit()).toFixed(2)"></span>)</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" :disabled="!isBalanced()" :class="!isBalanced() ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-brand-500 hover:bg-brand-600 text-white'" class="rounded-lg px-4 py-2 text-sm font-medium">Record Journal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
