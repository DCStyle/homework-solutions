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
            'site_url' => 'required|url|max:255',
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
            $validated['site_logo'] = $request->file('site_logo')->store('settings', 's3');
        }
        if ($request->hasFile('site_favicon')) {
            $validated['site_favicon'] = $request->file('site_favicon')->store('settings', 's3');
        }
        if ($request->hasFile('site_og_image')) {
            $validated['site_og_image'] = $request->file('site_og_image')->store('settings', 's3');
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

    public function home()
    {
        return view('admin.settings.home');
    }

    public function updateHome(Request $request)
    {
        // First, get the actual instruction steps (excluding the empty last one)
        $instructionSteps = collect($request->instruction_steps)
            ->filter(function ($step) {
                return !empty($step['title']) || !empty($step['description']);
            })->values()->all();

        // Replace the steps in the request with the filtered ones
        $request->merge(['instruction_steps' => $instructionSteps]);

        $validated = $request->validate([
            'home_hero_description' => 'required|string',
            'home_hero_banner' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'home_hero_banner_url' => 'nullable|string|max:255',
            'home_instruction_banner' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'instruction_steps' => 'required|array|min:1',
            'instruction_steps.*' => 'required|array', // Validate each step is an array
            'instruction_steps.*.title' => 'required|string|max:255',
            'instruction_steps.*.description' => 'required|string',
        ]);

        if ($request->hasFile('home_hero_banner')) {
            $validated['home_hero_banner'] = $request->file('home_hero_banner')->store('settings', 's3');
        }

        if ($request->hasFile('home_instruction_banner')) {
            $validated['home_instruction_banner'] = $request->file('home_instruction_banner')->store('settings', 's3');
        }

        // Convert instruction steps to JSON before saving
        $validated['home_instruction_steps'] = json_encode(array_values($validated['instruction_steps']));
        unset($validated['instruction_steps']);

        foreach ($validated as $key => $value) {
            setting([$key => $value]);
        }

        return back()->with('success', 'Cập nhật cài đặt trang chủ thành công');
    }
}
