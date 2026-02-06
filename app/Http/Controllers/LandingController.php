<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\SuratPengajuan;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function index(): Response
    {
        $prodi = ProgramStudi::active()->orderBy('nama_prodi')->get(['id', 'nama_prodi']);

        return Inertia::render('Landing/Home', [
            'prodi' => $prodi,
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
            ],
            'existingPending' => $existingPending,
            'semesters' => $semesters,
        ]);
    }

    public function submit(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_surat' => 'required|in:aktif_kuliah,krs,khs,transkrip',
            'keperluan' => 'required_if:jenis_surat,aktif_kuliah|nullable|string|max:255',
            'tahun_akademik_id' => 'required_if:jenis_surat,krs,khs|nullable|exists:tahun_akademik,id',
            'jenis_transkrip' => 'required_if:jenis_surat,transkrip|nullable|in:reguler,rpl',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string|max:500',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kota_kabupaten' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            // Parent data
            'nama_ayah' => 'nullable|string|max:100',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'nama_ibu' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'alamat_ortu' => 'nullable|string|max:500',
        ]);

        // Update mahasiswa data if provided
        $mahasiswaData = collect($validated)
            ->only(['tempat_lahir', 'tanggal_lahir', 'alamat', 'rt', 'rw', 'kelurahan', 'kecamatan', 'kota_kabupaten', 'provinsi', 'no_hp', 'nama_ayah', 'pekerjaan_ayah', 'nama_ibu', 'pekerjaan_ibu', 'alamat_ortu'])
            ->filter()
            ->toArray();
        if (!empty($mahasiswaData)) {
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
        $mahasiswa->load(['programStudi', 'krs.tahunAkademik', 'nilai.tahunAkademik']);

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
                'ipk' => number_format((float) ($mahasiswa->ipk ?? 0), 2),
                'sks_tempuh' => $mahasiswa->sks_tempuh ?? 0,
            ],
            'semesters' => $allSemesters,
            'existingPending' => $existingPending,
            'recentPengajuan' => $recentPengajuan,
        ]);
    }

    /**
     * Print KRS directly (public access)
     */
    public function printKrs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->generatePdfResponse('krs', $mahasiswa, $tahunAkademik);
    }

    /**
     * Print KHS directly (public access)
     */
    public function printKhs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->generatePdfResponse('khs', $mahasiswa, $tahunAkademik);
    }

    /**
     * Print Transkrip directly (public access)
     */
    public function printTranskrip(Mahasiswa $mahasiswa, string $jenis = 'reguler')
    {
        return $this->generatePdfResponse('transkrip', $mahasiswa, null, $jenis);
    }

    /**
     * Print Kartu Ujian directly (public access - students only print their own)
     */
    public function printKartuUjian(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->generatePdfResponse('kartu_ujian', $mahasiswa, $tahunAkademik);
    }

    /**
     * Helper to generate PDF response - eliminates code duplication
     */
    private function generatePdfResponse(
        string $type, 
        Mahasiswa $mahasiswa, 
        ?TahunAkademik $tahunAkademik = null, 
        string $jenis = 'reguler'
    ) {
        $pdfService = app(\App\Services\PdfGeneratorService::class);
        
        $filename = match($type) {
            'krs' => $pdfService->generateKrs($mahasiswa, $tahunAkademik),
            'khs' => $pdfService->generateKhs($mahasiswa, $tahunAkademik),
            'kartu_ujian' => $pdfService->generateKartuUjian($mahasiswa, $tahunAkademik),
            'transkrip' => $pdfService->generateTranskrip($mahasiswa, $jenis),
        };
        
        return response()->file(storage_path('app/public/surat/' . $filename), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}

