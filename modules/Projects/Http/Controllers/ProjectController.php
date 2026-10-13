<?php

namespace Modules\Projects\Http\Controllers;

use Modules\Assets\Domain\Models\Asset;
use Modules\Assets\Domain\Models\AssetCategory;
use Modules\Governance\Domain\Models\AuditLog;
use Modules\Sales\Domain\Models\Customer;
use Modules\HumanResources\Domain\Models\Employee;
use Modules\Projects\Domain\Models\Project;
use Modules\Projects\Domain\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController
{
    // --- PROJECTS & TASKS ---
    public function index(Request $request)
    {
        $projects = Project::with(['customer', 'manager', 'tasks.assignee'])->latest()->paginate(10);
        $customers = Customer::where('status', 'active')->get();
        $employees = Employee::where('status', 'active')->get();

        return view('pages.projects.index', compact('projects', 'customers', 'employees'));
    }

    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30',
            'customer_id' => 'nullable|exists:customers,id',
            'manager_id' => 'nullable|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'budget' => 'required|numeric|min:0',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'description' => 'nullable|string',
        ]);

        $project = Project::create($validated);
        AuditLog::log('created', $project, null, $project->toArray());

        return redirect()->back()->with('success', "Project {$project->name} created successfully.");
    }

    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'assigned_to' => 'nullable|exists:employees,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,review,completed',
            'description' => 'nullable|string',
        ]);

        $task = ProjectTask::create($validated);
        AuditLog::log('created', $task, null, $task->toArray());

        return redirect()->back()->with('success', "Task {$task->title} added to project.");
    }

    // --- ASSETS & DEPRECIATION ---
    public function assets(Request $request)
    {
        $assets = Asset::with('category')->latest()->paginate(10);
        $categories = AssetCategory::all();

        return view('pages.assets.index', compact('assets', 'categories'));
    }

    public function storeAsset(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_tag' => 'required|string|max:30',
            'category_id' => 'required|exists:asset_categories,id',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'salvage_value' => 'required|numeric|min:0',
            'current_value' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'status' => 'required|in:in_use,maintenance,retired,scrapped',
            'notes' => 'nullable|string',
        ]);

        $asset = Asset::create($validated);
        AuditLog::log('created', $asset, null, $asset->toArray());

        return redirect()->back()->with('success', "Asset {$asset->name} registered.");
    }
}
