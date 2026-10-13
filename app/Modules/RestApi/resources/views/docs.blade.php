@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="REST API Developer Documentation" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">API Documentation & Reference</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Comprehensive reference for developers integrating with ApexERP core resources.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                OpenAPI 3.1 Compatible
            </span>
        </div>
    </div>

    <!-- Authentication Guide -->
    <x-common.component-card title="Authentication & Headers">
        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
            <p>Every API request must include a valid API Key in the HTTP headers using either <code>X-API-KEY</code> or the standard <code>Authorization: Bearer</code> header:</p>
            <div class="rounded-lg bg-gray-900 p-4 font-mono text-xs text-green-400 overflow-x-auto">
                <pre>curl -X GET "http://127.0.0.1:8000/api/v1/sales/orders?tenant=companya" \
  -H "X-API-KEY: apk_live_your_secret_key_here" \
  -H "Accept: application/json"</pre>
            </div>
        </div>
    </x-common.component-card>

    <!-- Available Endpoints -->
    <x-common.component-card title="Core API Endpoints">
        <div class="space-y-4">
            @foreach($endpoints as $ep)
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 bg-gray-50/50 dark:bg-gray-800/30">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center rounded px-2.5 py-1 text-xs font-bold font-mono {{ $ep['method'] === 'GET' ? 'bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300' : 'bg-brand-100 text-brand-800 dark:bg-brand-900/50 dark:text-brand-300' }}">
                            {{ $ep['method'] }}
                        </span>
                        <span class="font-mono text-sm font-semibold text-gray-900 dark:text-white">{{ $ep['uri'] }}</span>
                        <span class="ms-auto text-xs font-medium text-gray-500 dark:text-gray-400">{{ $ep['auth'] }}</span>
                    </div>
                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">{{ $ep['description'] }}</p>
                </div>
            @endforeach
        </div>
    </x-common.component-card>
</div>
@endsection
