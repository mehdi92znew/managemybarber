<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = AdminSetting::all();
        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        foreach ($request->settings as $setting) {
            AdminSetting::query()->where('key', '=', $setting['key'])->update([
                'value' => is_array($setting['value']) ? json_encode($setting['value']) : $setting['value']
            ]);
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
