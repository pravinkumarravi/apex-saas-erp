@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-erp.page-header 
        title="Modular Extension & Plugin Manager" 
        subtitle="Manage installable ERP extensions, upload ZIP plugins, and configure tenant capabilities."
        badge="Extensibility Engine"
    >
        <x-slot:actions>
            <button 
                type="button" 
                x-data 
                @click="$dispatch('open-upload-modal')"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Install Module from ZIP
            </button>
        </x-slot:actions>
    </x-erp.page-header>

    <!-- Installed Modules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($modules as $slug => $module)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 transition hover:border-brand-500/50 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $module['name'] ?? $slug }}</h3>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-mono text-xs text-gray-400">v{{ $module['version'] ?? '1.0.0' }}</span>
                                    <span class="text-gray-300 dark:text-gray-700">&bull;</span>
                                    <span class="text-xs text-gray-500">{{ $module['author'] ?? 'ApexERP' }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $module['is_active'] ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-gray-100 text-gray-500 dark:bg-gray-800' }}">
                            {{ $module['is_active'] ? 'Active' : 'Disabled' }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-4 leading-relaxed">
                        {{ $module['description'] ?? 'Modular plugin for enterprise extension.' }}
                    </p>

                    @if(!empty($module['menu']))
                        <div class="mt-4 rounded-lg bg-gray-50 p-2.5 dark:bg-gray-800/50 text-xs">
                            <span class="text-gray-400 block text-[11px]">Sidebar Navigation Registered:</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $module['menu']['name'] ?? $module['menu']['title'] }}</span>
                            @if(isset($module['menu']['route']) && \Illuminate\Support\Facades\Route::has($module['menu']['route']))
                                <a href="{{ route($module['menu']['route']) }}" class="ms-2 text-brand-600 dark:text-brand-400 hover:underline">Go to Page &rarr;</a>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <form action="{{ route('settings.modules.toggle', $slug) }}" method="POST">
                        @csrf
                        <input type="hidden" name="enable" value="{{ $module['is_active'] ? '0' : '1' }}">
                        <button type="submit" class="text-xs font-medium {{ $module['is_active'] ? 'text-warning-600 hover:text-warning-700' : 'text-success-600 hover:text-success-700' }}">
                            {{ $module['is_active'] ? 'Disable for Tenant' : 'Enable for Tenant' }}
                        </button>
                    </form>

                    <form action="{{ route('settings.modules.destroy', $slug) }}" method="POST" onsubmit="return confirm('Are you sure you want to completely uninstall this module from the filesystem?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-error-600 hover:text-error-700">
                            Uninstall
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 p-12 text-center dark:border-gray-800">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <h3 class="mt-2 text-sm font-bold text-gray-900 dark:text-white">No external modules installed</h3>
                <p class="mt-1 text-xs text-gray-500">Upload a ZIP module or run <code class="font-mono text-brand-600">php artisan module:make MyModule</code> to scaffold one.</p>
            </div>
        @endforelse
    </div>

    <!-- Upload ZIP Modal -->
    <x-erp.modal name="upload-modal" title="Install External Module (.zip)">
        <form action="{{ route('settings.modules.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Select Module ZIP Package *</label>
                <input 
                    type="file" 
                    name="module_zip" 
                    accept=".zip" 
                    required 
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-800 file:me-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-brand-600 hover:file:bg-brand-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                <p class="text-[11px] text-gray-400 mt-1">Package must contain a valid <code class="font-mono">module.json</code> manifest with name, slug, and service provider.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-upload-modal')" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Upload & Install</button>
            </div>
        </form>
    </x-erp.modal>
</div>
@endsection
