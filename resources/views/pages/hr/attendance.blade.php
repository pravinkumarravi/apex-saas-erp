@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Daily Employee Attendance & Shift Timesheets" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Staff Time & Attendance</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Log biometric check-in timestamps, check-out intervals, and calculate overtime hours.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-attendance-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Record Attendance Log
        </button>
    </div>

    <!-- Attendance Table -->
    <x-common.component-card title="Daily Attendance Logs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Date</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Staff Member</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Punch In</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Punch Out</th>
                        <th class="px-4 py-3.5 text-center font-semibold text-gray-600 dark:text-gray-300">Total Hours</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-500 font-mono">
                                {{ $att->date ? $att->date->format('d M Y') : '' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-medium text-gray-900 dark:text-white block">{{ $att->employee->first_name ?? '' }} {{ $att->employee->last_name ?? '' }}</span>
                                <span class="text-xs text-gray-500 font-mono">{{ $att->employee->employee_code ?? '' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300 font-mono">
                                {{ $att->punch_in ? \Carbon\Carbon::parse($att->punch_in)->format('H:i') : '--:--' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300 font-mono">
                                {{ $att->punch_out ? \Carbon\Carbon::parse($att->punch_out)->format('H:i') : '--:--' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center font-mono text-xs font-bold text-gray-800 dark:text-white">
                                {{ number_format($att->total_hours, 1) }} hrs
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($att->status === 'present')
                                    <span class="inline-flex rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Present</span>
                                @elseif($att->status === 'late')
                                    <span class="inline-flex rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600">Late</span>
                                @elseif($att->status === 'half_day')
                                    <span class="inline-flex rounded-full bg-blue-light-50 px-2.5 py-0.5 text-xs font-medium text-blue-light-600">Half Day</span>
                                @else
                                    <span class="inline-flex rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">Absent</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No attendance records logged.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $attendances->links() }}
        </div>
    </x-common.component-card>

    <!-- Create Attendance Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-attendance-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Log Attendance Entry</h3>
            
            <form action="{{ route('hr.attendance.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Employee *</label>
                    <select name="employee_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Select Employee</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }} ({{ $e->employee_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Attendance Date *</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Punch In Time</label>
                        <input type="time" name="punch_in" value="09:00" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Punch Out Time</label>
                        <input type="time" name="punch_out" value="18:00" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                    <select name="status" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="half_day">Half Day</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
