<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramStudi;
use App\Traits\HasDataTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProdiController extends Controller
{
    use HasDataTable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = ProgramStudi::query();

        $prodiList = $this->applyDataTable($query, $request, [
            'kode_prodi',
            'nama_prodi',
            'jenjang',
            'jenis_program',
            'akreditasi'
        ], 20);

        return Inertia::render('Admin/Akademik/Prodi', [
            'prodiList' => $prodiList,
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProgramStudi $prodi)
    {
        return response()->json($prodi);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProgramStudi $prodi)
    {
        $validated = $request->validate([
            'kode_prodi' => 'required|string|max:50|unique:program_studi,kode_prodi,' . $prodi->id,
            'nama_prodi' => 'required|string|max:255',
            'jenjang' => 'required|string|max:50',
            'jenis_program' => 'required|string|max:50',
            'akreditasi' => 'nullable|string|max:5',
            'is_active' => 'required|boolean',
        ]);

        $prodi->update($validated);

        return redirect()->back()->with('success', 'Program Studi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProgramStudi $prodi)
    {
        $prodi->delete();

        return redirect()->back()->with('success', 'Program Studi berhasil dihapus');
    }
}
