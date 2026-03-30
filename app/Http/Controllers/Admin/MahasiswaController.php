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
    use \App\Traits\GeneratesPdf;

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
            $query->orderBy('mahasiswa.nim', 'asc');
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
        // Auto-sync KRS jika:
        // 1. Belum ada di database sama sekali
        // 2. ATAU ada tapi datanya belum lengkap (belum ada nama dosen karena dari bulk sync)
        $needsSync = false;
        if ($mahasiswa->id_registrasi_mahasiswa) {
            $hasNoKrs = $mahasiswa->krs()->count() === 0;
            $hasIncompleteDetails = $mahasiswa->krs()->whereHas('details', function ($q) {
                $q->whereNull('nama_dosen');
            })->exists();

            if ($hasNoKrs || $hasIncompleteDetails) {
                $needsSync = true;
            }
        }

        if ($needsSync) {
            try {
                $syncService->syncKrsMahasiswa($mahasiswa->id_registrasi_mahasiswa);
            } catch (\Exception $e) {
                \Log::warning("Auto-sync KRS failed for {$mahasiswa->nim}: " . $e->getMessage());
            }
        }

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
        $mahasiswa->delete();
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Berhasil menghapus data mahasiswa.');
    }

    public function printKrs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik): BinaryFileResponse|\Illuminate\Http\Response
    {
        return $this->pdfDownloadResponse('krs', $mahasiswa, $tahunAkademik);
    }

    public function printKhs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik): BinaryFileResponse|\Illuminate\Http\Response
    {
        return $this->pdfDownloadResponse('khs', $mahasiswa, $tahunAkademik);
    }

    public function printKartuUjian(Request $request, Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik): BinaryFileResponse|\Illuminate\Http\Response
    {
        return $this->pdfDownloadResponse('kartu_ujian', $mahasiswa, $tahunAkademik, $request->get('jenis', 'uts'));
    }

    public function printTranskrip(Mahasiswa $mahasiswa, Request $request): BinaryFileResponse|\Illuminate\Http\Response
    {
        return $this->pdfDownloadResponse('transkrip', $mahasiswa, null, $request->get('jenis', 'reguler'));
    }

    /**
     * Show batch kartu ujian page with student list
     */
    public function batchKartuUjian(Request $request): Response
    {
        $tahunAkademik = TahunAkademik::orderBy('id_semester', 'desc')->get();
        $prodi = ProgramStudi::active()->orderBy('nama_prodi')->get(['id', 'nama_prodi']);

        // Get unique angkatan values
        $angkatanList = Mahasiswa::active()
            ->whereNotNull('angkatan')
            ->where('angkatan', '!=', '')
            ->distinct()
            ->orderByDesc('angkatan')
            ->pluck('angkatan');

        $mahasiswa = collect();
        $selectedTa = null;

        if ($request->filled('tahun_akademik_id')) {
            $selectedTa = TahunAkademik::find($request->tahun_akademik_id);

            $query = Mahasiswa::with(['programStudi'])
                ->whereHas('krs', function ($q) use ($request) {
                    $q->where('tahun_akademik_id', $request->tahun_akademik_id);
                })
                ->active();

            if ($request->filled('angkatan')) {
                $query->where('angkatan', $request->angkatan);
            }

            if ($request->filled('prodi_id')) {
                $query->where('program_studi_id', $request->prodi_id);
            }

            $mahasiswa = $query->orderBy('nama')->get()->map(fn($m) => [
                'id' => $m->id,
                'nim' => $m->nim,
                'nama' => $m->nama,
                'prodi' => $m->programStudi?->nama_prodi,
                'angkatan' => $m->angkatan,
            ]);
        }

        return Inertia::render('Admin/Mahasiswa/BatchKartuUjian', [
            'tahunAkademik' => $tahunAkademik->map(fn($ta) => [
                'id' => $ta->id,
                'nama' => $ta->nama_semester,
            ]),
            'prodi' => $prodi,
            'angkatanList' => $angkatanList,
            'mahasiswa' => $mahasiswa,
            'filters' => $request->only(['tahun_akademik_id', 'angkatan', 'prodi_id']),
            'selectedSemester' => $selectedTa?->nama_semester,
        ]);
    }

    /**
     * Batch print kartu ujian for multiple students (admin only)
     */
    public function printBatchKartuUjian(Request $request): BinaryFileResponse|\Illuminate\Http\Response
    {
        $request->validate([
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'angkatan' => 'nullable|string',
            'prodi_id' => 'nullable|exists:program_studi,id',
        ]);

        $tahunAkademik = TahunAkademik::findOrFail($request->tahun_akademik_id);

        // Build query for students who have KRS in this semester
        $query = Mahasiswa::with([
            'programStudi',
            'krs' => function ($q) use ($tahunAkademik) {
                $q->where('tahun_akademik_id', $tahunAkademik->id);
            }
        ])
            ->whereHas('krs', function ($q) use ($tahunAkademik) {
                $q->where('tahun_akademik_id', $tahunAkademik->id);
            })
            ->active();

        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        if ($request->filled('prodi_id')) {
            $query->where('program_studi_id', $request->prodi_id);
        }

        $mahasiswaList = $query->orderBy('nama')->get();

        if ($mahasiswaList->isEmpty()) {
            return response('Tidak ada mahasiswa yang memenuhi kriteria', 404);
        }

        try {
            $pdfService = new \App\Services\Pdfs\KartuUjianService();
            $filename = $pdfService->generateBatch($mahasiswaList, $tahunAkademik, $request->get('jenis', 'uts'));

            $path = storage_path('app/public/surat/' . $filename);

            return response()->file($path, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error("Error generating batch kartu ujian", [
                'semester' => $tahunAkademik->id,
                'error' => $e->getMessage(),
            ]);
            return response('Error generating PDF: ' . $e->getMessage(), 500);
        }
    }


    // PDF generation handled by GeneratesPdf trait



    public function sync(Request $request, ReferenceSyncService $refSyncService, StudentSyncService $studentSyncService): \Illuminate\Http\RedirectResponse
    {
        $type = $request->get('type', 'mahasiswa');
        $message = '';

        try {
            switch ($type) {
                case 'prodi':
                    $result = $refSyncService->syncProdi();
                    $message = "Berhasil sync {$result['synced']} program studi. " . count($result['errors']) . " errors.";
                    break;

                case 'semester':
                    $result = $refSyncService->syncSemester();
                    $message = "Berhasil sync {$result['synced']} semester. " . count($result['errors']) . " errors.";
                    break;

                case 'matakuliah':
                    $result = (new \App\Services\Sync\CurriculumSyncService(app(\App\Services\NeoFeederService::class)))->syncMataKuliah();
                    $message = "Berhasil sync {$result['synced']} mata kuliah. " . count($result['errors']) . " errors.";
                    break;

                case 'mahasiswa':
                default:
                    // Sync sequence for full update
                    $refSyncService->syncProdi();
                    $refSyncService->syncSemester();
                    $result = $studentSyncService->syncMahasiswa();
                    $message = "Berhasil sync {$result['synced']} mahasiswa. " . count($result['errors']) . " errors.";
                    break;
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal sync: ' . $e->getMessage());
        }
    }

    /**
     * Export mahasiswa to Excel
     */
    public function export(Request $request): BinaryFileResponse
    {
        $prodiId = $request->input('prodi') ? (int) $request->input('prodi') : null;
        $search = $request->input('search');

        $filename = 'mahasiswa_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new MahasiswaExport($prodiId, $search),
            $filename
        );
    }

    public function syncKrs(Mahasiswa $mahasiswa, AcademicSyncService $syncService): \Illuminate\Http\RedirectResponse
    {
        try {
            if (!$mahasiswa->id_registrasi_mahasiswa) {
                throw new \Exception("Mahasiswa belum memiliki ID Registrasi");
            }

            $result = $syncService->syncKrsMahasiswa($mahasiswa->id_registrasi_mahasiswa);

            $msg = "Berhasil sync KRS: {$result['synced']} semester synced, {$result['total']} total dari API.";
            if (($result['skipped'] ?? 0) > 0) {
                $msg .= " {$result['skipped']} semester dilewati (belum ada di data semester lokal).";
            }
            if (!empty($result['errors'])) {
                $msg .= " Errors: " . count($result['errors']);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal sync KRS: ' . $e->getMessage());
        }
    }
}

