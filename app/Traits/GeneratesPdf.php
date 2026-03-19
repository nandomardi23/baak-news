<?php

namespace App\Traits;

use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Services\PdfGeneratorService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Shared PDF generation logic for controllers.
 * Eliminates duplication between LandingController and MahasiswaController.
 */
trait GeneratesPdf
{
    /**
     * Generate PDF and return as inline response (for public/landing pages)
     */
    protected function pdfInlineResponse(
        string $type,
        Mahasiswa $mahasiswa,
        ?TahunAkademik $tahunAkademik = null,
        string $jenis = 'reguler'
    ) {
        $filename = $this->generatePdf($type, $mahasiswa, $tahunAkademik, $jenis);
        $path = storage_path('app/public/surat/' . $filename);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate PDF and return as download response (for admin pages)
     * Deletes the file after sending.
     */
    protected function pdfDownloadResponse(
        string $type,
        Mahasiswa $mahasiswa,
        ?TahunAkademik $tahunAkademik = null,
        string $jenis = 'reguler'
    ): BinaryFileResponse|\Illuminate\Http\Response {
        try {
            $filename = $this->generatePdf($type, $mahasiswa, $tahunAkademik, $jenis);
            $path = storage_path('app/public/surat/' . $filename);

            if (!file_exists($path)) {
                return response('PDF file not found', 500);
            }

            return response()->file($path, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error("Error generating {$type} PDF", [
                'mahasiswa' => $mahasiswa->nim,
                'semester' => $tahunAkademik?->id,
                'error' => $e->getMessage(),
            ]);
            return response('Error generating PDF: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Core PDF generation logic
     */
    private function generatePdf(
        string $type,
        Mahasiswa $mahasiswa,
        ?TahunAkademik $tahunAkademik = null,
        string $jenis = 'reguler'
    ): string {
        $pdfService = app(PdfGeneratorService::class);

        return match ($type) {
            'krs' => $pdfService->generateKrs($mahasiswa, $tahunAkademik),
            'khs' => $pdfService->generateKhs($mahasiswa, $tahunAkademik),
            'kartu_ujian' => $pdfService->generateKartuUjian($mahasiswa, $tahunAkademik, $jenis),
            'transkrip' => $pdfService->generateTranskrip($mahasiswa, $jenis),
        };
    }
}
