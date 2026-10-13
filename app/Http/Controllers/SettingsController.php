<?php

namespace App\Http\Controllers;

use Modules\Governance\Models\ApprovalRequest;
use Modules\Governance\Models\ApprovalWorkflow;
use Modules\Governance\Models\AuditLog;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;
use Modules\Organization\Models\Subscription;
use Modules\Organization\Models\Tenant;
use Modules\Identity\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController
{
    // --- APPROVAL WORKFLOWS ---
    public function approvals(Request $request)
    {
        $requests = ApprovalRequest::with(['requester', 'actioner'])->latest()->paginate(10);
        $workflows = ApprovalWorkflow::all();

        return view('pages.governance.approvals', compact('requests', 'workflows'));
    }

    public function actionApproval(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'comments' => 'nullable|string',
        ]);

        $appr = ApprovalRequest::findOrFail($id);
        $appr->update([
            'status' => $request->status,
            'comments' => $request->comments,
            'actioned_by' => Auth::id(),
            'actioned_at' => now(),
        ]);

        // If it's a Purchase Order, update the PO model as well
        if ($appr->approvable_type === 'Modules\Procurement\Models\PurchaseOrder') {
            $po = \Modules\Procurement\Models\PurchaseOrder::find($appr->approvable_id);
            if ($po) {
                $po->update(['status' => $request->status, 'approved_by' => Auth::id()]);
            }
        }

        do_action('audit_log', 'actioned_approval', $appr, null, ['status' => $request->status]);

        return redirect()->back()->with('success', "Approval request marked as {$request->status}.");
    }

    // --- AUDIT TRAIL LOGS ---
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('auditable_type', 'like', "%{$s}%")
                    ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        $logs = $query->latest('created_at')->paginate(20);

        return view('pages.governance.audit-logs', compact('logs'));
    }

    // --- ROLES & PERMISSIONS ---
    public function roles(Request $request)
    {
        $roles = Role::with(['permissions', 'users'])->get();
        $permissionsByModule = Permission::all()->groupBy('module');

        return view('pages.settings.roles', compact('roles', 'permissionsByModule'));
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:50',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'is_system' => false,
        ]);

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        do_action('audit_log', 'created_role', $role, null, $role->toArray());

        return redirect()->back()->with('success', "Role {$role->name} created successfully.");
    }

    // --- TENANT PROFILE & SUBSCRIPTION ---
    public function tenantSettings()
    {
        $tenant = TenantContext::getTenant();
        $subscription = Subscription::where('tenant_id', $tenant?->id)->latest()->first();

        return view('pages.settings.tenant', compact('tenant', 'subscription'));
    }

    public function updateTenantSettings(Request $request)
    {
        $tenant = TenantContext::getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string|max:50',
            'gstin' => 'nullable|string|max:15',
            'pan' => 'nullable|string|max:10',
            'state_code' => 'required|string|size:2',
            'address' => 'nullable|string',
        ]);

        $tenant->update($validated);
        do_action('audit_log', 'updated_tenant_settings', $tenant, null, $validated);

        return redirect()->back()->with('success', 'Organization and GST profile updated.');
    }

    // --- API TOKENS (SANCTUM) ---
    public function apiTokens(Request $request)
    {
        $user = Auth::user() ?? User::where('tenant_id', TenantContext::getTenantId())->first();
        $tokens = $user ? $user->tokens()->latest()->get() : collect();

        return view('pages.settings.api-tokens', compact('tokens', 'user'));
    }

    public function createApiToken(Request $request)
    {
        $request->validate([
            'token_name' => 'required|string|max:100',
        ]);

        $user = Auth::user() ?? User::where('tenant_id', TenantContext::getTenantId())->first();
        $token = $user->createToken($request->token_name);

        return redirect()->back()->with('newToken', $token->plainTextToken);
    }
}
