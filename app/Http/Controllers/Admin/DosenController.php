<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DosenController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Dosen::with('programStudi');

        // Search
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filter by prodi
        if ($prodiId = $request->input('prodi')) {
            $query->where('program_studi_id', $prodiId);
        }

        // Filter by status
        if ($request->input('status') === 'aktif') {
            $query->active();
        }

        $sortField = $request->input('sort_field', 'nama');
        $sortDirection = $request->input('sort_direction', 'asc');

        $allowedSorts = ['nidn', 'nip', 'nama', 'jabatan_fungsional', 'status_aktif'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'nama';
        }

        $dosen = $query->orderBy($sortField, $sortDirection)
            ->paginate(20)
            ->withQueryString()
            ->through(fn($item) => [
                'id' => $item->id,
                'id_dosen' => $item->id_dosen,
                'nidn' => $item->nidn,
                'nip' => $item->nip,
                'nama' => $item->nama,
                'nama_lengkap' => $item->nama_lengkap,
                'jenis_kelamin' => $item->jenis_kelamin,
                'jabatan_fungsional' => $item->jabatan_fungsional,
                'status_aktif' => $item->status_aktif,
                'prodi' => $item->programStudi?->nama_prodi,
                'program_studi_id' => $item->program_studi_id,
            ]);

        return Inertia::render('Admin/Dosen/Index', [
            'dosen' => $dosen,
            'prodiList' => ProgramStudi::orderBy('nama_prodi')->pluck('nama_prodi', 'id'),
            'filters' => [
                'search' => $request->input('search'),
                'prodi' => $request->input('prodi'),
                'status' => $request->input('status'),
            ],
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nidn' => 'nullable|string|max:20|unique:dosen,nidn',
            'nip' => 'nullable|string|max:20|unique:dosen,nip',
            'nama_dosen' => 'required|string|max:255',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan_fungsional' => 'nullable|string|max:100',
            'program_studi_id' => 'nullable|exists:program_studi,id',
            'status_aktif' => 'nullable|string',
        ]);

        // Map nama_dosen to descriptors if needed, usually just nama
        $validated['nama'] = $validated['nama_dosen'];
        unset($validated['nama_dosen']);

        Dosen::create($validated);

        return redirect()->back()->with('success', 'Data dosen berhasil ditambahkan (Note: Sinkronisasi Neo Feeder disarankan)');
    }

    public function update(Request $request, Dosen $dosen)
    {
        $validated = $request->validate([
            'nidn' => 'nullable|string|max:20|unique:dosen,nidn,' . $dosen->id,
            'nip' => 'nullable|string|max:20|unique:dosen,nip,' . $dosen->id,
            'nama_dosen' => 'required|string|max:255',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan_fungsional' => 'nullable|string|max:100',
            'program_studi_id' => 'nullable|exists:program_studi,id',
            'status_aktif' => 'nullable|string',
        ]);

         // Map nama_dosen to descriptors if needed
        $validated['nama'] = $validated['nama_dosen'];
        unset($validated['nama_dosen']);

        $dosen->update($validated);

        return redirect()->back()->with('success', 'Data dosen berhasil diperbarui');
    }

    public function destroy(Dosen $dosen)
    {
        $dosen->delete();
        return redirect()->back()->with('success', 'Data dosen berhasil dihapus');
    }
}

