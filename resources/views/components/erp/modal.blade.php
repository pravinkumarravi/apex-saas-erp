@props([
    'name' => 'modal',
    'title' => 'Dialog',
    'maxWidth' => 'max-w-lg',
])

<div 
    x-data="{ isOpen: false }" 
    x-show="isOpen" 
    @open-{{ $name }}.window="isOpen = true"
    @close-{{ $name }}.window="isOpen = false"
    @keydown.escape.window="isOpen = false"
    x-cloak
    class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs overflow-y-auto"
>
    <div 
        @click.away="isOpen = false" 
        class="w-full {{ $maxWidth }} rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800 my-8 transition"
    >
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
            <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg leading-none">&times;</button>
        </div>

        <div class="mt-4">
            {{ $slot }}
        </div>
    </div>
</div>
