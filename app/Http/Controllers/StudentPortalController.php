<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentPortalController extends Controller
{
    public function showVerify(Mahasiswa $mahasiswa): Response
    {
        return Inertia::render('Landing/VerifyIdentity', [
            'mahasiswa_id' => $mahasiswa->id,
        ]);
    }

    public function processVerify(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $request->validate([
            'nim' => 'required|string',
            'tanggal_lahir' => 'required|date',
        ]);

        if (
            $mahasiswa->nim === $request->nim &&
            $mahasiswa->tanggal_lahir &&
            $mahasiswa->tanggal_lahir->format('Y-m-d') === $request->tanggal_lahir
        ) {
            $request->session()->put('verified_mahasiswa_id', $mahasiswa->id);
            $request->session()->put('verified_mahasiswa_at', now());
            return redirect()->route('landing.dokumen', $mahasiswa->id);
        }

        return back()->withErrors([
            'identity' => 'NIM atau tanggal lahir tidak sesuai.',
        ]);
    }
}
