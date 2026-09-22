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
        $query = $this->buildIndexQuery($request);
        $query = $this->applyIndexSorting($query, $request);

        $kurikulum = $query->paginate($request->input('per_page', 20))
            ->withQueryString()
            ->through(fn($item) => $this->transformIndexData($item));

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

        $matkulQuery = $this->buildMatkulQuery($kurikulum->id_kurikulum, request('search'));
        $matkulQuery = $this->applyMatkulSorting($matkulQuery, request('sort_field', 'semester'), request('sort_direction', 'asc'));

        $matkulKurikulum = $matkulQuery->paginate(request('per_page', 10))
            ->withQueryString()
            ->through(fn($mk) => $this->transformMatkulData($mk));

        return Inertia::render('Admin/Akademik/Kurikulum/Show', [
            'kurikulum' => $this->transformKurikulumData($kurikulum),
            'matkulKurikulum' => $matkulKurikulum,
            'filters' => request()->only(['search', 'per_page', 'sort_field', 'sort_direction']),
        ]);
    }

    private function buildIndexQuery(Request $request)
    {
        $query = Kurikulum::with(['programStudi', 'tahunAkademik']);

        if ($search = $request->input('search')) {
            $query->where('nama_kurikulum', 'like', "%{$search}%");
        }

        if ($prodiId = $request->input('prodi')) {
            $query->whereHas('programStudi', function ($q) use ($prodiId) {
                $q->where('id', $prodiId);
            });
        }

        return $query;
    }

    private function applyIndexSorting($query, Request $request)
    {
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSorts = ['nama_kurikulum', 'id_semester', 'jumlah_sks_lulus', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        return $query->orderBy($sortField, $sortDirection);
    }

    private function transformIndexData(Kurikulum $item): array
    {
        return [
            'id' => $item->id,
            'id_kurikulum' => $item->id_kurikulum,
            'nama_kurikulum' => $item->nama_kurikulum,
            'prodi' => $item->programStudi?->nama_prodi,
            'semester' => $item->tahunAkademik?->nama_semester ?? $item->id_semester,
            'jumlah_sks_lulus' => $item->jumlah_sks_lulus,
            'jumlah_sks_wajib' => $item->jumlah_sks_wajib,
            'jumlah_sks_pilihan' => $item->jumlah_sks_pilihan,
        ];
    }

    private function buildMatkulQuery(string $idKurikulum, ?string $search)
    {
        $matkulQuery = \App\Models\MatkulKurikulum::with('mataKuliah')
            ->where('id_kurikulum', $idKurikulum);

        if ($search) {
            $matkulQuery->whereHas('mataKuliah', function ($q) use ($search) {
                $q->where('nama_matkul', 'like', "%{$search}%")
                    ->orWhere('kode_matkul', 'like', "%{$search}%");
            });
        }

        return $matkulQuery;
    }

    private function applyMatkulSorting($matkulQuery, string $sortField, string $sortDirection)
    {
        if (in_array($sortField, ['kode_matkul', 'nama_matkul', 'sks_mata_kuliah'])) {
            return $matkulQuery->join('mata_kuliah', 'mata_kuliah.id', '=', 'matkul_kurikulum.mata_kuliah_id')
                ->orderBy("mata_kuliah.{$sortField}", $sortDirection)
                ->select('matkul_kurikulum.*');
        }

        return $matkulQuery->orderBy($sortField, $sortDirection);
    }

    private function transformMatkulData($mk): array
    {
        return [
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
        ];
    }

    private function transformKurikulumData(Kurikulum $kurikulum): array
    {
        return [
            'id' => $kurikulum->id,
            'nama_kurikulum' => $kurikulum->nama_kurikulum,
            'prodi' => $kurikulum->programStudi?->nama_prodi,
            'semester' => $kurikulum->tahunAkademik?->nama_semester ?? $kurikulum->id_semester,
            'jumlah_sks_lulus' => $kurikulum->jumlah_sks_lulus,
            'jumlah_sks_wajib' => $kurikulum->jumlah_sks_wajib,
            'jumlah_sks_pilihan' => $kurikulum->jumlah_sks_pilihan,
        ];
    }
}
