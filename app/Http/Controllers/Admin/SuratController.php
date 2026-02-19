<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Mahasiswa;
use App\Models\SuratPengajuan;
use App\Models\TahunAkademik;
use App\Services\PdfGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SuratController extends Controller
{
    use \App\Traits\HasDataTable;

    public function index(Request $request): Response
    {
        $query = SuratPengajuan::with(['mahasiswa.programStudi', 'pejabat', 'processedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_surat', $request->jenis);
        }

        // Apply standardized Search and Sort
        // Searching on related models: mahasiswa.nama, mahasiswa.nim
        $pengajuan = $this->applyDataTable($query, $request, ['mahasiswa.nama', 'mahasiswa.nim', 'nomor_surat'], 20);

        $pengajuan->through(fn($item) => [
            'id' => $item->id,
            'nomor_surat' => $item->nomor_surat,
            'mahasiswa' => [
                'nim' => $item->mahasiswa->nim,
                'nama' => $item->mahasiswa->nama,
                'prodi' => $item->mahasiswa->programStudi?->nama_prodi,
            ],
            'pejabat' => $item->pejabat ? [
                'id' => $item->pejabat->id,
                'nama' => $item->pejabat->nama_lengkap,
                'jabatan' => $item->pejabat->jabatan,
            ] : null,
            'jenis_surat' => $item->jenis_surat,
            'jenis_surat_label' => $item->jenis_surat_label,
            'keperluan' => $item->keperluan,
            'status' => $item->status,
            'status_label' => $item->status_label,
            'status_badge' => $item->status_badge,
            'processed_by' => $item->processedBy?->name,
            'processed_at' => $item->processed_at?->format('d M Y H:i'),
            'created_at' => $item->created_at->format('d M Y H:i'),
        ]);

        return Inertia::render('Admin/Surat/Index', [
            'pengajuan' => $pengajuan,
            'filters' => $request->only(['status', 'jenis', 'search', 'sort_field', 'sort_direction']),
        ]);
    }

    public function show(SuratPengajuan $surat): Response
    {
        $surat->load(['mahasiswa.programStudi', 'processedBy']);

        // Get active pejabat for signer selection
        $pejabatList = \App\Models\Pejabat::active()
            ->orderBy('jabatan')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'nama' => $p->nama_lengkap,
                'jabatan' => $p->jabatan,
                'label' => $p->nama_lengkap . ' (' . $p->jabatan . ')',
            ]);

        return Inertia::render('Admin/Surat/Show', [
            'surat' => [
                'id' => $surat->id,
                'mahasiswa' => [
                    'id' => $surat->mahasiswa->id,
                    'nim' => $surat->mahasiswa->nim,
                    'nama' => $surat->mahasiswa->nama,
                    'ttl' => $surat->mahasiswa->ttl,
                    'prodi' => $surat->mahasiswa->programStudi?->nama_prodi,
                    'angkatan' => $surat->mahasiswa->angkatan,
                    'status' => $surat->mahasiswa->status_mahasiswa,
                ],
                'jenis_surat' => $surat->jenis_surat,
                'jenis_surat_label' => $surat->jenis_surat_label,
                'keperluan' => $surat->keperluan,
                'data_tambahan' => $surat->data_tambahan,
                'status' => $surat->status,
                'status_label' => $surat->status_label,
                'catatan' => $surat->catatan,
                'processed_by' => $surat->processedBy?->name,
                'processed_at' => $surat->processed_at?->format('d M Y H:i'),
                'created_at' => $surat->created_at->format('d M Y H:i'),
            ],
            'pejabatList' => $pejabatList,
        ]);
    }

    public function approve(SuratPengajuan $surat): RedirectResponse
    {
        $surat->approve(auth()->id());

        ActivityLog::log('approved', "Menyetujui pengajuan surat {$surat->jenis_surat_label} untuk {$surat->mahasiswa->nama}", $surat);

        return back()->with('success', 'Surat berhasil disetujui');
    }

    public function reject(Request $request, SuratPengajuan $surat): RedirectResponse
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $surat->reject(auth()->id(), $request->catatan);

        ActivityLog::log('rejected', "Menolak pengajuan surat {$surat->jenis_surat_label} untuk {$surat->mahasiswa->nama}", $surat);

        return back()->with('success', 'Surat ditolak');
    }

    public function print(Request $request, SuratPengajuan $surat)
    {
        // Allow printing for approved OR already printed
        if (!in_array($surat->status, ['approved', 'printed'])) {
            abort(403, 'Surat belum disetujui');
        }

        $surat->load(['mahasiswa.programStudi', 'pejabat']);
        $mahasiswa = $surat->mahasiswa;
        $dataTambahan = $surat->data_tambahan ?? [];
        
        // Determine signer: 
        // 1. Explicit request param
        // 2. Already assigned pejabat in surat
        // 3. Default to Ketua
        $signerId = $request->query('signer_id');
        $customSigner = null;

        if ($signerId) {
            $customSigner = \App\Models\Pejabat::find($signerId);
        } elseif ($surat->pejabat) {
            $customSigner = $surat->pejabat;
        } else {
            // Fallback to Ketua
            $customSigner = \App\Models\Pejabat::where('jabatan', 'Ketua')->first();
        }

        // Use PDF service for surat aktif kuliah
        if ($surat->jenis_surat === 'aktif_kuliah') {
            $pdfService = app(PdfGeneratorService::class);
            
            $filename = $pdfService->generateSuratAktifKuliah(
                $mahasiswa, 
                ['nomor_surat' => $surat->nomor_surat],
                $customSigner
            );
            
            // Update signer if not set
            if ($customSigner && !$surat->pejabat_id) {
                $surat->pejabat_id = $customSigner->id;
                $surat->save();
            }

            $surat->markAsPrinted($signerId ? (int) $signerId : ($customSigner?->id));
            
            return response()->file(
                storage_path('app/public/surat/' . $filename),
                ['Content-Type' => 'application/pdf']
            );
        }

        // Fallback to PDF if no Word template
        $pdfService = app(PdfGeneratorService::class);

        // ... (rest of old logic logic kept for valid syntax but unreachable for aktif_kuliah)
        // Hardcoded PDF generation
        $filename = match($surat->jenis_surat) {
            'krs' => $pdfService->generateKrs(
                $mahasiswa,
                TahunAkademik::findOrFail($dataTambahan['tahun_akademik_id'] ?? 0),
                $customSigner
            ),
            'khs' => $pdfService->generateKhs(
                $mahasiswa,
                TahunAkademik::findOrFail($dataTambahan['tahun_akademik_id'] ?? 0),
                $customSigner
            ),
            'transkrip' => $pdfService->generateTranskrip(
                $mahasiswa,
                $dataTambahan['jenis'] ?? 'reguler'
            ),
            default => abort(400, 'Jenis surat tidak didukung'),
        };

        $surat->markAsPrinted($customSigner?->id);

        return response()->file(
            storage_path('app/public/surat/' . $filename),
            ['Content-Type' => 'application/pdf']
        );
    }

    public function destroy(SuratPengajuan $surat): RedirectResponse
    {
        $description = "Menghapus pengajuan surat {$surat->jenis_surat_label} untuk {$surat->mahasiswa->nama}";
        $surat->delete();

        ActivityLog::log('deleted', $description);

        return redirect()->route('admin.surat.index')
            ->with('success', 'Pengajuan surat berhasil dihapus');
    }

    /**
     * Bulk approve multiple surat
     */
    /**
     * Bulk approve multiple surat
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:surat_pengajuan,id',
        ]);

        // Optimization: Fetch all records in one query
        $suratList = SuratPengajuan::whereIn('id', $request->ids)->where('status', 'pending')->get();
        $count = 0;

        foreach ($suratList as $surat) {
            /** @var SuratPengajuan $surat */
            $surat->approve(auth()->id());
            // Log is handled inside approve or we can batch log, but keeping it simple for now
            ActivityLog::log('approved', "Menyetujui pengajuan surat {$surat->jenis_surat_label} untuk {$surat->mahasiswa->nama}", $surat);
            $count++;
        }

        return back()->with('success', "{$count} surat berhasil disetujui");
    }

    /**
     * Bulk reject multiple surat
     */
    public function bulkReject(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:surat_pengajuan,id',
            'catatan' => 'nullable|string|max:500',
        ]);

        // Optimization: Fetch all records in one query
        $suratList = SuratPengajuan::whereIn('id', $request->ids)->where('status', 'pending')->get();
        $count = 0;

        foreach ($suratList as $surat) {
            /** @var SuratPengajuan $surat */
            $surat->reject(auth()->id(), $request->catatan);
            ActivityLog::log('rejected', "Menolak pengajuan surat {$surat->jenis_surat_label} untuk {$surat->mahasiswa->nama}", $surat);
            $count++;
        }

        return back()->with('success', "{$count} surat berhasil ditolak");
    }
}

