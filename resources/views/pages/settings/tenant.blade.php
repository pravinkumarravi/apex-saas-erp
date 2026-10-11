@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Organization Profile & SaaS Subscription" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Tenant Settings & Tax Profile</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Configure corporate identity, Indian GSTIN registration, currency, and multi-tenant plan details.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-mono font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                Subdomain: {{ $tenant->subdomain }}.erp.com
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Organization Profile Form -->
        <div class="lg:col-span-2">
            <x-common.component-card title="Corporate Profile & Fiscal Details">
                <form action="{{ route('settings.tenant.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Company Legal Name *</label>
                            <input type="text" name="name" value="{{ $tenant->name }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Assigned Tenant Subdomain</label>
                            <input type="text" value="{{ $tenant->subdomain }}" disabled class="w-full rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-400 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Billing Email</label>
                            <input type="email" name="email" value="{{ $tenant->email }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Corporate Phone</label>
                            <input type="text" name="phone" value="{{ $tenant->phone }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <!-- Indian GST Section -->
                    <div class="rounded-xl border border-brand-100 bg-brand-50/20 p-4 dark:border-brand-500/20 dark:bg-brand-500/5 space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">Indian GST & Tax Jurisdiction</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">GSTIN Number (15 Digits)</label>
                                <input type="text" name="gstin" maxlength="15" value="{{ $tenant->gstin }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white uppercase font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">State / UT</label>
                                <input type="text" name="state" value="{{ $tenant->state }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">GST State Code (2 Digits)</label>
                                <input type="text" name="state_code" maxlength="2" value="{{ $tenant->state_code }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Physical Address</label>
                        <textarea name="address" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ $tenant->address }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Default Base Currency</label>
                            <input type="text" name="currency" value="{{ $tenant->currency ?? 'INR' }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono uppercase">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">System Timezone</label>
                            <input type="text" name="timezone" value="{{ $tenant->timezone ?? 'Asia/Kolkata' }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="submit" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
                            Save Organization Settings
                        </button>
                    </div>
                </form>
            </x-common.component-card>
        </div>

        <!-- SaaS Subscription Card -->
        <div class="space-y-6">
            <x-common.component-card title="SaaS Plan & License">
                <div class="text-center py-4 border-b border-gray-100 dark:border-gray-800">
                    <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        {{ $subscription->plan_name ?? 'Enterprise Pro' }}
                    </span>
                    <h3 class="mt-3 text-2xl font-black text-gray-900 dark:text-white">
                        ₹{{ number_format($subscription->price ?? 14999, 2) }}
                        <span class="text-xs font-normal text-gray-500">/ billing cycle</span>
                    </h3>
                    <p class="text-xs text-success-600 dark:text-success-500 font-semibold mt-1">Status: Active & In Good Standing</p>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Max Users Allowed:</span>
                        <strong class="text-gray-900 dark:text-white">{{ $subscription->max_users ?? 50 }} Seats</strong>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Storage Allocation:</span>
                        <strong class="text-gray-900 dark:text-white">{{ $subscription->max_storage_gb ?? 500 }} GB NVMe</strong>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Current Cycle Ends:</span>
                        <strong class="font-mono text-gray-900 dark:text-white">{{ $subscription->ends_at ? $subscription->ends_at->format('d M Y') : 'Auto-Renewing' }}</strong>
                    </div>
                </div>

                <div class="mt-6">
                    <button class="w-full rounded-lg border border-gray-200 bg-white py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        Manage Payment Method
                    </button>
                </div>
            </x-common.component-card>
        </div>
    </div>
</div>
@endsection
