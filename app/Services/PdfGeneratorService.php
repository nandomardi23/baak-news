<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Pejabat;
use App\Models\TahunAkademik;
use App\Models\LetterTemplate;
use App\Services\Pdfs\KrsService;
use App\Services\Pdfs\KhsService;
use App\Services\Pdfs\KartuUjianService;
use App\Services\Pdfs\TranskripService;
use App\Services\Pdfs\SuratService;

class PdfGeneratorService
{
    /**
     * Services are injected once and reused - better memory usage
     */
    public function __construct(
        protected KrsService $krsService,
        protected KhsService $khsService,
        protected KartuUjianService $kartuUjianService,
        protected TranskripService $transkripService,
        protected SuratService $suratService,
    ) {}

    /**
     * Generate Surat Aktif Kuliah
     */
    public function generateSuratAktifKuliah(Mahasiswa $mahasiswa, array $dataTambahan = [], ?Pejabat $customSigner = null): string
    {
        return $this->suratService->generateSuratAktifKuliah($mahasiswa, $dataTambahan, $customSigner);
    }

    /**
     * Generate KRS PDF
     */
    public function generateKrs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik, ?Pejabat $customSigner = null): string
    {
        return $this->krsService->generate($mahasiswa, $tahunAkademik, $customSigner);
    }

    /**
     * Generate KHS PDF
     */
    public function generateKhs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik, ?Pejabat $customSigner = null): string
    {
        return $this->khsService->generate($mahasiswa, $tahunAkademik, $customSigner);
    }

    /**
     * Generate Kartu Ujian PDF
     */
    public function generateKartuUjian(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik): string
    {
        return $this->kartuUjianService->generate($mahasiswa, $tahunAkademik);
    }

    /**
     * Generate Transkrip PDF
     */
    public function generateTranskrip(Mahasiswa $mahasiswa, string $jenis = 'reguler'): string
    {
        return $this->transkripService->generate($mahasiswa, $jenis);
    }

    /**
     * Generate PDF from Letter Template
     */
    public function generateFromTemplate(LetterTemplate $template, array $data): string
    {
        return $this->suratService->generateFromTemplate($template, $data);
    }
}
