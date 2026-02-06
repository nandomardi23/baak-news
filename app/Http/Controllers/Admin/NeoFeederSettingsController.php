<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NeoFeederSettingsController extends Controller
{
    public function index(): Response
    {
        $settings = [
            'url' => Setting::getValue('neo_feeder_url', ''),
            'username' => Setting::getValue('neo_feeder_username', ''),
            'password' => '',
            'has_password' => Setting::hasValue('neo_feeder_password'),
        ];

        return Inertia::render('Admin/Settings/NeoFeeder', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        // Save URL (not encrypted)
        Setting::setValue('neo_feeder_url', $validated['url'], false, 'Neo Feeder API URL');

        // Save Username (not encrypted)
        Setting::setValue('neo_feeder_username', $validated['username'], false, 'Neo Feeder Username');

        // Save Password (encrypted) - only if provided
        if (!empty($validated['password'])) {
            Setting::setValue('neo_feeder_password', $validated['password'], true, 'Neo Feeder Password (encrypted)');
        }

        return back()->with('success', 'Pengaturan Neo Feeder berhasil disimpan');
    }

    public function testConnection(): JsonResponse
    {
        try {
            $url = Setting::getValue('neo_feeder_url', '');
            $username = Setting::getValue('neo_feeder_username', '');
            $password = Setting::getValue('neo_feeder_password', '');

            if (empty($url) || empty($username) || empty($password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kredensial belum lengkap. Pastikan URL, Username, dan Password sudah diisi dan disimpan.',
                ]);
            }

            // Make direct test request
            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'verify' => false,
            ]);

            $response = $client->post($url, [
                'json' => [
                    'act' => 'GetToken',
                    'username' => $username,
                    'password' => $password,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['data']['token'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi berhasil! Token diterima.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan token: ' . ($data['error_desc'] ?? 'Periksa kredensial.'),
            ]);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server Neo Feeder. Periksa URL dan koneksi internet.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}
