<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JabatanController extends Controller
{
    public function index(): Response
    {
        $jabatan = Jabatan::orderBy('nama_jabatan')->get();

        return Inertia::render('Admin/Jabatan/Index', [
            'jabatan' => $jabatan,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan',
            'kode_jabatan' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        Jabatan::create($validated);

        return redirect()->back()->with('success', 'Jabatan berhasil ditambahkan');
    }

    public function update(Request $request, Jabatan $jabatan): RedirectResponse
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan,' . $jabatan->id,
            'kode_jabatan' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $jabatan->update($validated);

        return redirect()->back()->with('success', 'Jabatan berhasil diperbarui');
    }

    public function destroy(Jabatan $jabatan): RedirectResponse
    {
        $jabatan->delete();

        return redirect()->back()->with('success', 'Jabatan berhasil dihapus');
    }
}
