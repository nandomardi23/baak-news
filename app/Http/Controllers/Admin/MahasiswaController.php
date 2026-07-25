<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MahasiswaExport;
use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Services\Sync\AcademicSyncService;
use App\Services\Sync\ReferenceSyncService;
use App\Services\Sync\StudentSyncService;
use App\Services\PdfGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MahasiswaController extends Controller
{
    use \App\Traits\HasDataTable;

    public function index(Request $request): Response
    {
        $query = Mahasiswa::query()
            ->select('mahasiswa.*')
            ->leftJoin('program_studi', 'mahasiswa.program_studi_id', '=', 'program_studi.id')
            ->with('programStudi')
            ->withCount(['krs', 'nilai', 'suratPengajuan']);

        if ($request->filled('prodi')) {
            $query->where('mahasiswa.program_studi_id', $request->prodi);
        }

        if ($request->filled('status')) {
            $query->where('mahasiswa.status_mahasiswa', $request->status);
        }

        if ($request->filled('angkatan')) {
            $query->where('mahasiswa.angkatan', $request->angkatan);
        }

        // Fix sorting for relationship
        if ($request->sort_field === 'program_studi') {
            $request->merge(['sort_field' => 'program_studi.nama_prodi']);
        }

        // Apply default sort if no sorting is specified
        if (!$request->filled('sort_field')) {
            $query->orderBy('mahasiswa.created_at', 'desc');
        }

        // Apply standardized Search and Sort
        $mahasiswa = $this->applyDataTable($query, $request, ['mahasiswa.nim', 'mahasiswa.nama', 'mahasiswa.angkatan', 'programStudi.nama_prodi'], 20);

        // Transform results
        $mahasiswa->through(fn($item) => [
            'id' => $item->id,
            'nim' => $item->nim,
            'nama' => $item->nama,
            'program_studi' => $item->programStudi?->nama_prodi,
            'angkatan' => $item->angkatan,
            'status' => $item->status_text,
            'ipk' => $item->ipk !== null ? (float) $item->ipk : null,
            'krs_count' => $item->krs_count ?? 0,
            'nilai_count' => $item->nilai_count ?? 0,
            'surat_pengajuan_count' => $item->surat_pengajuan_count ?? 0,
        ]);

        $prodi = ProgramStudi::active()->orderBy('nama_prodi')->get(['id', 'nama_prodi']);
        $angkatanList = Mahasiswa::whereNotNull('angkatan')
            ->where('angkatan', '!=', '')
            ->distinct()
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan')
            ->values()
            ->toArray();

        return Inertia::render('Admin/Mahasiswa/Index', [
            'mahasiswa' => $mahasiswa,
            'prodi' => $prodi,
            'angkatanList' => $angkatanList,
            'filters' => $request->only(['search', 'prodi', 'status', 'angkatan', 'sort_field', 'sort_direction']),
        ]);
    }

    public function show(Mahasiswa $mahasiswa, AcademicSyncService $syncService): Response
    {
        // Note: Auto-sync KRS telah dimatikan dari sini karena menyebabkan halaman diload sangat lambat (memanggil API PDDikti secara sinkron).
        // Gunakan tombol 'Sync KRS' secara manual di UI jika data krs belum ada.

        $mahasiswa->load(['programStudi', 'dosenWali', 'nilai.mataKuliah', 'nilai.tahunAkademik', 'krs.details.mataKuliah', 'krs.details.dosen', 'krs.tahunAkademik', 'krs.details.kelasKuliah.dosenPengajar']);

        // Filter: Semesters with Nilai OR Krs
        $semesterIds = $mahasiswa->nilai->pluck('tahun_akademik_id')
            ->merge($mahasiswa->krs->pluck('tahun_akademik_id'))
            ->unique();

        $tahunAkademik = TahunAkademik::whereIn('id', $semesterIds)
            ->orderBy('id_semester', 'desc')
            ->get();

        return Inertia::render('Admin/Mahasiswa/Show', [
            'mahasiswa' => [
                'id' => $mahasiswa->id,
                'id_mahasiswa' => $mahasiswa->id_mahasiswa,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'tempat_lahir' => $mahasiswa->tempat_lahir,
                'tanggal_lahir' => $mahasiswa->tanggal_lahir instanceof \Illuminate\Support\Carbon ? $mahasiswa->tanggal_lahir->translatedFormat('d F Y') : null,
                'ttl' => $mahasiswa->ttl,
                'jenis_kelamin' => $mahasiswa->jenis_kelamin,
                'alamat' => $mahasiswa->alamat,
                'alamat_lengkap' => $mahasiswa->alamat_lengkap,
                'no_hp' => $mahasiswa->no_hp,
                'email' => $mahasiswa->email,
                'nama_ayah' => $mahasiswa->nama_ayah,
                'nama_ibu' => $mahasiswa->nama_ibu,
                'program_studi' => $mahasiswa->programStudi?->nama_prodi,
                'jenjang' => $mahasiswa->programStudi?->jenjang,
                'angkatan' => $mahasiswa->angkatan,
                'status' => $mahasiswa->status_mahasiswa,
                'ipk' => $mahasiswa->ipk !== null ? (float) $mahasiswa->ipk : null,
                'sks_tempuh' => $mahasiswa->sks_tempuh,
                'dosen_wali' => $mahasiswa->dosenWali?->nama_lengkap ?? $mahasiswa->dosenWali?->nama,
            ],
            'tahunAkademik' => $tahunAkademik->map(fn($ta) => [
                'id' => $ta->id,
                'nama_semester' => $ta->nama_semester,
                'is_active' => $ta->is_active,
            ]),
            'krs' => $mahasiswa->krs->sortBy('id_semester')->values()->map(fn($krs) => [
                'id' => $krs->id,
                'tahun_akademik_id' => $krs->tahun_akademik_id,
                'semester' => $krs->tahunAkademik?->nama_semester,
                'total_sks' => $krs->total_sks,
                'details' => $krs->details->map(fn($d) => [
                    'kode' => $d->mataKuliah?->kode_matkul,
                    'nama' => $d->mataKuliah?->nama_matkul,
                    'sks' => $d->mataKuliah?->sks_mata_kuliah,
                    'kelas' => $d->nama_kelas,
                    // Check if class has team teaching, otherwise legacy name
                    'dosen_pengajar' => $d->kelasKuliah?->dosenPengajar->map(fn($lecture) => $lecture->nama_lengkap),
                    'nama_dosen' => $d->nama_dosen ?? $d->dosen?->nama,
                ]),
            ]),
            'nilai' => $mahasiswa->nilai
                ->groupBy('tahun_akademik_id')
                ->map(fn($group, $taId) => [
                    'tahun_akademik_id' => $taId,
                    'semester' => $group->first()?->tahunAkademik?->nama_semester,
                    'list' => $group->map(fn($n) => [
                        'kode' => $n->mataKuliah?->kode_matkul,
                        'nama' => $n->mataKuliah?->nama_matkul,
                        'sks' => $n->mataKuliah?->sks_mata_kuliah,
                        'nilai_huruf' => $n->nilai_huruf,
                        'nilai_angka' => $n->nilai_angka,
                        'nilai_indeks' => $n->nilai_indeks !== null ? (float) $n->nilai_indeks : null,
                    ]),
                ])->values(),
            'dosen' => \App\Models\Dosen::select('id', 'nama', 'gelar_depan', 'gelar_belakang')->orderBy('nama')->get()->map(fn($d) => [
                'id' => $d->id,
                'nama' => $d->nama_lengkap
            ]),
        ]);
    }

    public function create(): Response
    {
        $prodi = ProgramStudi::active()->orderBy('nama_prodi')->get(['id', 'nama_prodi']);
        $dosen = \App\Models\Dosen::select('id', 'nama', 'gelar_depan', 'gelar_belakang')->orderBy('nama')->get()->map(fn($d) => [
            'id' => $d->id,
            'nama' => $d->nama_lengkap
        ]);
        
        return Inertia::render('Admin/Mahasiswa/Create', [
            'prodi' => $prodi,
            'dosen' => $dosen,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:50|unique:mahasiswa,nim',
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:50',
            'program_studi_id' => 'required|exists:program_studi,id',
            'angkatan' => 'required|string|max:4',
            'status_mahasiswa' => 'required|string|max:1',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'id_agama' => 'nullable|numeric',
            'email' => 'nullable|email|max:100',
            'no_hp' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'dosen_wali_id' => 'nullable|exists:dosen,id',
        ]);

        $validated['id_mahasiswa'] = \Illuminate\Support\Str::uuid()->toString();
        $validated['nama_mahasiswa'] = $validated['nama'];
        
        Mahasiswa::create($validated);

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Berhasil menambahkan data mahasiswa.');
    }

    public function edit(Mahasiswa $mahasiswa): Response
    {
        $prodi = ProgramStudi::active()->orderBy('nama_prodi')->get(['id', 'nama_prodi']);
        $dosen = \App\Models\Dosen::select('id', 'nama', 'gelar_depan', 'gelar_belakang')->orderBy('nama')->get()->map(fn($d) => [
            'id' => $d->id,
            'nama' => $d->nama_lengkap
        ]);
        
        // Format date for input type="date"
        $mahasiswaArray = $mahasiswa->toArray();
        $mahasiswaArray['tanggal_lahir'] = $mahasiswa->tanggal_lahir ? $mahasiswa->tanggal_lahir->format('Y-m-d') : null;

        return Inertia::render('Admin/Mahasiswa/Edit', [
            'mahasiswa' => $mahasiswaArray,
            'prodi' => $prodi,
            'dosen' => $dosen,
        ]);
    }

    public function update(Request $request, Mahasiswa $mahasiswa): \Illuminate\Http\RedirectResponse
    {
        // Support partial updates (like Dosen Wali from Show.vue) 
        // or full updates from Edit.vue
        $validated = $request->validate([
            'nim' => 'sometimes|required|string|max:50|unique:mahasiswa,nim,' . $mahasiswa->id,
            'nama' => 'sometimes|required|string|max:255',
            'nik' => 'nullable|string|max:50',
            'program_studi_id' => 'sometimes|required|exists:program_studi,id',
            'angkatan' => 'sometimes|required|string|max:4',
            'status_mahasiswa' => 'sometimes|required|string|max:1',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'id_agama' => 'nullable|numeric',
            'email' => 'nullable|email|max:100',
            'no_hp' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'dosen_wali_id' => 'nullable|exists:dosen,id',
        ]);

        if (isset($validated['nama'])) {
            $validated['nama_mahasiswa'] = $validated['nama'];
        }

        $mahasiswa->update($validated);

        // If it's a full update from the edit page, we could redirect back to index or show.
        // We generally redirect back(), and let the client handle Inertia redirection if needed.
        return redirect()->back()->with('success', 'Berhasil memperbarui data mahasiswa.');
    }

    public function destroy(Mahasiswa $mahasiswa): \Illuminate\Http\RedirectResponse
    {
        try {
            $mahasiswa->delete();
            return redirect()->route('admin.mahasiswa.index')->with('success', 'Berhasil menghapus data mahasiswa.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->route('admin.mahasiswa.index')->with('error', 'Data tidak bisa dihapus karena sedang berelasi dengan data lain.');
            }
            return redirect()->route('admin.mahasiswa.index')->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

}

