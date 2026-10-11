@props([
    'title' => 'Metric',
    'value' => '0',
    'change' => null,
    'changeType' => 'increase', // increase, decrease, neutral
    'color' => 'brand', // brand, success, error, warning, blue
])

@php
    $colorClasses = [
        'brand' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
        'success' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-500',
        'error' => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-500',
        'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-500',
        'blue' => 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400',
    ][$color] ?? 'bg-brand-50 text-brand-600';
@endphp

<div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 transition hover:border-brand-500/30">
    <div class="flex items-center justify-between">
        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $title }}</span>
        @if(isset($icon))
            <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $colorClasses }}">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="mt-3 flex items-baseline justify-between">
        <h3 class="text-2xl font-bold font-mono text-gray-900 dark:text-white">{{ $value }}</h3>
        @if($change)
            <span class="inline-flex items-center text-xs font-semibold {{ $changeType === 'increase' ? 'text-success-600' : 'text-error-600' }}">
                {{ $changeType === 'increase' ? '↑' : '↓' }} {{ $change }}
            </span>
        @endif
    </div>
</div>
