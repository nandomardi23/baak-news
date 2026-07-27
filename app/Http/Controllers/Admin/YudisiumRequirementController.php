<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\YudisiumRequirement;
use App\Models\ProgramStudi;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class YudisiumRequirementController extends Controller
{
    use \App\Traits\HasDataTable;

    public function index(Request $request): Response
    {
        $query = YudisiumRequirement::query()->with('programStudi');
        $requirements = $this->applyDataTable($query, $request, ['nama_syarat', 'deskripsi', 'programStudi.nama_prodi'], 15);
        
        $requirements->through(function ($item) {
            return [
                'id' => $item->id,
                'nama_syarat' => $item->nama_syarat,
                'deskripsi' => $item->deskripsi,
                'is_upload_required' => $item->is_upload_required,
                'is_active' => $item->is_active,
                'program_studi_id' => $item->program_studi_id,
                'prodi' => $item->programStudi ? $item->programStudi->nama_prodi : 'Semua Prodi',
            ];
        });

        return Inertia::render('Admin/Yudisium/Requirements', [
            'requirements' => $requirements,
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
            'prodiList' => ProgramStudi::orderBy('nama_prodi')->pluck('nama_prodi', 'id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_studi_id' => 'nullable|exists:program_studi,id',
            'nama_syarat' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_upload_required' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $requirement = YudisiumRequirement::create($validated);
        ActivityLog::log('created', "Menambahkan syarat yudisium: {$requirement->nama_syarat}");

        return redirect()->back()->with('success', 'Syarat Yudisium berhasil ditambahkan');
    }

    public function update(Request $request, YudisiumRequirement $requirement): RedirectResponse
    {
        $validated = $request->validate([
            'program_studi_id' => 'nullable|exists:program_studi,id',
            'nama_syarat' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_upload_required' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $requirement->update($validated);
        ActivityLog::log('updated', "Mengubah syarat yudisium: {$requirement->nama_syarat}");

        return redirect()->back()->with('success', 'Syarat Yudisium berhasil diperbarui');
    }

    public function destroy(YudisiumRequirement $requirement): RedirectResponse
    {
        try {
            $nama = $requirement->nama_syarat;
            $requirement->delete();
            ActivityLog::log('deleted', "Menghapus syarat yudisium: {$nama}");

            return redirect()->back()->with('success', 'Syarat Yudisium berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'Syarat tidak dapat dihapus karena sudah ada mahasiswa yang menggunakannya.');
        }
    }
}
