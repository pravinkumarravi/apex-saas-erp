@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Financial Statements (Balance Sheet & Profit & Loss)" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">General Ledger Financial Statements</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Formal multi-step Statement of Financial Position (Balance Sheet) and Income Statement (P&L).</p>
        </div>
        <button onclick="window.print()" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
            Print Financial Statements
        </button>
    </div>

    <!-- Profit and Loss Statement -->
    <x-common.component-card title="Income Statement (Profit & Loss)">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm">
            <!-- Revenue -->
            <div>
                <h4 class="font-bold text-success-600 dark:text-success-500 uppercase tracking-wider text-xs border-b border-gray-200 pb-2 dark:border-gray-800">
                    Operating Revenues & Sales
                </h4>
                <div class="mt-3 space-y-2">
                    @forelse($profitAndLoss['revenue'] as $rev)
                        <div class="flex justify-between items-center text-xs py-1">
                            <span class="text-gray-700 dark:text-gray-300">
                                <span class="font-mono text-gray-400 me-1">{{ $rev->code }}</span>
                                {{ $rev->name }}
                            </span>
                            <span class="font-mono font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($rev->journalItems->sum('credit') - $rev->journalItems->sum('debit'), 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-xs text-gray-400 italic">No revenue ledger transactions.</div>
                    @endforelse
                </div>
            </div>

            <!-- Expenses -->
            <div>
                <h4 class="font-bold text-error-600 dark:text-error-500 uppercase tracking-wider text-xs border-b border-gray-200 pb-2 dark:border-gray-800">
                    Operating Expenses & Cost of Goods Sold
                </h4>
                <div class="mt-3 space-y-2">
                    @forelse($profitAndLoss['expenses'] as $exp)
                        <div class="flex justify-between items-center text-xs py-1">
                            <span class="text-gray-700 dark:text-gray-300">
                                <span class="font-mono text-gray-400 me-1">{{ $exp->code }}</span>
                                {{ $exp->name }}
                            </span>
                            <span class="font-mono font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($exp->journalItems->sum('debit') - $exp->journalItems->sum('credit'), 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-xs text-gray-400 italic">No expense transactions recorded.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-between items-center rounded-xl bg-gray-50 p-4 border border-gray-100 dark:bg-gray-800/50 dark:border-gray-800">
            <span class="text-sm font-bold text-gray-800 dark:text-white">Net Operating Profit / (Loss):</span>
            <span class="text-lg font-black font-mono {{ $profitAndLoss['net_profit'] >= 0 ? 'text-success-600 dark:text-success-500' : 'text-error-600 dark:text-error-500' }}">
                ₹{{ number_format($profitAndLoss['net_profit'], 2) }}
            </span>
        </div>
    </x-common.component-card>

    <!-- Balance Sheet Statement -->
    <x-common.component-card title="Balance Sheet (Statement of Financial Position)">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm">
            <!-- Assets Side -->
            <div>
                <h4 class="font-bold text-brand-600 dark:text-brand-400 uppercase tracking-wider text-xs border-b border-gray-200 pb-2 dark:border-gray-800">
                    Assets (Current & Non-Current)
                </h4>
                <div class="mt-3 space-y-2">
                    @forelse($balanceSheet['assets'] as $ast)
                        <div class="flex justify-between items-center text-xs py-1">
                            <span class="text-gray-700 dark:text-gray-300">
                                <span class="font-mono text-gray-400 me-1">{{ $ast->code }}</span>
                                {{ $ast->name }}
                            </span>
                            <span class="font-mono font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($ast->journalItems->sum('debit') - $ast->journalItems->sum('credit'), 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-xs text-gray-400 italic">No asset transactions.</div>
                    @endforelse
                </div>
                <div class="mt-4 border-t border-gray-200 pt-3 flex justify-between font-bold dark:border-gray-800">
                    <span>Total Assets:</span>
                    <span class="font-mono text-brand-600 dark:text-brand-400">₹{{ number_format($balanceSheet['total_assets'], 2) }}</span>
                </div>
            </div>

            <!-- Liabilities & Equity Side -->
            <div>
                <h4 class="font-bold text-orange-600 dark:text-orange-400 uppercase tracking-wider text-xs border-b border-gray-200 pb-2 dark:border-gray-800">
                    Liabilities & Shareholder Equity
                </h4>
                <div class="mt-3 space-y-2">
                    <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Liabilities</div>
                    @foreach($balanceSheet['liabilities'] as $liab)
                        <div class="flex justify-between items-center text-xs py-1">
                            <span class="text-gray-700 dark:text-gray-300">
                                <span class="font-mono text-gray-400 me-1">{{ $liab->code }}</span>
                                {{ $liab->name }}
                            </span>
                            <span class="font-mono font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($liab->journalItems->sum('credit') - $liab->journalItems->sum('debit'), 2) }}
                            </span>
                        </div>
                    @endforeach

                    <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider pt-2">Equity & Retained Earnings</div>
                    @foreach($balanceSheet['equity'] as $eq)
                        <div class="flex justify-between items-center text-xs py-1">
                            <span class="text-gray-700 dark:text-gray-300">
                                <span class="font-mono text-gray-400 me-1">{{ $eq->code }}</span>
                                {{ $eq->name }}
                            </span>
                            <span class="font-mono font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($eq->journalItems->sum('credit') - $eq->journalItems->sum('debit'), 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 border-t border-gray-200 pt-3 flex justify-between font-bold dark:border-gray-800">
                    <span>Total Liabilities & Equity:</span>
                    <span class="font-mono text-orange-600 dark:text-orange-400">₹{{ number_format($balanceSheet['total_liabilities_equity'], 2) }}</span>
                </div>
            </div>
        </div>
    </x-common.component-card>
</div>
@endsection
