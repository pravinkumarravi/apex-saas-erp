@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false }">
    <x-common.page-breadcrumb pageTitle="Event Webhook Subscriptions" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Real-Time Webhooks</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Subscribe external HTTP endpoints to ERP domain lifecycle events (orders, customers, dispatches).</p>
        </div>
        <button 
            type="button" 
            @click="showCreateModal = true"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Webhook Endpoint
        </button>
    </div>

    <!-- Webhooks Table -->
    <x-common.component-card title="Registered Endpoints">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Endpoint Name</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Target Payload URL</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Subscribed Events</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Last Dispatched</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse($webhooks as $webhook)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                            <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white/90">
                                {{ $webhook->name }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-xs text-gray-600 dark:text-gray-300">
                                {{ $webhook->target_url }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($webhook->events ?? [] as $event)
                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-mono font-medium bg-blue-light-50 dark:bg-blue-light-900/30 text-blue-light-700 dark:text-blue-light-300">
                                            {{ $event }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400">
                                {{ $webhook->last_dispatched_at ? $webhook->last_dispatched_at->diffForHumans() : 'No dispatches yet' }}
                            </td>
                            <td class="px-4 py-3.5 text-end space-x-2">
                                <form action="{{ route('rest-api.webhooks.test', $webhook->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 transition">
                                        Test Ping
                                    </button>
                                </form>
                                <span class="text-gray-300 dark:text-gray-700">|</span>
                                <form action="{{ route('rest-api.webhooks.destroy', $webhook->id) }}" method="POST" onsubmit="return confirm('Delete this webhook endpoint?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-error-600 hover:text-error-700 dark:text-error-400 transition">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No Webhook subscriptions created. Click "Add Webhook Endpoint" to receive real-time events.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>

    <!-- Create Webhook Modal -->
    <div 
        x-show="showCreateModal" 
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @keydown.escape.window="showCreateModal = false"
    >
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-theme-xl border border-gray-200 dark:border-gray-800" @click.away="showCreateModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">Register Webhook Endpoint</h3>
                <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('rest-api.webhooks.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Subscription Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        placeholder="e.g. ERP to Slack Notifications" 
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Target Endpoint URL (HTTPS)</label>
                    <input 
                        type="url" 
                        name="target_url" 
                        required 
                        placeholder="https://api.external-service.com/webhooks/apex" 
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Events to Dispatch</label>
                    <div class="space-y-2 rounded-lg border border-gray-200 dark:border-gray-800 p-3 bg-gray-50/50 dark:bg-gray-800/40">
                        @foreach($availableEvents as $event => $label)
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="events[]" value="{{ $event }}" {{ $event === 'after_sales_order_created' ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                <span class="font-mono font-medium">{{ $event }}</span>
                                <span class="text-gray-500 dark:text-gray-400">({{ $label }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition shadow-theme-xs">
                        Save Webhook
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
