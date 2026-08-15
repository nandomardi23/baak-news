<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ArsipSurat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArsipSuratController extends Controller
{
    use \App\Traits\HasDataTable;

    /**
     * Display a listing of arsip surat.
     */
    public function index(Request $request): Response
    {
        $query = ArsipSurat::with('creator')->latest('tanggal_surat');

        // Filter by jenis
        if ($request->filled('jenis') && $request->jenis !== 'all') {
            $query->where('jenis', $request->jenis);
        }

        // Filter by date range
        if ($request->filled('dari_tanggal')) {
            $query->where('tanggal_surat', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->where('tanggal_surat', '<=', $request->sampai_tanggal);
        }

        // Apply standardized Search and Sort
        $arsipSurat = $this->applyDataTable(
            $query,
            $request,
            ['nomor_surat', 'perihal', 'asal_surat', 'tujuan_surat'],
            15
        );

        $arsipSurat->through(fn($item) => [
            'id' => $item->id,
            'jenis' => $item->jenis,
            'jenis_label' => $item->jenis_label,
            'jenis_badge' => $item->jenis_badge,
            'nomor_surat' => $item->nomor_surat,
            'tanggal_surat' => $item->tanggal_surat->format('d M Y'),
            'tanggal_diterima' => $item->tanggal_diterima?->format('d M Y'),
            'asal_surat' => $item->asal_surat,
            'tujuan_surat' => $item->tujuan_surat,
            'perihal' => $item->perihal,
            'keterangan' => $item->keterangan,
            'file_url' => $item->file_url,
            'file_extension' => $item->file_extension,
            'is_pdf' => $item->is_pdf,
            'is_image' => $item->is_image,
            'created_by' => $item->creator?->name,
            'created_at' => $item->created_at->format('d M Y H:i'),
        ]);

        return Inertia::render('Admin/ArsipSurat/Index', [
            'arsipSurat' => $arsipSurat,
            'filters' => $request->only(['jenis', 'dari_tanggal', 'sampai_tanggal', 'search', 'sort_field', 'sort_direction']),
        ]);
    }

    /**
     * Store a newly created arsip surat.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis' => 'required|in:masuk,keluar',
            'nomor_surat' => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
            'tanggal_diterima' => 'nullable|date|required_if:jenis,masuk',
            'asal_surat' => 'nullable|string|max:255|required_if:jenis,masuk',
            'tujuan_surat' => 'nullable|string|max:255|required_if:jenis,keluar',
            'perihal' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240', // max 10MB
        ]);

        // Store file
        $filePath = $request->file('file')->store('arsip-surat', 'public');

        $arsip = ArsipSurat::create([
            'jenis' => $validated['jenis'],
            'nomor_surat' => $validated['nomor_surat'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'tanggal_diterima' => $validated['tanggal_diterima'] ?? null,
            'asal_surat' => $validated['asal_surat'] ?? null,
            'tujuan_surat' => $validated['tujuan_surat'] ?? null,
            'perihal' => $validated['perihal'],
            'keterangan' => $validated['keterangan'] ?? null,
            'file_path' => $filePath,
            'created_by' => auth()->id(),
        ]);

        ActivityLog::log('created', "Menambahkan arsip {$arsip->jenis_label}: {$arsip->perihal}", $arsip);

        return back()->with('success', 'Arsip surat berhasil ditambahkan');
    }

    /**
     * Display the specified arsip surat.
     */
    public function show(ArsipSurat $arsipSurat): Response
    {
        $arsipSurat->load('creator');

        return Inertia::render('Admin/ArsipSurat/Show', [
            'arsip' => [
                'id' => $arsipSurat->id,
                'jenis' => $arsipSurat->jenis,
                'jenis_label' => $arsipSurat->jenis_label,
                'jenis_badge' => $arsipSurat->jenis_badge,
                'nomor_surat' => $arsipSurat->nomor_surat,
                'tanggal_surat' => $arsipSurat->tanggal_surat->format('Y-m-d'),
                'tanggal_surat_formatted' => $arsipSurat->tanggal_surat->format('d M Y'),
                'tanggal_diterima' => $arsipSurat->tanggal_diterima?->format('Y-m-d'),
                'tanggal_diterima_formatted' => $arsipSurat->tanggal_diterima?->format('d M Y'),
                'asal_surat' => $arsipSurat->asal_surat,
                'tujuan_surat' => $arsipSurat->tujuan_surat,
                'perihal' => $arsipSurat->perihal,
                'keterangan' => $arsipSurat->keterangan,
                'file_url' => $arsipSurat->file_url,
                'file_extension' => $arsipSurat->file_extension,
                'is_pdf' => $arsipSurat->is_pdf,
                'is_image' => $arsipSurat->is_image,
                'created_by' => $arsipSurat->creator?->name,
                'created_at' => $arsipSurat->created_at->format('d M Y H:i'),
                'updated_at' => $arsipSurat->updated_at->format('d M Y H:i'),
            ],
        ]);
    }

    /**
     * Update the specified arsip surat.
     */
    public function update(Request $request, ArsipSurat $arsipSurat): RedirectResponse
    {
        $validated = $request->validate([
            'jenis' => 'required|in:masuk,keluar',
            'nomor_surat' => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
            'tanggal_diterima' => 'nullable|date|required_if:jenis,masuk',
            'asal_surat' => 'nullable|string|max:255|required_if:jenis,masuk',
            'tujuan_surat' => 'nullable|string|max:255|required_if:jenis,keluar',
            'perihal' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        // If new file uploaded, replace old one
        if ($request->hasFile('file')) {
            // Delete old file
            if ($arsipSurat->file_path && Storage::disk('public')->exists($arsipSurat->file_path)) {
                Storage::disk('public')->delete($arsipSurat->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('arsip-surat', 'public');
        }

        // Remove 'file' key from validated (it's the UploadedFile, not the path)
        unset($validated['file']);

        $arsipSurat->update($validated);

        ActivityLog::log('updated', "Memperbarui arsip {$arsipSurat->jenis_label}: {$arsipSurat->perihal}", $arsipSurat);

        return back()->with('success', 'Arsip surat berhasil diperbarui');
    }

    /**
     * Remove the specified arsip surat.
     */
    public function destroy(ArsipSurat $arsipSurat): RedirectResponse
    {
        try {
            $description = "Menghapus arsip {$arsipSurat->jenis_label}: {$arsipSurat->perihal}";

            // Delete file
            if ($arsipSurat->file_path && Storage::disk('public')->exists($arsipSurat->file_path)) {
                Storage::disk('public')->delete($arsipSurat->file_path);
            }

            $arsipSurat->delete();

            ActivityLog::log('deleted', $description);

            return redirect()->route('admin.arsip-surat.index')
                ->with('success', 'Arsip surat berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->route('admin.arsip-surat.index')
                    ->with('error', 'Data tidak bisa dihapus karena sedang berelasi dengan data lain.');
            }
            return redirect()->route('admin.arsip-surat.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    /**
     * Download the arsip surat file.
     */
    public function download(ArsipSurat $arsipSurat): StreamedResponse
    {
        $filePath = $arsipSurat->file_path;

        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        $extension = $arsipSurat->file_extension;
        $filename = str_replace(['/', '\\', ' '], '_', $arsipSurat->nomor_surat) . '.' . $extension;

        return Storage::disk('public')->download($filePath, $filename);
    }
}
