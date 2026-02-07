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
    use \App\Traits\HasDataTable;

    public function index(Request $request): Response
    {
        $query = Jabatan::query();

        // Specific filter (Active) if needed, unrelated to global search
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $jabatan = $this->applyDataTable($query, $request, ['nama_jabatan', 'kode_jabatan'], 10);

        return Inertia::render('Admin/Jabatan/Index', [
            'jabatan' => $jabatan,
            'filters' => $request->only(['search', 'sort_field', 'sort_direction', 'is_active']),
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
