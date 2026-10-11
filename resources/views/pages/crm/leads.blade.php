@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Sales Leads & Deal Pipeline" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Opportunity Pipeline</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track incoming inquiries, negotiate contract terms, and convert qualified leads to account masters.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-lead-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Capture New Lead
        </button>
    </div>

    <!-- Leads Table -->
    <x-common.component-card title="Lead Pipeline Opportunities">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Lead Title / Prospect</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Contact Person</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Source</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Estimated Value</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Assigned Rep</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-bold text-gray-900 dark:text-white block">{{ $lead->title }}</span>
                                <span class="text-xs text-gray-500 font-mono">{{ $lead->lead_number }} &bull; {{ $lead->company_name ?? 'Individual' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300">
                                <div class="font-medium">{{ $lead->contact_name }}</div>
                                <div class="text-gray-400">{{ $lead->email }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-600 dark:text-gray-400">
                                <span class="rounded bg-gray-100 px-2 py-0.5 capitalize dark:bg-gray-800">{{ $lead->source ?? 'Direct' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-mono text-sm font-bold text-brand-600 dark:text-brand-400">
                                ₹{{ number_format($lead->estimated_value, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($lead->status === 'converted')
                                    <span class="inline-flex rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Converted</span>
                                @elseif($lead->status === 'qualified')
                                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">Qualified</span>
                                @elseif($lead->status === 'lost')
                                    <span class="inline-flex rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">Lost</span>
                                @else
                                    <span class="inline-flex rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600">{{ ucfirst($lead->status) }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-600 dark:text-gray-300">
                                {{ $lead->assignedUser->name ?? 'Unassigned' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end text-xs font-medium">
                                @if($lead->status !== 'converted')
                                    <form action="{{ route('crm.leads.convert', $lead->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="rounded bg-success-500 px-2.5 py-1 text-xs text-white hover:bg-success-600 transition">
                                            Convert to Client
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Account Active</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No leads or inquiries currently registered.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $leads->links() }}
        </div>
    </x-common.component-card>

    <!-- Create Lead Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-lead-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Capture Sales Lead</h3>
                <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <form action="{{ route('crm.leads.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Opportunity Title *</label>
                    <input type="text" name="title" required placeholder="5000 Units Sensor Assembly RFQ" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Prospect Company</label>
                        <input type="text" name="company_name" placeholder="Bosch Automotive India" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Person *</label>
                        <input type="text" name="contact_name" required placeholder="Mr. Rajesh Kumar" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input type="email" name="email" placeholder="rajesh@bosch.in" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                        <input type="text" name="phone" placeholder="+91 98200 11223" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Estimated Deal Value (₹)</label>
                        <input type="number" step="0.01" name="estimated_value" value="250000.00" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Lead Source</label>
                        <select name="source" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="website">Website / Inbound</option>
                            <option value="trade_show">Trade Expo</option>
                            <option value="referral">Referral Partner</option>
                            <option value="outreach">Cold Outreach</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Assigned Account Manager</label>
                    <select name="assigned_user_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Unassigned</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Save Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
