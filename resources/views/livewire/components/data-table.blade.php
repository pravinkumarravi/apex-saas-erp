<div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
    <!-- Table Header Controls -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
            <span class="text-xs text-gray-500">Showing {{ $records->total() }} total entries</span>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Search Input -->
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search records..." 
                    class="w-56 rounded-lg border border-gray-200 bg-white py-1.5 ps-8 pe-3 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                <svg class="absolute start-2.5 top-2 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- Per Page Select -->
            <select wire:model.live="perPage" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>

            <!-- Export CSV Button -->
            <button 
                wire:click="exportCsv" 
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700/50"
            >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </button>
        </div>
    </div>

    <!-- Table Body -->
    <div class="overflow-x-auto mt-4">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
            <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    @foreach($columns as $col)
                        <th 
                            class="px-4 py-3 text-start font-semibold cursor-pointer select-none hover:text-gray-800 dark:hover:text-white"
                            wire:click="sortBy('{{ $col['key'] }}')"
                        >
                            <div class="flex items-center gap-1">
                                <span>{{ $col['label'] ?? ucfirst($col['key']) }}</span>
                                @if($sortColumn === $col['key'])
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                @forelse($records as $record)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        @foreach($columns as $col)
                            @php
                                $val = data_get($record, $col['key']);
                                $type = $col['type'] ?? 'text';
                            @endphp
                            <td class="whitespace-nowrap px-4 py-3 {{ isset($col['align']) && $col['align'] === 'end' ? 'text-end' : 'text-start' }}">
                                @if($type === 'badge')
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-medium {{ $val === 'active' || $val === 'completed' || $val === 'passed' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                        {{ ucfirst($val) }}
                                    </span>
                                @elseif($type === 'currency')
                                    <span class="font-mono font-semibold text-gray-900 dark:text-white">
                                        ₹{{ number_format((float)$val, 2) }}
                                    </span>
                                @elseif($type === 'date')
                                    <span class="font-mono text-gray-500">
                                        {{ $val ? \Carbon\Carbon::parse($val)->format('d M Y') : '-' }}
                                    </span>
                                @else
                                    <span class="text-gray-800 dark:text-gray-200">
                                        {{ $val ?? '-' }}
                                    </span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="px-4 py-8 text-center text-gray-400">
                            No records found matching your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
        {{ $records->links() }}
    </div>
</div>
