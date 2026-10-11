@props([
    'title' => 'Page Title',
    'subtitle' => null,
    'badge' => null,
])

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pb-2">
    <div>
        <div class="flex items-center gap-2.5">
            <h1 class="text-title-md font-bold text-gray-800 dark:text-white/90">{{ $title }}</h1>
            @if($badge)
                <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    {{ $badge }}
                </span>
            @endif
        </div>
        @if($subtitle)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
