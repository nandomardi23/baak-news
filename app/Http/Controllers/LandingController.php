<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuratPengajuanRequest;
use App\Models\DokumenTemplate;
use App\Models\Dosen;
use App\Models\KalenderAkademik;
use App\Models\Mahasiswa;
use App\Models\Pejabat;
use App\Models\ProgramStudi;
use App\Models\SuratPengajuan;
use App\Models\TahunAkademik;
use App\Traits\GeneratesPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    use GeneratesPdf;
    public function index(Request $request): Response
    {
        $prodi = ProgramStudi::active()->orderBy('nama_prodi')->get(['id', 'nama_prodi']);
        
        $templatesQuery = DokumenTemplate::query();

        if ($request->filled('search_template')) {
            $templatesQuery->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search_template . '%')
                  ->orWhere('deskripsi', 'like', '%' . $request->search_template . '%');
            });
        }

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $templatesQuery->where('kategori', $request->kategori);
        }

        $templates = $templatesQuery->orderBy('created_at', 'desc')->paginate(5)->withQueryString();

        return Inertia::render('Landing/Home', [
            'prodi' => $prodi,
            'templates' => $templates,
            'filters' => $request->only(['search_template', 'kategori']),
        ]);
    }

    public function profile(): Response
    {
        $pejabat = Pejabat::active()
            ->where('jabatan', 'not like', '%Kaprodi%')
            ->where('jabatan', 'not like', '%Program Studi%')
            ->where('jabatan', 'not like', '%Ketua%') // Exclude Ketua Stikes
            ->orderBy('id') // Simple ordering for now
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'nama' => $p->nama_lengkap,
                'jabatan' => $p->jabatan,
                'nip' => $p->nip,
            ]);

        return Inertia::render('Landing/Profile', [
            'pejabat' => $pejabat,
        ]);
    }

    public function search(Request $request): Response
    {
        $request->validate([
            'search' => 'required|string|min:3',
        ]);

        $mahasiswa = Mahasiswa::with('programStudi')
            ->search($request->search)
            ->active()
            ->take(20)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'nim' => $item->nim,
                'nama' => $item->nama,
                'prodi' => $item->programStudi?->nama_prodi,
                'angkatan' => $item->angkatan,
            ]);

        return Inertia::render('Landing/SearchResult', [
            'mahasiswa' => $mahasiswa,
            'search' => $request->search,
        ]);
    }

    public function form(Mahasiswa $mahasiswa): Response
    {
        $mahasiswa->load('programStudi');

        // Check for existing pending request for any type
        $existingPending = SuratPengajuan::where('mahasiswa_id', $mahasiswa->id)
            ->pending()
            ->exists();

        // Get available semesters for KRS/KHS
        $semesters = TahunAkademik::orderBy('id_semester', 'desc')
            ->take(6)
            ->get()
            ->map(fn($ta) => [
                'id' => $ta->id,
                'nama' => $ta->nama_semester,
            ]);

        return Inertia::render('Landing/FormPengajuan', [
            'mahasiswa' => [
                'id' => $mahasiswa->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'tempat_lahir' => $mahasiswa->tempat_lahir,
                'tanggal_lahir' => $mahasiswa->tanggal_lahir?->format('Y-m-d'),
                'alamat' => $mahasiswa->alamat,
                'rt' => $mahasiswa->rt,
                'rw' => $mahasiswa->rw,
                'kelurahan' => $mahasiswa->kelurahan,
                'kecamatan' => $mahasiswa->kecamatan,
                'kota_kabupaten' => $mahasiswa->kota_kabupaten,
                'provinsi' => $mahasiswa->provinsi,
                'no_hp' => $mahasiswa->no_hp,
                'prodi' => $mahasiswa->programStudi?->nama_prodi,
                'jenis_program' => $mahasiswa->programStudi?->jenis_program ?? 'reguler',
                'angkatan' => $mahasiswa->angkatan,
                // Parent data
                'nama_ayah' => $mahasiswa->nama_ayah,
                'pekerjaan_ayah' => $mahasiswa->pekerjaan_ayah,
                'nama_ibu' => $mahasiswa->nama_ibu,
                'pekerjaan_ibu' => $mahasiswa->pekerjaan_ibu,
                'alamat_ortu' => $mahasiswa->alamat_ortu,
                'rt_ortu' => $mahasiswa->rt_ortu,
                'rw_ortu' => $mahasiswa->rw_ortu,
                'kelurahan_ortu' => $mahasiswa->kelurahan_ortu,
                'kecamatan_ortu' => $mahasiswa->kecamatan_ortu,
                'kota_kabupaten_ortu' => $mahasiswa->kota_kabupaten_ortu,
                'provinsi_ortu' => $mahasiswa->provinsi_ortu,
            ],
            'existingPending' => $existingPending,
            'semesters' => $semesters,
        ]);
    }

    public function submit(StoreSuratPengajuanRequest $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $validated = $request->validated();

        // Update mahasiswa data if provided
        $mahasiswaData = collect($validated)
            ->only([
                'nama', 'tempat_lahir', 'tanggal_lahir', 
                'alamat', 'rt', 'rw', 'kelurahan', 'kecamatan', 'kota_kabupaten', 'provinsi', 'no_hp', 
                'nama_ayah', 'pekerjaan_ayah', 'nama_ibu', 'pekerjaan_ibu', 
                'alamat_ortu', 'rt_ortu', 'rw_ortu', 'kelurahan_ortu', 'kecamatan_ortu', 'kota_kabupaten_ortu', 'provinsi_ortu'
            ])
            ->filter()
            ->toArray();
        
        if (!empty($mahasiswaData)) {
            // Enforce Title Case for name if present
            if (isset($mahasiswaData['nama'])) {
                $mahasiswaData['nama'] = \Illuminate\Support\Str::title(strtolower($mahasiswaData['nama']));
            }
            
            $mahasiswa->update($mahasiswaData);
        }

        // Build data_tambahan based on jenis_surat
        $dataTambahan = [];
        switch ($validated['jenis_surat']) {
            case 'aktif_kuliah':
                $dataTambahan['keperluan'] = $validated['keperluan'];
                break;
            case 'krs':
            case 'khs':
                $dataTambahan['tahun_akademik_id'] = $validated['tahun_akademik_id'];
                break;
            case 'transkrip':
                $dataTambahan['jenis'] = $validated['jenis_transkrip'] ?? 'reguler';
                break;
        }

        // Create surat pengajuan
        SuratPengajuan::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => $validated['jenis_surat'],
            'keperluan' => $validated['keperluan'] ?? null,
            'data_tambahan' => $dataTambahan,
            'status' => 'pending',
        ]);

        return redirect()->route('landing.status', ['mahasiswa' => $mahasiswa->id])
            ->with('success', 'Pengajuan surat berhasil dikirim');
    }

    public function status(Mahasiswa $mahasiswa): Response
    {
        $pengajuan = SuratPengajuan::where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'jenis_surat' => $item->jenis_surat_label,
                'keperluan' => $item->keperluan,
                'status' => $item->status,
                'status_label' => $item->status_label,
                'status_badge' => $item->status_badge,
                'catatan' => $item->catatan,
                'created_at' => $item->created_at->format('d M Y H:i'),
                'processed_at' => $item->processed_at?->format('d M Y H:i'),
            ]);

        return Inertia::render('Landing/Status', [
            'mahasiswa' => [
                'id' => $mahasiswa->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
            ],
            'pengajuan' => $pengajuan,
        ]);
    }

    /**
     * Unified page for all student documents (KRS, KHS, Transkrip, Surat)
     */
    public function dokumen(Mahasiswa $mahasiswa): Response
    {
        $mahasiswa->load(['programStudi', 'krs.tahunAkademik', 'nilai.tahunAkademik', 'nilai.mataKuliah', 'dosenWali']);

        // Calculate actual SKS and IPK from grades
        $totalSks = 0;
        $totalBobot = 0;
        foreach ($mahasiswa->nilai as $nilai) {
            if ($mk = $nilai->mataKuliah) {
                if ($nilai->nilai_huruf) {
                    $bobot = $mk->sks_mata_kuliah * ($nilai->nilai_indeks ?? 0);
                    $totalSks += $mk->sks_mata_kuliah;
                    $totalBobot += $bobot;
                }
            }
        }
        
        $calculatedIpk = $totalSks > 0 ? $totalBobot / $totalSks : 0;
        $ipk = (float)($mahasiswa->ipk ?? 0) > 0 ? (float)$mahasiswa->ipk : $calculatedIpk;
        $sks = ($mahasiswa->sks_tempuh ?? 0) > 0 ? $mahasiswa->sks_tempuh : $totalSks;

        // Get all semesters where student has KRS or Nilai
        $krsSemesters = $mahasiswa->krs->pluck('tahunAkademik')->filter()->unique('id');
        $nilaiSemesters = $mahasiswa->nilai->pluck('tahunAkademik')->filter()->unique('id');
        
        $allSemesters = $krsSemesters->merge($nilaiSemesters)
            ->unique('id')
            ->sortByDesc('id_semester')
            ->values()
            ->map(fn($ta) => [
                'id' => $ta->id,
                'nama' => $ta->nama_semester,
                'has_krs' => $krsSemesters->contains('id', $ta->id),
                'has_nilai' => $nilaiSemesters->contains('id', $ta->id),
            ]);

        // Check for existing pending request
        $existingPending = SuratPengajuan::where('mahasiswa_id', $mahasiswa->id)
            ->pending()
            ->exists();

        // Get recent pengajuan
        $recentPengajuan = SuratPengajuan::where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'jenis_surat' => $item->jenis_surat_label,
                'status' => $item->status,
                'status_label' => $item->status_label,
                'status_badge' => $item->status_badge,
                'created_at' => $item->created_at->format('d M Y'),
            ]);

        return Inertia::render('Landing/Dokumen', [
            'mahasiswa' => [
                'id' => $mahasiswa->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'prodi' => $mahasiswa->programStudi?->nama_prodi,
                'angkatan' => $mahasiswa->angkatan,
                'ipk' => number_format($ipk, 2),
                'sks_tempuh' => $sks,
                'dosen_wali_id' => $mahasiswa->dosen_wali_id ? (string) $mahasiswa->dosen_wali_id : null,
                'dosen_wali_nama' => $mahasiswa->dosenWali?->nama_lengkap ?? null,
            ],
            'semesters' => $allSemesters,
            'existingPending' => $existingPending,
            'recentPengajuan' => $recentPengajuan,
            'dosens' => Dosen::active()->orderBy('nama')->get()->map(fn($d) => [
                'id' => (string) $d->id,
                'nama' => $d->nama_lengkap
            ]),
        ]);
    }

    /**
     * Update Dosen Wali from student self-service page
     */
    public function updateDosenWali(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $request->validate([
            'dosen_wali_id' => 'required|exists:dosen,id',
        ]);

        $mahasiswa->update([
            'dosen_wali_id' => $request->dosen_wali_id,
        ]);

        return redirect()->back()->with('success', 'Dosen Pembimbing Akademik berhasil disimpan.');
    }

    /**
     * Print KRS directly (public access)
     */
    public function printKrs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->pdfInlineResponse('krs', $mahasiswa, $tahunAkademik);
    }

    /**
     * Print KHS directly (public access)
     */
    public function printKhs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->pdfInlineResponse('khs', $mahasiswa, $tahunAkademik);
    }

    /**
     * Print Transkrip directly (public access)
     */
    public function printTranskrip(Mahasiswa $mahasiswa, string $jenis = 'reguler')
    {
        return $this->pdfInlineResponse('transkrip', $mahasiswa, null, $jenis);
    }

    /**
     * Print Kartu Ujian directly (public access - students only print their own)
     */
    public function printKartuUjian(Request $request, Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->pdfInlineResponse('kartu_ujian', $mahasiswa, $tahunAkademik, $request->get('jenis', 'uts'));
    }

    /**
     * Public Kalender Akademik page
     */
    public function kalender(): Response
    {
        $activeTahun = TahunAkademik::where('is_active', true)->first();

        $kalender = KalenderAkademik::with('tahunAkademik')
            ->when($activeTahun, fn($q) => $q->where('tahun_akademik_id', $activeTahun->id))
            ->orderBy('tanggal_mulai')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'judul' => $item->judul,
                'deskripsi' => $item->deskripsi,
                'tanggal_mulai' => $item->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai' => $item->tanggal_selesai?->format('Y-m-d'),
                'tanggal_format' => $item->tanggal_format,
                'jenis' => $item->jenis,
                'jenis_label' => $item->jenis_label,
                'warna' => $item->warna ?: $item->default_color,
                'duration_days' => $item->duration_days,
            ]);

        $upcomingEvents = KalenderAkademik::upcoming()
            ->when($activeTahun, fn($q) => $q->where('tahun_akademik_id', $activeTahun->id))
            ->take(5)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'judul' => $item->judul,
                'tanggal_format' => $item->tanggal_format,
                'jenis_label' => $item->jenis_label,
                'warna' => $item->warna ?: $item->default_color,
            ]);

        return Inertia::render('Landing/Kalender', [
            'kalender' => $kalender,
            'tahunAkademik' => $activeTahun ? [
                'id' => $activeTahun->id,
                'nama' => $activeTahun->nama_semester,
            ] : null,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    /**
     * Show identity verification form for document access
     */
    public function showVerify(Mahasiswa $mahasiswa): Response
    {
        return Inertia::render('Landing/VerifyIdentity', [
            'mahasiswa_id' => $mahasiswa->id,
        ]);
    }

    /**
     * Process identity verification
     */
    public function processVerify(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $request->validate([
            'nim' => 'required|string',
            'tanggal_lahir' => 'required|date',
        ]);

        if (
            $mahasiswa->nim === $request->nim &&
            $mahasiswa->tanggal_lahir &&
            $mahasiswa->tanggal_lahir->format('Y-m-d') === $request->tanggal_lahir
        ) {
            $request->session()->put('verified_mahasiswa_id', $mahasiswa->id);
            $request->session()->put('verified_mahasiswa_at', now());
            return redirect()->route('landing.dokumen', $mahasiswa->id);
        }

        return back()->withErrors([
            'identity' => 'NIM atau tanggal lahir tidak sesuai.',
        ]);
    }

    /**
     * Download Document Template (Public)
     */
    public function downloadDokumenTemplate(DokumenTemplate $dokumen_template)
    {
        if ($dokumen_template->file_path && Storage::disk('public')->exists($dokumen_template->file_path)) {
            return response()->download(storage_path('app/public/' . $dokumen_template->file_path), $dokumen_template->nama . '.' . $dokumen_template->file_type);
        }
        abort(404, 'File tidak ditemukan.');
    }

    // PDF generation is handled by the GeneratesPdf trait
}

