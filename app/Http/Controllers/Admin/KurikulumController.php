<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KurikulumController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Kurikulum::with(['programStudi', 'tahunAkademik']);

        // Search
        if ($search = $request->input('search')) {
            $query->where('nama_kurikulum', 'like', "%{$search}%");
        }

        // Filter by prodi
        if ($prodiId = $request->input('prodi')) {
            $query->whereHas('programStudi', function ($q) use ($prodiId) {
                $q->where('id', $prodiId);
            });
        }

        $sortField = $request->input('sort_field', 'nama_kurikulum');
        $sortDirection = $request->input('sort_direction', 'asc');

        $allowedSorts = ['nama_kurikulum', 'id_semester', 'jumlah_sks_lulus'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'nama_kurikulum';
        }

        $kurikulum = $query->orderBy($sortField, $sortDirection)
            ->paginate(20)
            ->withQueryString()
            ->through(fn($item) => [
                'id' => $item->id,
                'id_kurikulum' => $item->id_kurikulum,
                'nama_kurikulum' => $item->nama_kurikulum,
                'prodi' => $item->programStudi?->nama_prodi,
                'semester' => $item->tahunAkademik?->nama_semester ?? $item->id_semester,
                'jumlah_sks_lulus' => $item->jumlah_sks_lulus,
                'jumlah_sks_wajib' => $item->jumlah_sks_wajib,
                'jumlah_sks_pilihan' => $item->jumlah_sks_pilihan,
            ]);

        return Inertia::render('Admin/Akademik/Kurikulum/Index', [
            'kurikulum' => $kurikulum,
            'prodiList' => ProgramStudi::orderBy('nama_prodi')->pluck('nama_prodi', 'id'),
            'filters' => [
                'search' => $request->input('search'),
                'prodi' => $request->input('prodi'),
            ],
        ]);
    }

    public function show($id): Response
    {
        $kurikulum = Kurikulum::with(['programStudi', 'tahunAkademik', 'matkulKurikulum.mataKuliah'])
            ->findOrFail($id);

        $matkulKurikulum = $kurikulum->matkulKurikulum->map(fn($mk) => [
            'id' => $mk->id,
            'kode_matkul' => $mk->mataKuliah?->kode_matkul,
            'nama_matkul' => $mk->mataKuliah?->nama_matkul,
            'semester' => $mk->semester,
            'sks_mata_kuliah' => $mk->sks_mata_kuliah,
            'sks_tatap_muka' => $mk->sks_tatap_muka,
            'sks_praktek' => $mk->sks_praktek,
            'sks_praktek_lapangan' => $mk->sks_praktek_lapangan,
            'sks_simulasi' => $mk->sks_simulasi,
            'apakah_wajib' => $mk->apakah_wajib ? 'Wajib' : 'Pilihan',
        ])->sortBy('semester')->values();

        return Inertia::render('Admin/Akademik/Kurikulum/Show', [
            'kurikulum' => [
                'id' => $kurikulum->id,
                'nama_kurikulum' => $kurikulum->nama_kurikulum,
                'prodi' => $kurikulum->programStudi?->nama_prodi,
                'semester' => $kurikulum->tahunAkademik?->nama_semester ?? $kurikulum->id_semester,
                'jumlah_sks_lulus' => $kurikulum->jumlah_sks_lulus,
                'jumlah_sks_wajib' => $kurikulum->jumlah_sks_wajib,
                'jumlah_sks_pilihan' => $kurikulum->jumlah_sks_pilihan,
            ],
            'matkulKurikulum' => $matkulKurikulum,
        ]);
    }
}
