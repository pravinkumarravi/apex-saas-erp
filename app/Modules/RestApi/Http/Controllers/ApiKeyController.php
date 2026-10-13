<?php

namespace Modules\RestApi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RestApi\Models\ApiKey;

class ApiKeyController extends Controller
{
    public function index()
    {
        $keys = ApiKey::where('is_active', true)->latest()->get();
        $availableScopes = [
            'sales:read' => 'Read Sales Orders & Delivery Challans',
            'sales:write' => 'Create & Update Sales Orders',
            'crm:read' => 'Read Customers & Leads',
            'crm:write' => 'Create & Modify Customers',
            'inventory:read' => 'Read Product Catalog & Stock Levels',
            'inventory:write' => 'Record Stock Adjustments',
            '*' => 'Full Administrative Access',
        ];

        return view('rest-api::keys', compact('keys', 'availableScopes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'abilities' => 'nullable|array',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $abilities = $request->input('abilities', ['*']);
        $result = ApiKey::createKey(
            $request->name,
            $abilities,
            $request->expires_at
        );

        do_action('api_key_generated', $result['api_key']);

        return redirect()->back()->with([
            'success' => "API Key '{$request->name}' generated successfully.",
            'new_token' => $result['plain_text_token'],
        ]);
    }

    public function destroy(int $id)
    {
        $key = ApiKey::findOrFail($id);
        $key->update(['is_active' => false]);
        do_action('api_key_revoked', $id);

        return redirect()->back()->with('success', 'API Key revoked successfully.');
    }
}
