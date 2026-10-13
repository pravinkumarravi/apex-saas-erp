<?php

namespace Modules\Assets\Http\Controllers;

use Modules\Assets\Models\Asset;
use Modules\Assets\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetController
{
    public function index(Request $request)
    {
        $assets = Asset::with('category')->latest()->paginate(10);
        $categories = AssetCategory::all();

        return view('pages.assets.index', compact('assets', 'categories'));
    }

    public function store(Request $request)
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
        do_action('audit_log', 'created', $asset, null, $asset->toArray());

        return redirect()->back()->with('success', "Asset {$asset->name} registered.");
    }
}
