@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Project Management & Milestone Deliverables" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-800 dark:text-white/90">Customer Projects & Engagements</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track task backlogs, project milestones, client deliverables, and budgeted hours.</p>
        </div>
        <button 
            type="button" 
            x-data 
            @click="$dispatch('open-project-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create New Project
        </button>
    </div>

    <!-- Projects Grid -->
    <div class="space-y-6">
        @forelse($projects as $p)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-brand-50 px-2 py-0.5 text-xs font-mono font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ $p->code }}
                            </span>
                            <span class="text-xs text-gray-500 font-medium">{{ $p->customer->name ?? 'Internal Initiative' }}</span>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $p->status === 'completed' ? 'bg-success-50 text-success-600' : 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' }}">
                                {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $p->name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $p->description ?? 'Project delivery' }}</p>
                    </div>

                    <div class="flex items-center gap-6 text-xs">
                        <div>
                            <span class="text-gray-400 block">Project Lead</span>
                            <span class="font-bold text-gray-800 dark:text-white text-sm">{{ $p->manager->name ?? 'Unassigned' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Budgeted Cost</span>
                            <span class="font-mono font-bold text-gray-800 dark:text-white text-sm">₹{{ number_format($p->budget, 2) }}</span>
                        </div>
                        <button 
                            type="button" 
                            x-data 
                            @click="$dispatch('open-task-modal', { projectId: {{ $p->id }} })"
                            class="rounded-lg border border-brand-500 px-3 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-500/10"
                        >
                            + Add Task
                        </button>
                    </div>
                </div>

                <!-- Tasks List -->
                <div class="mt-4">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Milestone Tasks ({{ $p->tasks->count() }})</h4>
                    <div class="space-y-2">
                        @forelse($p->tasks as $task)
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 p-2.5 text-xs dark:bg-gray-800/50">
                                <div class="flex items-center gap-3">
                                    <span class="rounded px-2 py-0.5 text-[11px] font-medium {{ $task->status === 'completed' ? 'bg-success-100 text-success-700' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $task->title }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-gray-500">
                                    <span>Assignee: <strong>{{ $task->assignee->name ?? 'Unassigned' }}</strong></span>
                                    <span>Due: {{ $task->due_date ? $task->due_date->format('d M') : 'Open' }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">No tasks created under this project yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-gray-500 dark:text-gray-400">No projects registered.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $projects->links() }}
    </div>

    <!-- Create Project Modal -->
    <div 
        x-data="{ isOpen: false }" 
        x-show="isOpen" 
        @open-project-modal.window="isOpen = true"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Create Client Project</h3>
            
            <form action="{{ route('projects.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Project Name *</label>
                    <input type="text" name="name" required placeholder="Robotics Assembly Cell Line Automation" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Customer / Client</label>
                        <select name="customer_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Internal Project</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Project Manager</label>
                        <select name="manager_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select Manager</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Budget (₹)</label>
                        <input type="number" step="0.01" name="budget" value="1000000.00" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Turnkey robotics integration" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Save Project</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Task Modal -->
    <div 
        x-data="{ isOpen: false, projectId: null }" 
        x-show="isOpen" 
        @open-task-modal.window="isOpen = true; projectId = $event.detail.projectId"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
    >
        <div @click.away="isOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 dark:border dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Add Project Task</h3>
            
            <form action="{{ route('projects.tasks.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="project_id" :value="projectId">
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Task Title *</label>
                    <input type="text" name="title" required placeholder="Design PLC Ladder Logic" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Assignee</label>
                        <select name="assigned_to" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Unassigned</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="isOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
