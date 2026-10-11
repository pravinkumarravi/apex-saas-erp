@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Role-Based Access Control (RBAC) & Permissions" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Roles & Granular Permissions</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Configure administrative privilege sets and assign role memberships across tenant users.</p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                type="button" 
                x-data 
                @click="$dispatch('open-assign-modal')"
                class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200"
            >
                Assign User Role
            </button>
            <button 
                type="button" 
                x-data 
                @click="$dispatch('open-role-modal')"
                class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
            >
                Create Custom Role
            </button>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $role->name }}</h3>
                        <span class="rounded bg-brand-50 px-2 py-0.5 text-xs font-mono text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            {{ $role->slug }}
                        </span>
                    </div>
                    <span class="text-xs text-gray-400 font-mono">{{ $role->permissions->count() }} Perms</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ $role->description ?? 'Role definition' }}</p>

                <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-2">Granted Capabilities</span>
                    <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto">
                        @foreach($role->permissions->take(8) as $perm)
                            <span class="rounded bg-gray-100 px-2 py-0.5 text-[10px] text-gray-700 dark:bg-gray-800 dark:text-gray-300 font-mono">
                                {{ $perm->name }}
                            </span>
                        @endforeach
                        @if($role->permissions->count() > 8)
                            <span class="text-[10px] text-brand-600 dark:text-brand-400 self-center">+{{ $role->permissions->count() - 8 }} more</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create Role Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-role-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs overflow-y-auto"
    >
        <div @click.away="isOpen = false" class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800 my-8">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Create Security Role</h3>
            
            <form action="{{ route('settings.roles.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Role Title *</label>
                        <input type="text" name="name" required placeholder="Warehouse Officer" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Slug Identifier *</label>
                        <input type="text" name="slug" required placeholder="warehouse_officer" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Role Description</label>
                    <input type="text" name="description" placeholder="Inventory receiving and dispatch authority" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Assign Permissions</label>
                    <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        @foreach($permissions as $p)
                            <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="permissions[]" value="{{ $p->id }}" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                <span>{{ $p->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Save Role</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Role Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-assign-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Assign Role to Staff Member</h3>
            
            <form action="{{ route('settings.roles.assign') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Select User *</label>
                    <select name="user_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Assign Role *</label>
                    <select name="role_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->slug }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Assign Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
