@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="HR Staff Directory & Employee Profiles" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Employee Records & Payroll Masters</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage organizational designations, PF / PAN identification, and base compensation structures.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-employee-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Add New Employee
        </button>
    </div>

    <!-- Employees Directory Table -->
    <x-common.component-card title="Active Personnel Directory">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Staff Member</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Department / Role</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">PAN / PF UAN</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Base Salary</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Joining Date</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        {{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-900 dark:text-white block">{{ $emp->first_name }} {{ $emp->last_name }}</span>
                                        <span class="text-xs text-gray-500 font-mono">{{ $emp->employee_code }} &bull; {{ $emp->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs">
                                <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $emp->designation->name ?? 'Specialist' }}</div>
                                <div class="text-gray-400">{{ $emp->department->name ?? 'Operations' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs font-mono text-gray-600 dark:text-gray-400">
                                <div>PAN: <strong class="text-gray-800 dark:text-gray-200">{{ $emp->pan_number ?? 'ABCDE1234F' }}</strong></div>
                                <div>UAN: {{ $emp->pf_uan ?? '101234567890' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-mono text-xs font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($emp->basic_salary, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-500 font-mono">
                                {{ $emp->date_of_joining ? $emp->date_of_joining->format('d M Y') : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $emp->status === 'active' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-gray-100 text-gray-500' }}">
                                    {{ ucfirst($emp->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No employees registered in the system.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $employees->links() }}
        </div>
    </x-common.component-card>

    <!-- Create Employee Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-employee-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs overflow-y-auto"
    >
        <div @click.away="isOpen = false" class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800 my-8">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Register New Employee</h3>
                <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <form action="{{ route('hr.employees.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">First Name *</label>
                        <input type="text" name="first_name" required placeholder="Aarav" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name *</label>
                        <input type="text" name="last_name" required placeholder="Sharma" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Corporate Email *</label>
                        <input type="email" name="email" required placeholder="aarav.sharma@tataengineering.com" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Mobile Phone</label>
                        <input type="text" name="phone" placeholder="+91 98200 44332" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Department *</label>
                        <select name="department_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select Department</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Designation *</label>
                        <select name="designation_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select Designation</option>
                            @foreach($designations as $des)
                                <option value="{{ $des->id }}">{{ $des->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Basic Monthly Salary (₹) *</label>
                        <input type="number" step="0.01" name="basic_salary" required value="45000.00" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Joining Date</label>
                        <input type="date" name="date_of_joining" value="{{ date('Y-m-d') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">PAN Number (10 Digits)</label>
                        <input type="text" name="pan_number" maxlength="10" placeholder="ABCDE1234F" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white uppercase font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Provident Fund UAN (12 Digits)</label>
                        <input type="text" name="pf_uan" maxlength="12" placeholder="100987654321" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Save Employee Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
