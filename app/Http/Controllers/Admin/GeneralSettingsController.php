<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings/General', [
            'settings' => [
                'app_name' => Setting::getValue('app_name', 'BAAK News'),
                'app_description' => Setting::getValue('app_description', 'Sistem Informasi Akademik'),
                'institute_name' => Setting::getValue('institute_name', 'STIKES Hang Tuah'),
                'institute_abbreviation' => Setting::getValue('institute_abbreviation', 'STIKES-HT'),
                'contact_email' => Setting::getValue('contact_email', ''),
                'contact_phone' => Setting::getValue('contact_phone', ''),
                'contact_address' => Setting::getValue('contact_address', ''),
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
        ]);

        foreach ($validated as $key => $value) {
            Setting::setValue($key, $value);
        }

        return back()->with('success', 'General settings updated successfully.');
    }
}
