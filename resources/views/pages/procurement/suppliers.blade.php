@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{ openModal: false }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <x-common.page-breadcrumb pageTitle="Suppliers & Vendor Management" />
            <button @click="openModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-500 text-white text-xs font-semibold hover:bg-brand-600 transition-colors shadow-theme-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add New Supplier
            </button>
        </div>

        <!-- Supplier Table -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <form method="GET" action="/procurement/suppliers" class="relative max-w-sm w-full">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search supplier by name, code, or GSTIN..." class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-4 text-gray-800 dark:text-gray-200 placeholder:text-gray-400 focus:outline-hidden focus:border-brand-500" />
                </form>
                <div class="text-xs text-gray-400">Total Suppliers: {{ $suppliers->total() }}</div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/40 text-gray-500 uppercase font-semibold text-[10px]">
                        <tr>
                            <th class="py-3 px-4 text-start">Code</th>
                            <th class="py-3 px-4 text-start">Company Name</th>
                            <th class="py-3 px-4 text-start">Contact Person</th>
                            <th class="py-3 px-4 text-start">GSTIN / State</th>
                            <th class="py-3 px-4 text-start">Bank Details</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse($suppliers as $s)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                <td class="py-3.5 px-4 font-semibold text-brand-600 dark:text-brand-400">{{ $s->code }}</td>
                                <td class="py-3.5 px-4 font-medium text-gray-900 dark:text-white">{{ $s->company_name }}</td>
                                <td class="py-3.5 px-4">
                                    <p>{{ $s->contact_person ?? 'N/A' }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $s->email }} · {{ $s->phone }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono text-gray-800 dark:text-gray-200">{{ $s->gstin ?? 'Unregistered' }}</span>
                                    <span class="block text-[11px] text-gray-400">State Code: {{ $s->state_code }} ({{ $s->state ?? 'India' }})</span>
                                </td>
                                <td class="py-3.5 px-4 text-[11px]">
                                    @if($s->bank_details && isset($s->bank_details['bank_name']))
                                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ $s->bank_details['bank_name'] }}</p>
                                        <p class="text-gray-400">A/c: {{ $s->bank_details['account_number'] ?? 'N/A' }} · IFSC: {{ $s->bank_details['ifsc_code'] ?? 'N/A' }}</p>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-success-50 text-success-700 border border-success-200 dark:bg-success-950/60 dark:text-success-300 dark:border-success-800">
                                        {{ $s->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400">No suppliers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $suppliers->links() }}
            </div>
        </div>

        <!-- Add Supplier Modal -->
        <div x-show="openModal" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center bg-black/60 p-4">
            <div @click.outside="openModal = false" class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Register New Supplier / Vendor</h3>
                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="/procurement/suppliers" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Company Name *</label>
                            <input type="text" name="company_name" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200" placeholder="e.g. Jindal Steel Ltd" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Supplier Code *</label>
                            <input type="text" name="code" required class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200" placeholder="e.g. SUP-003" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Contact Person</label>
                            <input type="text" name="contact_person" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200" placeholder="Full name" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" name="email" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200" placeholder="vendor@example.com" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                            <input type="text" name="phone" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200" placeholder="+91 ..." />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">GSTIN (15 Digits)</label>
                            <input type="text" name="gstin" maxlength="15" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200 font-mono" placeholder="27AAACJ2234N1Z1" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">PAN (10 Digits)</label>
                            <input type="text" name="pan" maxlength="10" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200 font-mono" placeholder="AAACJ2234N" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">State Code (GST) *</label>
                            <input type="text" name="state_code" required maxlength="2" value="27" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2.5 px-3 text-gray-800 dark:text-gray-200 font-mono" placeholder="27 (MH), 29 (KA)" />
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                        <h4 class="text-xs font-semibold uppercase text-gray-500 mb-2">Bank Account Details</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <input type="text" name="bank_name" placeholder="Bank Name" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200" />
                            </div>
                            <div>
                                <input type="text" name="account_number" placeholder="Account Number" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200" />
                            </div>
                            <div>
                                <input type="text" name="ifsc_code" placeholder="IFSC Code" class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent py-2 px-3 text-gray-800 dark:text-gray-200 font-mono" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-medium text-gray-600 dark:border-gray-800 dark:text-gray-400 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-500 text-xs font-semibold text-white hover:bg-brand-600 transition-colors shadow-theme-xs">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
