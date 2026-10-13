<?php

namespace Modules\Projects\Http\Controllers;

use Modules\Projects\Contracts\ProjectRepositoryInterface;
use Modules\Projects\Contracts\ProjectTaskRepositoryInterface;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController
{
    public function __construct(
        protected ProjectRepositoryInterface $projectRepo,
        protected ProjectTaskRepositoryInterface $taskRepo,
    ) {}

    // --- PROJECTS & TASKS ---
    public function index(Request $request)
    {
        $projects = Project::with(['customer', 'manager', 'tasks.assignee'])->latest()->paginate(10);
        $customers = apply_filters('projects_customer_options', collect());
        $employees = apply_filters('projects_employee_options', collect());

        return view('pages.projects.index', compact('projects', 'customers', 'employees'));
    }

    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30',
            'customer_id' => 'nullable',
            'manager_id' => 'nullable',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'budget' => 'required|numeric|min:0',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'description' => 'nullable|string',
        ]);

        $project = $this->projectRepo->create($validated);
        do_action('audit_log', 'created', $project, null, $project->toArray());

        return redirect()->back()->with('success', "Project {$project->name} created successfully.");
    }

    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'assigned_to' => 'nullable',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,review,completed',
            'description' => 'nullable|string',
        ]);

        $task = $this->taskRepo->create($validated);
        do_action('audit_log', 'created', $task, null, $task->toArray());

        return redirect()->back()->with('success', "Task {$task->title} added to project.");
    }
}
