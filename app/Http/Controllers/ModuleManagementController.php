<?php

namespace App\Http\Controllers;

use App\Services\ModuleInstaller;
use App\Services\ModuleManager;
use Illuminate\Http\Request;

class ModuleManagementController extends Controller
{
    protected ModuleManager $moduleManager;
    protected ModuleInstaller $moduleInstaller;

    public function __construct(ModuleManager $moduleManager, ModuleInstaller $moduleInstaller)
    {
        $this->moduleManager = $moduleManager;
        $this->moduleInstaller = $moduleInstaller;
    }

    public function index()
    {
        $modules = $this->moduleManager->discoverModules();
        return view('pages.settings.modules', compact('modules'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'module_zip' => 'required|file|mimes:zip|max:51200', // max 50MB
        ]);

        $result = $this->moduleInstaller->installFromZip($request->file('module_zip'));

        if ($result['success']) {
            return redirect()->route('settings.modules.index')->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function toggle(Request $request, string $slug)
    {
        $enable = $request->boolean('enable', true);
        $this->moduleManager->toggleModule($slug, $enable);

        $action = $enable ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Module [{$slug}] has been {$action} for your organization.");
    }

    public function destroy(string $slug)
    {
        $this->moduleInstaller->uninstallModule($slug);
        return redirect()->back()->with('success', "Module [{$slug}] uninstalled and removed successfully.");
    }
}
