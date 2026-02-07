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
}

