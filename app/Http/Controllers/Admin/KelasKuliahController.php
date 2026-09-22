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
    use \App\Traits\HasDataTable;

    public function index(Request $request): Response
    {
        $query = $this->buildIndexQuery($request);
        $allowedSorts = ['nama_kelas_kuliah', 'kode_mata_kuliah', 'nama_mata_kuliah', 'sks', 'created_at'];

        $kelasKuliah = $this->dataTableQuery($query, $request, $allowedSorts, 'created_at', 'desc')
            ->through(fn($item) => $this->transformIndexData($item));

        return Inertia::render('Admin/KelasKuliah/Index', [
            'kelasKuliah' => $kelasKuliah,
            'prodiList' => ProgramStudi::orderBy('nama_prodi')->pluck('nama_prodi', 'id'),
            'semesterList' => TahunAkademik::orderBy('id_semester', 'desc')->get(['id', 'nama_semester']),
            'filters' => [
                'search' => $request->input('search'),
                'prodi' => $request->input('prodi'),
                'semester' => $request->input('semester'),
            ],
        ]);
    }

    private function buildIndexQuery(Request $request)
    {
        $query = KelasKuliah::with(['programStudi', 'mataKuliah', 'tahunAkademik', 'dosenPengajar'])
            ->withCount('krsDetails as peserta_count');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas_kuliah', 'like', "%{$search}%")
                    ->orWhere('kode_mata_kuliah', 'like', "%{$search}%")
                    ->orWhere('nama_mata_kuliah', 'like', "%{$search}%");
            });
        }

        if ($prodiId = $request->input('prodi')) {
            $query->where('program_studi_id', $prodiId);
        }

        if ($semesterId = $request->input('semester')) {
            $query->where('tahun_akademik_id', $semesterId);
        }

        return $query;
    }

    private function transformIndexData(KelasKuliah $item): array
    {
        return [
            'id' => $item->id,
            'id_kelas_kuliah' => $item->id_kelas_kuliah,
            'nama_kelas_kuliah' => $item->nama_kelas_kuliah,
            'kode_mata_kuliah' => $item->kode_mata_kuliah,
            'nama_mata_kuliah' => $item->nama_mata_kuliah,
            'sks' => $item->sks,
            'kapasitas' => $item->kapasitas,
            'peserta' => $item->peserta_count,
            'prodi' => $item->programStudi?->nama_prodi,
            'semester' => $item->tahunAkademik?->nama_semester,
            'program_studi_id' => $item->program_studi_id,
            'tahun_akademik_id' => $item->tahun_akademik_id,
            'dosen_pengajar' => $item->dosenPengajar->map(fn($d) => [
                'id' => $d->id,
                'nama' => $d->nama_lengkap,
            ]),
        ];
    }

    public function show(KelasKuliah $kelasKuliah): Response
    {
        $kelasKuliah->load([
            'programStudi',
            'mataKuliah',
            'tahunAkademik',
            'dosenPengajar',
        ]);

        $pesertaQuery = $this->buildPesertaQuery($kelasKuliah->id_kelas_kuliah, request('search'));
        $pesertaQuery = $this->applyPesertaSorting($pesertaQuery, request('sort_field', 'nim'), request('sort_direction', 'asc'));

        $peserta = $pesertaQuery->paginate(request('per_page', 10))
            ->withQueryString()
            ->through(fn($krsDetail) => $this->transformPesertaData($krsDetail));

        $totalPeserta = \App\Models\KrsDetail::where('id_kelas_kuliah', $kelasKuliah->id_kelas_kuliah)->count();

        return Inertia::render('Admin/KelasKuliah/Show', [
            'kelasKuliah' => $this->transformKelasKuliahShowData($kelasKuliah, $totalPeserta),
            'peserta' => $peserta,
            'filters' => request()->only(['search', 'per_page', 'sort_field', 'sort_direction']),
        ]);
    }

    private function buildPesertaQuery(string $idKelasKuliah, ?string $search)
    {
        $pesertaQuery = \App\Models\KrsDetail::with(['krs.mahasiswa.programStudi'])
            ->where('id_kelas_kuliah', $idKelasKuliah);

        if ($search) {
            $pesertaQuery->whereHas('krs.mahasiswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }
        
        return $pesertaQuery;
    }

    private function applyPesertaSorting($pesertaQuery, string $sortField, string $sortDirection)
    {
        if (in_array($sortField, ['nim', 'nama', 'angkatan'])) {
            return $pesertaQuery->join('krs', 'krs.id', '=', 'krs_detail.krs_id')
                ->join('mahasiswa', 'mahasiswa.id', '=', 'krs.mahasiswa_id')
                ->orderBy("mahasiswa.{$sortField}", $sortDirection)
                ->select('krs_detail.*');
        } else if ($sortField === 'prodi') {
            return $pesertaQuery->join('krs', 'krs.id', '=', 'krs_detail.krs_id')
                ->join('mahasiswa', 'mahasiswa.id', '=', 'krs.mahasiswa_id')
                ->join('program_studi', 'program_studi.id', '=', 'mahasiswa.program_studi_id')
                ->orderBy('program_studi.nama_prodi', $sortDirection)
                ->select('krs_detail.*');
        }

        return $pesertaQuery->orderBy('krs_detail.id', $sortDirection);
    }

    private function transformPesertaData($krsDetail): array
    {
        return [
            'id' => $krsDetail->id,
            'nim' => $krsDetail->krs?->mahasiswa?->nim,
            'nama' => $krsDetail->krs?->mahasiswa?->nama,
            'angkatan' => $krsDetail->krs?->mahasiswa?->angkatan,
            'prodi' => $krsDetail->krs?->mahasiswa?->programStudi?->nama_prodi,
        ];
    }

    private function transformKelasKuliahShowData(KelasKuliah $kelasKuliah, int $totalPeserta): array
    {
        return [
            'id' => $kelasKuliah->id,
            'id_kelas_kuliah' => $kelasKuliah->id_kelas_kuliah,
            'nama_kelas_kuliah' => $kelasKuliah->nama_kelas_kuliah,
            'kode_mata_kuliah' => $kelasKuliah->kode_mata_kuliah,
            'nama_mata_kuliah' => $kelasKuliah->nama_mata_kuliah,
            'sks' => $kelasKuliah->sks,
            'kapasitas' => $kelasKuliah->kapasitas,
            'prodi' => $kelasKuliah->programStudi?->nama_prodi,
            'semester' => $kelasKuliah->tahunAkademik?->nama_semester ?? $kelasKuliah->id_semester,
            'tanggal_uts' => $kelasKuliah->tanggal_uts,
            'tanggal_uas' => $kelasKuliah->tanggal_uas,
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
        ];
    }


    public function destroy(KelasKuliah $kelasKuliah)
    {
        try {
            $kelasKuliah->delete();
            return redirect()->back()->with('success', 'Data kelas kuliah berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->back()->with('error', 'Data tidak bisa dihapus karena sedang berelasi dengan data lain.');
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    public function updateJadwal(Request $request, KelasKuliah $kelasKuliah)
    {
        $validated = $request->validate([
            'tanggal_uts' => 'nullable|date',
            'tanggal_uas' => 'nullable|date',
        ]);

        $kelasKuliah->update([
            'tanggal_uts' => $validated['tanggal_uts'],
            'tanggal_uas' => $validated['tanggal_uas'],
        ]);

        return redirect()->back()->with('success', 'Jadwal Ujian berhasil diperbarui');
    }
}
