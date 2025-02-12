<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function update(Request $request)
    {
        // Validate settings
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'site_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'site_favicon' => 'nullable|image|mimes:jpg,jpeg,png,ico|max:1024',
            'site_og_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'site_meta_keywords' => 'nullable|string|max:255',
            'cache_lifetime' => 'required|integer|min:0',
        ]);

        // Save files and update settings
        if ($request->hasFile('site_logo')) {
            $validated['site_logo'] = $request->file('site_logo')->store('settings', 'public');
        }
        if ($request->hasFile('site_favicon')) {
            $validated['site_favicon'] = $request->file('site_favicon')->store('settings', 'public');
        }
        if ($request->hasFile('site_og_image')) {
            $validated['site_og_image'] = $request->file('site_og_image')->store('settings', 'public');
        }
        if ($request->cache_enabled == 'on') {
            setting(['cache_enabled' => 1]);
        } else {
            setting(['cache_enabled' => 0]);
        }

        // Save settings
        foreach ($validated as $key => $value) {
            setting([$key => $value]);
        }

        return back()->with('success', 'Cập nhật cài đặt thành công');
    }
}
