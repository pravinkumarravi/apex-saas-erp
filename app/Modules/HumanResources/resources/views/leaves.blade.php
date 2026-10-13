@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Leave Management & Paid Time Off (PTO)" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Leave Requests & Approvals</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Review employee vacation, medical, and casual leave requests with manager approval hierarchy.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-leave-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Apply for Leave
        </button>
    </div>

    <!-- Leaves Table -->
    <x-common.component-card title="Employee Leave Applications">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Staff Member</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Leave Type</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Duration</th>
                        <th class="px-4 py-3.5 text-center font-semibold text-gray-600 dark:text-gray-300">Total Days</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Reason</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($leaves as $lv)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-medium text-gray-900 dark:text-white block">{{ $lv->employee->first_name ?? '' }} {{ $lv->employee->last_name ?? '' }}</span>
                                <span class="text-xs text-gray-500 font-mono">{{ $lv->employee->employee_code ?? '' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs capitalize font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ str_replace('_', ' ', $lv->leave_type) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-600 dark:text-gray-300 font-mono">
                                {{ $lv->start_date ? $lv->start_date->format('d M') : '' }} - {{ $lv->end_date ? $lv->end_date->format('d M Y') : '' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center font-bold text-xs text-gray-800 dark:text-white">
                                {{ $lv->days }} days
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-500 max-w-xs truncate">
                                {{ $lv->reason ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($lv->status === 'approved')
                                    <span class="inline-flex rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Approved</span>
                                @elseif($lv->status === 'rejected')
                                    <span class="inline-flex rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">Rejected</span>
                                @else
                                    <span class="inline-flex rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600 animate-pulse">Pending Review</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end text-xs font-medium space-x-1">
                                @if($lv->status === 'pending')
                                    <form action="{{ route('hr.leaves.action', $lv->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="rounded bg-success-500 px-2.5 py-1 text-xs text-white hover:bg-success-600 transition">Approve</button>
                                    </form>
                                    <form action="{{ route('hr.leaves.action', $lv->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="rounded bg-error-500 px-2.5 py-1 text-xs text-white hover:bg-error-600 transition">Reject</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No leave applications registered.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $leaves->links() }}
        </div>
    </x-common.component-card>

    <!-- Create Leave Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-leave-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Submit Leave Request</h3>
            
            <form action="{{ route('hr.leaves.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Employee *</label>
                    <select name="employee_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Select Employee</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Leave Category *</label>
                    <select name="leave_type" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="casual">Casual Leave (CL)</option>
                        <option value="sick">Medical / Sick Leave (SL)</option>
                        <option value="earned">Earned / Privilege Leave (PL)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date *</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">End Date *</label>
                        <input type="date" name="end_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                    <textarea name="reason" rows="2" placeholder="Personal emergency" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
