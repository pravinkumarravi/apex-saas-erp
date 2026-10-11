@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Payroll Engine & Statutory Indian Deductions" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Automated Monthly Payroll Execution</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Calculate Provident Fund (PF 12%), Professional Tax (PT ₹200), TDS withholdings, and auto-post to General Ledger.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-payroll-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Process Monthly Payroll Run
        </button>
    </div>

    <!-- Payroll Runs Accordion / List -->
    <div class="space-y-6">
        @forelse($runs as $run)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-brand-50 px-2 py-0.5 text-xs font-mono font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ $run->payroll_number }}
                            </span>
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                Period: {{ date("F", mktime(0, 0, 0, $run->month, 10)) }} {{ $run->year }}
                            </span>
                            <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                {{ ucfirst($run->status) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Processed by: {{ $run->creator->name ?? 'System' }} &bull; Headcount: {{ $run->payslips->count() }} Employees</p>
                    </div>

                    <div class="flex items-center gap-6 text-xs font-mono">
                        <div>
                            <span class="text-gray-400 block font-sans">Gross Wage Expense</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">₹{{ number_format($run->total_gross, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-sans">Total Deductions</span>
                            <span class="text-sm font-bold text-error-600 dark:text-error-500">₹{{ number_format($run->total_deductions, 2) }}</span>
                        </div>
                        <div class="border-s border-gray-200 ps-4 dark:border-gray-800">
                            <span class="text-gray-400 block font-sans">Net Payout to Bank</span>
                            <span class="text-base font-black text-brand-600 dark:text-brand-400">₹{{ number_format($run->total_net, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payslip Table for this run -->
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-xs text-start">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-start">Employee</th>
                                <th class="px-3 py-2 text-end">Basic</th>
                                <th class="px-3 py-2 text-end">HRA</th>
                                <th class="px-3 py-2 text-end">Gross</th>
                                <th class="px-3 py-2 text-end">PF (12%)</th>
                                <th class="px-3 py-2 text-end">PT</th>
                                <th class="px-3 py-2 text-end">TDS</th>
                                <th class="px-3 py-2 text-end">Total Deductions</th>
                                <th class="px-3 py-2 text-end font-bold text-gray-800 dark:text-white">Net Salary</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-mono">
                            @foreach($run->payslips as $slip)
                                <tr>
                                    <td class="px-3 py-2 font-sans font-medium text-gray-800 dark:text-white">
                                        {{ $slip->employee->first_name ?? '' }} {{ $slip->employee->last_name ?? '' }}
                                        <span class="text-[11px] text-gray-400 font-mono block">{{ $slip->employee->employee_code ?? '' }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-end text-gray-600 dark:text-gray-400">₹{{ number_format($slip->basic_salary, 2) }}</td>
                                    <td class="px-3 py-2 text-end text-gray-600 dark:text-gray-400">₹{{ number_format($slip->hra, 2) }}</td>
                                    <td class="px-3 py-2 text-end font-semibold text-gray-800 dark:text-white">₹{{ number_format($slip->gross_salary, 2) }}</td>
                                    <td class="px-3 py-2 text-end text-error-600">₹{{ number_format($slip->pf_deduction, 2) }}</td>
                                    <td class="px-3 py-2 text-end text-error-600">₹{{ number_format($slip->pt_deduction, 2) }}</td>
                                    <td class="px-3 py-2 text-end text-error-600">₹{{ number_format($slip->tds_deduction, 2) }}</td>
                                    <td class="px-3 py-2 text-end font-semibold text-error-600">₹{{ number_format($slip->total_deductions, 2) }}</td>
                                    <td class="px-3 py-2 text-end font-bold text-brand-600 dark:text-brand-400">₹{{ number_format($slip->net_salary, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-gray-500 dark:text-gray-400">No payroll runs executed yet.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $runs->links() }}
    </div>

    <!-- Process Payroll Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-payroll-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Run Monthly Payroll</h3>
            <p class="text-xs text-gray-500 mt-1">This will generate automated payslips for all active employees and post salary expenses to the GL.</p>
            
            <form action="{{ route('hr.payroll.process') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Month *</label>
                        <select name="month" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Year *</label>
                        <select name="year" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="{{ date('Y') }}" selected>{{ date('Y') }}</option>
                            <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Execute Payroll Run</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
