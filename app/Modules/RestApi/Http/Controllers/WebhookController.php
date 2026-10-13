<?php

namespace Modules\RestApi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RestApi\Models\WebhookSubscription;

class WebhookController extends Controller
{
    public function index()
    {
        $webhooks = WebhookSubscription::latest()->paginate(10);
        $availableEvents = [
            'after_sales_order_created' => 'Sales Order Confirmed / Created',
            'after_customer_created' => 'New Customer Created',
            'after_delivery_challan_created' => 'Goods Dispatched / Delivery Challan',
            'after_lead_created' => 'New CRM Lead Logged',
            '*' => 'All System Events (Wildcard)',
        ];

        return view('rest-api::webhooks', compact('webhooks', 'availableEvents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'target_url' => 'required|url|max:255',
            'events' => 'required|array',
        ]);

        $subscription = WebhookSubscription::create($validated);
        do_action('webhook_subscription_created', $subscription);

        return redirect()->back()->with('success', "Webhook subscription '{$subscription->name}' registered successfully.");
    }

    public function destroy(int $id)
    {
        $subscription = WebhookSubscription::findOrFail($id);
        $subscription->delete();
        do_action('webhook_subscription_deleted', $id);

        return redirect()->back()->with('success', 'Webhook subscription deleted successfully.');
    }

    public function testPing(int $id)
    {
        $webhook = WebhookSubscription::findOrFail($id);
        $webhook->update([
            'last_dispatched_at' => now(),
        ]);

        return redirect()->back()->with('success', "Simulated ping dispatched to {$webhook->target_url}.");
    }
}
