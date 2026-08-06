<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        $heroBgPath = Setting::getValue('hero_background_image');

        return Inertia::render('Admin/Settings/General', [
            'settings' => [
                'app_name' => Setting::getValue('app_name', 'BAAK'),
                'app_description' => Setting::getValue('app_description', 'Sistem Informasi Akademik'),
                'institute_name' => Setting::getValue('institute_name', 'STIKES Hang Tuah'),
                'institute_abbreviation' => Setting::getValue('institute_abbreviation', 'STIKES-HT'),
                'contact_email' => Setting::getValue('contact_email', ''),
                'contact_phone' => Setting::getValue('contact_phone', ''),
                'contact_address' => Setting::getValue('contact_address', ''),
                'hero_background_image' => $heroBgPath ? Storage::url($heroBgPath) : null,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:255',
            'institute_name' => 'required|string|max:255',
            'institute_abbreviation' => 'required|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:500',
            'hero_background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_hero_background' => 'nullable|boolean',
        ]);

        // Handle hero background image upload
        if ($request->hasFile('hero_background_image')) {
            // Delete old image if exists
            $oldPath = Setting::getValue('hero_background_image');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('hero_background_image')->store('settings', 'public');
            Setting::setValue('hero_background_image', $path, false, 'Background image for Hero section on landing page');
        } elseif ($request->boolean('remove_hero_background')) {
            $oldPath = Setting::getValue('hero_background_image');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            Setting::setValue('hero_background_image', null, false, 'Background image for Hero section on landing page');
        }

        // Save text-based settings
        $textSettings = collect($validated)->except(['hero_background_image', 'remove_hero_background']);
        foreach ($textSettings as $key => $value) {
            Setting::setValue($key, $value);
        }

        return back()->with('success', 'General settings updated successfully.');
    }
}
