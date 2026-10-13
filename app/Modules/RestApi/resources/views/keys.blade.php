@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    showCreateModal: false,
    newKeyToken: '{{ session('new_token') }}',
    copied: false,
    copyToken() {
        navigator.clipboard.writeText(this.newKeyToken);
        this.copied = true;
        setTimeout(() => this.copied = false, 2500);
    }
}">
    <x-common.page-breadcrumb pageTitle="API Keys & Token Access" />

    @if(session('new_token'))
        <div class="rounded-xl border border-success-200 bg-success-50 dark:border-success-900/50 dark:bg-success-900/20 p-5 shadow-theme-xs">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-success-500 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="space-y-2 flex-1">
                    <h3 class="text-sm font-bold text-success-800 dark:text-success-300">API Key Created Successfully!</h3>
                    <p class="text-xs text-success-700 dark:text-success-400">Please copy your API key now. For security purposes, you will not be able to view this plain-text secret key again.</p>
                    <div class="flex items-center gap-2 max-w-xl">
                        <input 
                            type="text" 
                            readonly 
                            :value="newKeyToken" 
                            class="font-mono text-xs w-full rounded-lg border border-success-300 dark:border-success-800 bg-white dark:bg-gray-900 px-3 py-2 text-gray-900 dark:text-gray-100 select-all"
                        />
                        <button 
                            type="button" 
                            @click="copyToken()" 
                            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-success-600 px-3 py-2 text-xs font-semibold text-white hover:bg-success-700 transition"
                        >
                            <span x-text="copied ? 'Copied!' : 'Copy Key'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">API Key Credentials</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Provision scoped access tokens for external service integrations, CLI tools, and automated pipelines.</p>
        </div>
        <button 
            type="button" 
            @click="showCreateModal = true"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Generate New API Key
        </button>
    </div>

    <!-- API Keys Table -->
    <x-common.component-card title="Registered API Keys">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm text-start">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Key Name</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Prefix Identifier</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Abilities & Scopes</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Last Used</th>
                        <th class="px-4 py-3.5 text-start font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3.5 text-end font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse($keys as $key)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                            <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-white/90">
                                {{ $key->name }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-xs text-gray-500 dark:text-gray-400">
                                {{ $key->key_prefix }}••••••••••••
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($key->abilities ?? ['*'] as $ab)
                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-mono font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                            {{ $ab }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-500 dark:text-gray-400">
                                {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never used' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $key->is_active ? 'bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400' : 'bg-error-50 text-error-700 dark:bg-error-900/30 dark:text-error-400' }}">
                                    {{ $key->is_active ? 'Active' : 'Revoked' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-end">
                                @if($key->is_active)
                                    <form action="{{ route('rest-api.keys.destroy', $key->id) }}" method="POST" onsubmit="return confirm('Revoke this API Key permanently?');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-error-600 hover:text-error-700 dark:text-error-400 transition">
                                            Revoke Key
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Revoked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No API Keys registered. Click "Generate New API Key" to provision one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>

    <!-- Create Key Modal -->
    <div 
        x-show="showCreateModal" 
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @keydown.escape.window="showCreateModal = false"
    >
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-theme-xl border border-gray-200 dark:border-gray-800" @click.away="showCreateModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">Generate New API Key</h3>
                <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('rest-api.keys.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Key Name / Client System</label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        placeholder="e.g. Zapier Integration / Warehouse Scanner" 
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Expiration Date (Optional)</label>
                    <input 
                        type="date" 
                        name="expires_at" 
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Select Access Scopes</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-800 p-3 bg-gray-50/50 dark:bg-gray-800/40">
                        @foreach($availableScopes as $scope => $label)
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="abilities[]" value="{{ $scope }}" {{ $scope === '*' ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                <span class="font-mono font-medium">{{ $scope }}</span>
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
                        Create API Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
