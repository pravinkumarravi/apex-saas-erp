@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="OAuth 2.0 & Mobile REST API Integrations" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Sanctum Token Authentication</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Generate personal access tokens for future iOS & Android mobile apps, IoT shop floor sensors, and B2B EDI integrations.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-token-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            Generate Mobile API Token
        </button>
    </div>

    <!-- Alert for Newly Created Token -->
    @if(session('plain_text_token'))
        <div class="rounded-xl border border-success-200 bg-success-50 p-4 dark:border-success-500/20 dark:bg-success-500/10">
            <h4 class="text-sm font-bold text-success-800 dark:text-success-400">New API Token Generated!</h4>
            <p class="text-xs text-success-700 dark:text-success-500 mt-0.5">Make sure to copy your personal access token now. You will not be able to see it again.</p>
            <div class="mt-2 flex items-center gap-2">
                <input type="text" readonly value="{{ session('plain_text_token') }}" class="w-full rounded-lg border border-success-300 bg-white px-3 py-1.5 font-mono text-xs text-gray-900 select-all dark:bg-gray-800 dark:text-white">
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Active Tokens Table -->
        <div class="lg:col-span-2">
            <x-common.component-card title="Active Personal Access Tokens">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Client / Device Name</th>
                                <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Capabilities</th>
                                <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Last Used</th>
                                <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($tokens as $tok)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition text-xs">
                                    <td class="whitespace-nowrap px-4 py-3.5 font-medium text-gray-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            {{ $tok->name }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <span class="rounded bg-brand-50 px-2 py-0.5 font-mono text-[11px] text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                            {{ implode(', ', $tok->abilities ?? ['*']) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-gray-500 font-mono">
                                        {{ $tok->last_used_at ? $tok->last_used_at->diffForHumans() : 'Never' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-gray-500 font-mono">
                                        {{ $tok->created_at->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No mobile or API tokens generated yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-common.component-card>
        </div>

        <!-- Available Mobile Endpoints -->
        <div>
            <x-common.component-card title="Mobile & B2B REST Catalog">
                <p class="text-xs text-gray-500 mb-3">All API endpoints require <code class="font-mono text-brand-600">Bearer {token}</code> and respect multi-tenant subdomain scoping.</p>
                <div class="space-y-2">
                    @foreach($endpoints as $ep)
                        <div class="rounded-lg bg-gray-50 p-2.5 text-xs dark:bg-gray-800/50">
                            <div class="flex items-center gap-2">
                                <span class="rounded bg-brand-100 px-1.5 py-0.5 text-[10px] font-bold text-brand-700 dark:bg-brand-500/20 dark:text-brand-400 font-mono">
                                    {{ $ep['method'] }}
                                </span>
                                <span class="font-mono text-gray-800 dark:text-gray-200 text-[11px]">{{ $ep['uri'] }}</span>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">{{ $ep['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </x-common.component-card>
        </div>
    </div>

    <!-- Generate Token Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-token-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Create Mobile Access Token</h3>
            
            <form action="{{ route('settings.api-tokens.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Device / Integration Label *</label>
                    <input type="text" name="name" required placeholder="Warehouse iPad Barcode Scanner" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Create Token</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
