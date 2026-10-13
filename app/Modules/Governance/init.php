<?php

// Module Initialization for Governance & Audits

register_activation_hook('governance', function () {
    // Initial activation tasks
});

add_action('admin_init', function () {
    // Menu or admin initialization
});

add_action('audit_log', function (string $action, $model, ?array $oldValues = null, ?array $newValues = null) {
    if (class_exists(\Modules\Governance\Models\AuditLog::class)) {
        \Modules\Governance\Models\AuditLog::log($action, $model, $oldValues, $newValues);
    }
});

// Hook: Create approval request when PO requires approval
add_action('procurement_order_approval_needed', function ($po) {
    if (class_exists(\Modules\Governance\Models\ApprovalRequest::class)) {
        \Modules\Governance\Models\ApprovalRequest::create([
            'approvable_type' => get_class($po),
            'approvable_id' => $po->id,
            'current_step' => 1,
            'total_steps' => 2,
            'status' => 'pending',
            'requested_by' => \Illuminate\Support\Facades\Auth::id(),
        ]);
    }
});

// Hook: Mark approval request approved when PO is approved
add_action('procurement_order_approved', function ($po) {
    if (class_exists(\Modules\Governance\Models\ApprovalRequest::class)) {
        $approvalReq = \Modules\Governance\Models\ApprovalRequest::where('approvable_type', get_class($po))
            ->where('approvable_id', $po->id)
            ->first();
        if ($approvalReq) {
            $approvalReq->update([
                'status' => 'approved',
                'actioned_by' => \Illuminate\Support\Facades\Auth::id(),
                'actioned_at' => now(),
            ]);
        }
    }
});


