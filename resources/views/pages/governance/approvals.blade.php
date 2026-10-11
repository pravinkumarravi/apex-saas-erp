@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Multi-Tier Approval Workflows & Authority Matrix" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Enterprise Approvals & Governance</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Review pending purchase requisitions, high-value contracts, and payroll sign-offs.</p>
        </div>
    </div>

    <!-- Active Approval Requests -->
    <x-common.component-card title="Pending Authorization Requests">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Request #</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Document Type</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Originator</th>
                        <th class="px-4 py-3.5 text-center font-semibold text-gray-600 dark:text-gray-300">Approval Level</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Submission Date</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($requests as $req)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs font-semibold text-brand-600 dark:text-brand-400">
                                #{{ $req->id }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-medium text-gray-900 dark:text-white">
                                {{ class_basename($req->approvable_type) }} #{{ $req->approvable_id }}
                                <span class="text-xs text-gray-400 block font-normal">{{ $req->workflow->name ?? 'Standard Workflow' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-600 dark:text-gray-300">
                                {{ $req->requester->name ?? 'System' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                <span class="rounded bg-brand-50 px-2 py-0.5 text-xs font-mono font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                    Stage {{ $req->current_stage }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if($req->status === 'approved')
                                    <span class="inline-flex rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Authorized</span>
                                @elseif($req->status === 'rejected')
                                    <span class="inline-flex rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">Rejected</span>
                                @else
                                    <span class="inline-flex rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600 animate-pulse">Awaiting Approval</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-xs text-gray-500 font-mono">
                                {{ $req->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end text-xs font-medium space-x-1">
                                @if($req->status === 'pending')
                                    <form action="{{ route('governance.approvals.action', $req->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="rounded bg-success-500 px-2.5 py-1 text-xs text-white hover:bg-success-600 transition">Approve</button>
                                    </form>
                                    <form action="{{ route('governance.approvals.action', $req->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="rounded bg-error-500 px-2.5 py-1 text-xs text-white hover:bg-error-600 transition">Reject</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No pending approval requests in your queue.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </x-common.component-card>
</div>
@endsection
