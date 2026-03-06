<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelasKuliah;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KelasKuliahController extends Controller
{
    public function index(Request $request): Response
    {
        $query = KelasKuliah::with(['programStudi', 'mataKuliah', 'tahunAkademik', 'dosenPengajar']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas_kuliah', 'like', "%{$search}%")
                    ->orWhere('kode_mata_kuliah', 'like', "%{$search}%")
                    ->orWhere('nama_mata_kuliah', 'like', "%{$search}%");
            });
        }

        // Filter by prodi
        if ($prodiId = $request->input('prodi')) {
            $query->where('program_studi_id', $prodiId);
        }

        // Filter by semester
        if ($semesterId = $request->input('semester')) {
            $query->where('tahun_akademik_id', $semesterId);
        }

        $sortField = $request->input('sort_field', 'nama_kelas_kuliah');
        $sortDirection = $request->input('sort_direction', 'asc');

        $allowedSorts = ['nama_kelas_kuliah', 'kode_mata_kuliah', 'nama_mata_kuliah', 'sks'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'nama_kelas_kuliah';
        }

        $kelasKuliah = $query->orderBy($sortField, $sortDirection)
            ->paginate($request->input('per_page', 20))
            ->withQueryString()
            ->through(fn($item) => [
                'id' => $item->id,
                'id_kelas_kuliah' => $item->id_kelas_kuliah,
                'nama_kelas_kuliah' => $item->nama_kelas_kuliah,
                'kode_mata_kuliah' => $item->kode_mata_kuliah,
                'nama_mata_kuliah' => $item->nama_mata_kuliah,
                'sks' => $item->sks,
                'kapasitas' => $item->kapasitas,
                'prodi' => $item->programStudi?->nama_prodi,
                'semester' => $item->tahunAkademik?->nama_semester,
                'program_studi_id' => $item->program_studi_id,
                'tahun_akademik_id' => $item->tahun_akademik_id,
                'dosen_pengajar' => $item->dosenPengajar->map(fn($d) => [
                    'id' => $d->id,
                    'nama' => $d->nama_lengkap,
                ]),
            ]);

        return Inertia::render('Admin/KelasKuliah/Index', [
            'kelasKuliah' => $kelasKuliah,
            'prodiList' => ProgramStudi::orderBy('nama_prodi')->pluck('nama_prodi', 'id'),
            'semesterList' => TahunAkademik::orderBy('id_semester', 'desc')->pluck('nama_semester', 'id'),
            'filters' => [
                'search' => $request->input('search'),
                'prodi' => $request->input('prodi'),
                'semester' => $request->input('semester'),
            ],
        ]);
    }

    public function show(KelasKuliah $kelasKuliah): Response
    {
        $kelasKuliah->load([
            'programStudi',
            'mataKuliah',
            'tahunAkademik',
            'dosenPengajar',
        ]);

        // Get paginated Peserta List
        $pesertaQuery = \App\Models\KrsDetail::with(['krs.mahasiswa.programStudi'])
            ->where('id_kelas_kuliah', $kelasKuliah->id_kelas_kuliah);

        if ($search = request('search')) {
            $pesertaQuery->whereHas('krs.mahasiswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        $sortField = request('sort_field', 'nim');
        $sortDirection = request('sort_direction', 'asc');

        // Custom sorting since fields are in relations
        if (in_array($sortField, ['nim', 'nama', 'angkatan'])) {
            $pesertaQuery->join('krs', 'krs.id', '=', 'krs_detail.krs_id')
                ->join('mahasiswa', 'mahasiswa.id', '=', 'krs.mahasiswa_id')
                ->orderBy("mahasiswa.{$sortField}", $sortDirection)
                ->select('krs_detail.*'); // ensure we don't mix up ids
        } else if ($sortField === 'prodi') {
            $pesertaQuery->join('krs', 'krs.id', '=', 'krs_detail.krs_id')
                ->join('mahasiswa', 'mahasiswa.id', '=', 'krs.mahasiswa_id')
                ->join('program_studi', 'program_studi.id', '=', 'mahasiswa.program_studi_id')
                ->orderBy('program_studi.nama_prodi', $sortDirection)
                ->select('krs_detail.*');
        } else {
            $pesertaQuery->orderBy('krs_detail.id', $sortDirection);
        }

        $peserta = $pesertaQuery->paginate(request('per_page', 10))
            ->withQueryString()
            ->through(fn($krsDetail) => [
                'id' => $krsDetail->id,
                'nim' => $krsDetail->krs?->mahasiswa?->nim,
                'nama' => $krsDetail->krs?->mahasiswa?->nama,
                'angkatan' => $krsDetail->krs?->mahasiswa?->angkatan,
                'prodi' => $krsDetail->krs?->mahasiswa?->programStudi?->nama_prodi,
            ]);

        // Just get count for the top card
        $totalPeserta = \App\Models\KrsDetail::where('id_kelas_kuliah', $kelasKuliah->id_kelas_kuliah)->count();

        return Inertia::render('Admin/KelasKuliah/Show', [
            'kelasKuliah' => [
                'id' => $kelasKuliah->id,
                'id_kelas_kuliah' => $kelasKuliah->id_kelas_kuliah,
                'nama_kelas_kuliah' => $kelasKuliah->nama_kelas_kuliah,
                'kode_mata_kuliah' => $kelasKuliah->kode_mata_kuliah,
                'nama_mata_kuliah' => $kelasKuliah->nama_mata_kuliah,
                'sks' => $kelasKuliah->sks,
                'kapasitas' => $kelasKuliah->kapasitas,
                'prodi' => $kelasKuliah->programStudi?->nama_prodi,
                'semester' => $kelasKuliah->tahunAkademik?->nama_semester ?? $kelasKuliah->id_semester,
                'dosen_pengajar' => $kelasKuliah->dosenPengajar->map(fn($d) => [
                    'id' => $d->id,
                    'nama' => $d->nama_lengkap,
                    'nidn' => $d->nidn,
                    'sks_substansi' => $d->pivot->sks_substansi_total,
                    'rencana_tm' => $d->pivot->rencana_tatap_muka,
                    'realisasi_tm' => $d->pivot->realisasi_tatap_muka,
                    'evaluasi' => $d->pivot->nama_jenis_evaluasi,
                ]),
                'total_peserta' => $totalPeserta,
            ],
            'peserta' => $peserta,
            'filters' => request()->only(['search', 'per_page', 'sort_field', 'sort_direction']),
        ]);
    }


    public function destroy(KelasKuliah $kelasKuliah)
    {
        $kelasKuliah->delete();
        return redirect()->back()->with('success', 'Data kelas kuliah berhasil dihapus');
    }
}
