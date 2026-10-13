@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Immutable System Audit Trail & Compliance Log" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Enterprise Audit Log</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">SOC 2 and ISO 27001 compliant immutable event logging for all CRUD entity modifications.</p>
        </div>
    </div>

    <!-- Audit Log Table -->
    <x-common.component-card title="System Activity Records">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Timestamp</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">User / Actor</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Event Action</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Target Entity</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">IP Address</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Change Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition text-xs">
                            <td class="whitespace-nowrap px-4 py-3.5 text-gray-500 font-mono">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-medium text-gray-900 dark:text-white">
                                {{ $log->user->name ?? 'System Worker' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($log->action === 'created' || $log->action === 'store')
                                    <span class="rounded bg-success-50 px-2 py-0.5 font-mono uppercase text-success-600 dark:bg-success-500/15 dark:text-success-500">Created</span>
                                @elseif($log->action === 'updated')
                                    <span class="rounded bg-blue-light-50 px-2 py-0.5 font-mono uppercase text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">Updated</span>
                                @elseif($log->action === 'deleted')
                                    <span class="rounded bg-error-50 px-2 py-0.5 font-mono uppercase text-error-600 dark:bg-error-500/15 dark:text-error-500">Deleted</span>
                                @else
                                    <span class="rounded bg-gray-100 px-2 py-0.5 font-mono uppercase text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $log->action }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-gray-700 dark:text-gray-300">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-gray-500">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400 font-mono max-w-sm truncate">
                                @if($log->new_values)
                                    {{ json_encode($log->new_values) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No audit log records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </x-common.component-card>
</div>
@endsection
