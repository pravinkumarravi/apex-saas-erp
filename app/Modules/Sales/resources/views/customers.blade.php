@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Customer Directory & B2B CRM" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Client Master & Accounts</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Maintain GSTIN, credit limits, shipping destinations, and billing profiles.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-customer-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Add New Client
        </button>
    </div>

    <!-- Customer Directory Table -->
    <x-common.component-card title="Registered Clients">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Client / Company</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">GSTIN / Tax ID</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Location</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Contact Details</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Credit Limit</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Total Orders</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($customers as $c)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-bold text-gray-900 dark:text-white block">{{ $c->name }}</span>
                                <span class="text-xs text-gray-500 font-mono">{{ $c->customer_code }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs text-gray-700 dark:text-gray-300">
                                @if($c->gstin)
                                    <span class="rounded bg-gray-100 px-2 py-0.5 dark:bg-gray-800 font-bold">{{ $c->gstin }}</span>
                                    <span class="text-gray-400 block text-[11px] mt-0.5">State Code: {{ $c->state_code ?? substr($c->gstin, 0, 2) }}</span>
                                @else
                                    <span class="text-gray-400 italic">Unregistered / Consumer</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-600 dark:text-gray-300">
                                {{ $c->city ? $c->city . ', ' : '' }}{{ $c->state ?? 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-600 dark:text-gray-300">
                                <div>{{ $c->email ?? '-' }}</div>
                                <div class="text-gray-400">{{ $c->phone ?? '-' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-mono text-xs font-semibold text-gray-800 dark:text-gray-200">
                                ₹{{ number_format($c->credit_limit, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-bold text-brand-600 dark:text-brand-400">
                                {{ $c->salesOrders->count() }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $c->is_active ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $c->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No clients or customers registered yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    </x-common.component-card>

    <!-- Create Customer Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-customer-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Register Corporate Client</h3>
                <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <form action="{{ route('crm.customers.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Company / Customer Name *</label>
                    <input type="text" name="name" required placeholder="Bharat Electronics Ltd" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input type="email" name="email" placeholder="procurement@bel.co.in" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                        <input type="text" name="phone" placeholder="+91 80 2838 1200" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">GSTIN (15 Digits)</label>
                        <input type="text" name="gstin" maxlength="15" placeholder="29AAACB1234F1Z6" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white uppercase font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">State / Province *</label>
                        <select name="state" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @foreach($states as $code => $st)
                                <option value="{{ $st }}" {{ $code === '27' ? 'selected' : '' }}>{{ $code }} - {{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                        <input type="text" name="city" placeholder="Bengaluru" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Approved Credit Limit (₹)</label>
                        <input type="number" step="0.01" name="credit_limit" value="500000.00" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Save Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
