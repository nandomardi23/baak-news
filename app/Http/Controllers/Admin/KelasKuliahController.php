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
        $query = KelasKuliah::with(['programStudi', 'mataKuliah', 'tahunAkademik']);

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
        $kelasKuliah->load(['programStudi', 'mataKuliah', 'tahunAkademik', 'krsDetails.krs.mahasiswa']);

        // Try to get mahasiswa from local KRS data first
        $mahasiswaList = $kelasKuliah->krsDetails->map(fn($krsDetail) => [
            'id' => $krsDetail->id,
            'nim' => $krsDetail->krs?->mahasiswa?->nim,
            'nama' => $krsDetail->krs?->mahasiswa?->nama,
            'nama_dosen' => $krsDetail->nama_dosen,
        ])->filter(fn($m) => $m['nim'] !== null)->values();

        // If local data is empty, try fetching from Neo Feeder API
        if ($mahasiswaList->isEmpty() && $kelasKuliah->id_kelas_kuliah) {
            try {
                $pesertaResponse = $neoFeeder->getPesertaKelasKuliah($kelasKuliah->id_kelas_kuliah);
                if ($pesertaResponse && !empty($pesertaResponse['data'])) {
                    $mahasiswaList = collect($pesertaResponse['data'])->map(fn($p, $index) => [
                        'id' => $index + 1,
                        'nim' => $p['nim'] ?? null,
                        'nama' => $p['nama_mahasiswa'] ?? null,
                        'nama_dosen' => null,
                    ])->values();
                }
            } catch (\Exception $e) {
                // Silently fail - just show empty list
            }
        }

        // Get dosen pengajar info
        $dosenPengajar = null;
        if ($kelasKuliah->id_kelas_kuliah) {
            try {
                $dosenResponse = $neoFeeder->getDosenPengajarKelasKuliah($kelasKuliah->id_kelas_kuliah);
                if ($dosenResponse && !empty($dosenResponse['data'])) {
                    $dosenPengajar = $dosenResponse['data'][0]['nama_dosen'] ?? null;
                }
            } catch (\Exception $e) {
                // Silently fail
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
                'dosen_pengajar' => $dosenPengajar,
                'mahasiswa' => $mahasiswaList,
            ],
        ]);
    }

    public function destroy(KelasKuliah $kelasKuliah)
    {
        $kelasKuliah->delete();
        return redirect()->back()->with('success', 'Data kelas kuliah berhasil dihapus');
    }
}
