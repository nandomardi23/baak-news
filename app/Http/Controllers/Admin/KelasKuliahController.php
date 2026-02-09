<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelasKuliah;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Services\NeoFeederService;
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
            ->paginate(20)
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

    public function show(KelasKuliah $kelasKuliah, NeoFeederService $neoFeeder): Response
    {
        $kelasKuliah->load([
            'programStudi', 
            'mataKuliah', 
            'tahunAkademik', 
            'dosenPengajar',
            'krsDetails.krs.mahasiswa.programStudi'
        ]);

        // Get Peserta List from local KRS data
        $peserta = $kelasKuliah->krsDetails->map(fn($krsDetail) => [
            'id' => $krsDetail->id,
            'nim' => $krsDetail->krs?->mahasiswa?->nim,
            'nama' => $krsDetail->krs?->mahasiswa?->nama,
            'angkatan' => $krsDetail->krs?->mahasiswa?->angkatan,
            'prodi' => $krsDetail->krs?->mahasiswa?->programStudi?->nama_prodi,
        ])->filter(fn($m) => $m['nim'] !== null)->values();

        // If no local data, try fetching from API
        if ($peserta->isEmpty() && $kelasKuliah->id_kelas_kuliah) {
            try {
                $apiResponse = $neoFeeder->getPesertaKelasKuliah($kelasKuliah->id_kelas_kuliah);
                if ($apiResponse && !empty($apiResponse['data'])) {
                    $peserta = collect($apiResponse['data'])->map(fn($item) => [
                        'id' => $item['id_registrasi_mahasiswa'] ?? null,
                        'nim' => $item['nim'] ?? null,
                        'nama' => $item['nama_mahasiswa'] ?? null,
                        'angkatan' => $item['angkatan'] ?? null,
                        'prodi' => $item['nama_program_studi'] ?? null,
                    ])->values();
                }
            } catch (\Exception $e) {
                // Ignore API errors, just show empty
            }
        }

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
                'peserta' => $peserta,
                'total_peserta' => $peserta->count(),
            ],
        ]);
    }


    public function destroy(KelasKuliah $kelasKuliah)
    {
        $kelasKuliah->delete();
        return redirect()->back()->with('success', 'Data kelas kuliah berhasil dihapus');
    }
}
