<?php

namespace App\Http\Controllers\Api;

use Modules\Governance\Domain\Models\ApprovalRequest;
use Modules\Inventory\Domain\Models\Product;
use Modules\Procurement\Domain\Models\PurchaseOrder;
use Modules\Sales\Domain\Models\SalesOrder;
use Modules\Inventory\Domain\Models\StockLevel;
use Modules\Accounting\Domain\Models\TaxInvoice;
use Modules\Identity\Domain\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        $deviceName = $request->device_name ?? 'mobile-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Authenticated successfully.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'tenant' => $user->tenant,
            ],
        ]);
    }

    public function products(): JsonResponse
    {
        $products = Product::with(['category', 'uom', 'stockLevels'])->paginate(20);
        return response()->json(['success' => true, 'data' => $products]);
    }

    public function stockLevels(): JsonResponse
    {
        $levels = StockLevel::with(['product', 'warehouse'])->get();
        return response()->json(['success' => true, 'data' => $levels]);
    }

    public function purchaseOrders(): JsonResponse
    {
        $orders = PurchaseOrder::with(['supplier', 'items.product'])->latest()->paginate(15);
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function salesOrders(): JsonResponse
    {
        $orders = SalesOrder::with(['customer', 'items.product'])->latest()->paginate(15);
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function invoices(): JsonResponse
    {
        $invoices = TaxInvoice::with(['customer', 'supplier', 'items'])->latest()->paginate(15);
        return response()->json(['success' => true, 'data' => $invoices]);
    }

    public function pendingApprovals(): JsonResponse
    {
        $approvals = ApprovalRequest::where('status', 'pending')->with('requester')->latest()->get();
        return response()->json(['success' => true, 'data' => $approvals]);
    }

    public function actionApproval(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'comments' => 'nullable|string',
        ]);

        $approval = ApprovalRequest::findOrFail($id);
        $approval->update([
            'status' => $request->status,
            'comments' => $request->comments,
            'actioned_by' => $request->user()?->id,
            'actioned_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Approval marked as {$request->status}.",
            'data' => $approval,
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
                'tenant' => TenantContext::getTenant(),
            ],
        ]);
    }
}
