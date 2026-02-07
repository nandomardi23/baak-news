<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MataKuliahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = MataKuliah::with('programStudi');

        if ($request->filled('prodi')) {
            $query->where('id_prodi', $request->prodi);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_matkul', 'like', "%{$request->search}%")
                  ->orWhere('kode_matkul', 'like', "%{$request->search}%");
            });
        }

        $sortField = $request->input('sort_field', 'kode_matkul');
        $sortDirection = $request->input('sort_direction', 'asc');
        $allowedSorts = ['kode_matkul', 'nama_matkul', 'sks_mata_kuliah', 'sks_teori', 'sks_praktek'];
        
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'kode_matkul';
        }

        $mataKuliah = $query->orderBy($sortField, $sortDirection)
            ->paginate(20)
            ->through(fn($mk) => [
                'id' => $mk->id,
                'kode_matkul' => $mk->kode_matkul,
                'nama_matkul' => $mk->nama_matkul,
                'sks_mata_kuliah' => $mk->sks_mata_kuliah,
                'sks_teori' => $mk->sks_teori,
                'sks_praktek' => $mk->sks_praktek,
                'prodi' => $mk->programStudi?->nama_prodi,
                'id_prodi' => $mk->id_prodi, // needed for edit
            ]);

        $prodiList = ProgramStudi::active()->orderBy('nama_prodi')->get(['id', 'id_prodi', 'nama_prodi']);

        return Inertia::render('Admin/Akademik/MataKuliah', [
            'mataKuliah' => $mataKuliah,
            'prodiList' => $prodiList,
            'filters' => $request->only(['prodi', 'search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_matkul' => 'required|string|max:50|unique:mata_kuliah,kode_matkul',
            'nama_matkul' => 'required|string|max:255',
            'sks_mata_kuliah' => 'required|integer|min:0',
            'sks_teori' => 'nullable|integer|min:0',
            'sks_praktek' => 'nullable|integer|min:0',
            'id_prodi' => 'nullable|exists:program_studi,id_prodi',
        ]);

        MataKuliah::create($validated);

        return redirect()->back()->with('success', 'Mata Kuliah berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MataKuliah $matakuliah)
    {
        // Parameter name 'matakuliah' must match route param if using resource. 
        // Route::resource('matakuliah') -> param is {matakuliah}
        
        $validated = $request->validate([
            'kode_matkul' => 'required|string|max:50|unique:mata_kuliah,kode_matkul,' . $matakuliah->id,
            'nama_matkul' => 'required|string|max:255',
            'sks_mata_kuliah' => 'required|integer|min:0',
            'sks_teori' => 'nullable|integer|min:0',
            'sks_praktek' => 'nullable|integer|min:0',
            'id_prodi' => 'nullable|exists:program_studi,id_prodi',
        ]);

        $matakuliah->update($validated);

        return redirect()->back()->with('success', 'Mata Kuliah berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MataKuliah $matakuliah)
    {
        $matakuliah->delete();
        return redirect()->back()->with('success', 'Mata Kuliah berhasil dihapus');
    }
}
