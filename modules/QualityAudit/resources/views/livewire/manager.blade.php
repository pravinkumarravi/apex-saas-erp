<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-xl border border-success-200 bg-success-50 p-4 text-xs font-semibold text-success-800 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400">
            {{ session('message') }}
        </div>
    @endif

    <!-- Controls Bar -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2">
                <button 
                    wire:click="$set('filterType', 'all')" 
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $filterType === 'all' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}"
                >
                    All Audits
                </button>
                <button 
                    wire:click="$set('filterType', 'internal')" 
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $filterType === 'internal' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}"
                >
                    Internal
                </button>
                <button 
                    wire:click="$set('filterType', 'supplier')" 
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $filterType === 'supplier' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}"
                >
                    Supplier
                </button>
                <button 
                    wire:click="$set('filterType', 'regulatory')" 
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $filterType === 'regulatory' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}"
                >
                    Regulatory
                </button>
            </div>

            <div class="flex items-center gap-3">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search audit title..." 
                    class="w-56 rounded-lg border border-gray-200 px-3 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                <button 
                    type="button" 
                    @click="$dispatch('open-audit-modal')"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-medium text-white hover:bg-brand-600 transition"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Audit Run
                </button>
            </div>
        </div>
    </div>

    <!-- Audits Ledger Table -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-xs text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50 uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-start">Audit #</th>
                        <th class="px-4 py-3 text-start">Scope Title</th>
                        <th class="px-4 py-3 text-start">Type</th>
                        <th class="px-4 py-3 text-start">Lead Auditor</th>
                        <th class="px-4 py-3 text-end">Score</th>
                        <th class="px-4 py-3 text-start">Status</th>
                        <th class="px-4 py-3 text-start">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($audits as $audit)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="whitespace-nowrap px-4 py-3 font-mono font-bold text-brand-600 dark:text-brand-400">
                                {{ $audit->audit_number }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $audit->title }}
                                @if($audit->findings)
                                    <span class="block text-gray-400 font-normal text-[11px] truncate max-w-xs">{{ $audit->findings }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="rounded bg-gray-100 px-2 py-0.5 capitalize dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    {{ $audit->audit_type }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                {{ $audit->auditor_name }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end font-mono font-bold {{ $audit->score_percentage >= 85 ? 'text-success-600 dark:text-success-500' : 'text-error-600 dark:text-error-500' }}">
                                {{ number_format($audit->score_percentage, 1) }}%
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($audit->status === 'passed')
                                    <x-erp.badge variant="success">Passed</x-erp.badge>
                                @elseif($audit->status === 'capa_required')
                                    <x-erp.badge variant="error">CAPA Required</x-erp.badge>
                                @elseif($audit->status === 'in_progress')
                                    <x-erp.badge variant="brand">In Progress</x-erp.badge>
                                @else
                                    <x-erp.badge variant="neutral">Scheduled</x-erp.badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-gray-500">
                                {{ $audit->scheduled_date ? $audit->scheduled_date->format('d M Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                No quality audits registered under current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
            {{ $audits->links() }}
        </div>
    </div>

    <!-- Create Audit Modal -->
    <x-erp.modal name="audit-modal" title="Schedule Quality Audit & Compliance Check">
        <form wire:submit.prevent="saveAudit" class="space-y-4 text-xs">
            <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Audit Scope Title *</label>
                <input type="text" wire:model="title" required placeholder="ISO 9001 Section 8.5 Production Control Audit" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                @error('title') <span class="text-error-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Audit Category *</label>
                    <select wire:model="audit_type" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="internal">Internal Shop Floor</option>
                        <option value="supplier">Vendor / Supplier</option>
                        <option value="regulatory">Regulatory / ISO</option>
                        <option value="customer">Customer Witness</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Auditor Name *</label>
                    <input type="text" wire:model="auditor_name" required placeholder="Mr. Vikram Deshmukh" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('auditor_name') <span class="text-error-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Compliance Score (%) *</label>
                    <input type="number" step="0.1" wire:model="score_percentage" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-800 font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('score_percentage') <span class="text-error-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Scheduled Date *</label>
                    <input type="date" wire:model="scheduled_date" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Audit Findings & Observations</label>
                <textarea wire:model="findings" rows="2" placeholder="Caliper calibrations current; slight packaging label deviation noted." class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-audit-modal')" class="rounded-lg border border-gray-200 px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-medium text-white hover:bg-brand-600">Save Audit Record</button>
            </div>
        </form>
    </x-erp.modal>
</div>
