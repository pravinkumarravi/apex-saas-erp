@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Developer & REST API Gateway" />

    <!-- Top Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">API Gateway & Integrations</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage tenant-isolated API Keys, real-time event Webhooks, and inspect OpenAPI documentation.</p>
        </div>
        <div class="flex items-center gap-3">
            @action('rest_api_header_buttons')
            <a 
                href="{{ route('rest-api.docs') }}" 
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                View API Docs
            </a>
            <a 
                href="{{ route('rest-api.keys.index') }}" 
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                Manage API Keys
            </a>
        </div>
    </div>

    <!-- Metrics Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800 p-5 shadow-theme-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active API Keys</span>
                    <h3 class="text-title-md font-bold text-gray-800 dark:text-white/90 mt-1">{{ $keysCount }}</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-900/30 text-brand-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-xs text-success-500 font-medium">
                <span class="inline-block w-2 h-2 rounded-full bg-success-500"></span>
                SHA-256 Hashed Tokens
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800 p-5 shadow-theme-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Webhooks Registered</span>
                    <h3 class="text-title-md font-bold text-gray-800 dark:text-white/90 mt-1">{{ $webhooksCount }}</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-light-50 dark:bg-blue-light-900/30 text-blue-light-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span>Real-Time Async Dispatch</span>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800 p-5 shadow-theme-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">API Protocol</span>
                    <h3 class="text-title-md font-bold text-gray-800 dark:text-white/90 mt-1">REST / JSON</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-success-50 dark:bg-success-900/30 text-success-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span>Tenant Scoped Routing</span>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800 p-5 shadow-theme-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gateway Status</span>
                    <h3 class="text-title-md font-bold text-success-600 dark:text-success-400 mt-1">Operational</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-success-50 dark:bg-success-900/30 text-success-500">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-success-500"></span>
                    </span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span>Version 1.0.0</span>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- API Keys Card -->
        <x-common.component-card title="Active API Keys">
            <div class="space-y-3">
                @forelse($recentKeys as $key)
                    <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/40">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/50 text-brand-600 flex items-center justify-center font-semibold text-xs">
                                API
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $key->name }}</h4>
                                <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ $key->key_prefix }}••••••••••••</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-success-50 dark:bg-success-900/30 text-success-600 dark:text-success-400">
                            Active
                        </span>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-400 text-sm">
                        No API Keys generated yet.
                    </div>
                @endforelse
                <div class="pt-2 text-end">
                    <a href="{{ route('rest-api.keys.index') }}" class="text-sm font-medium text-brand-500 hover:text-brand-600 inline-flex items-center gap-1">
                        View All Keys &rarr;
                    </a>
                </div>
            </div>
        </x-common.component-card>

        <!-- Webhook Subscriptions Card -->
        <x-common.component-card title="Recent Webhooks">
            <div class="space-y-3">
                @forelse($recentWebhooks as $webhook)
                    <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/40">
                        <div class="flex items-center gap-3 truncate">
                            <div class="w-8 h-8 rounded-full bg-blue-light-100 dark:bg-blue-light-900/50 text-blue-light-600 flex items-center justify-center font-semibold text-xs">
                                WH
                            </div>
                            <div class="truncate">
                                <h4 class="text-sm font-medium text-gray-800 dark:text-white/90 truncate">{{ $webhook->name }}</h4>
                                <span class="font-mono text-xs text-gray-500 dark:text-gray-400 truncate block">{{ $webhook->target_url }}</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 shrink-0">
                            {{ count($webhook->events ?? []) }} Events
                        </span>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-400 text-sm">
                        No Webhook subscriptions configured yet.
                    </div>
                @endforelse
                <div class="pt-2 text-end">
                    <a href="{{ route('rest-api.webhooks.index') }}" class="text-sm font-medium text-brand-500 hover:text-brand-600 inline-flex items-center gap-1">
                        Manage Subscriptions &rarr;
                    </a>
                </div>
            </div>
        </x-common.component-card>
    </div>
</div>
@endsection
