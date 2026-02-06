<?php

namespace App\Services\Pdfs;

use App\Models\Mahasiswa;
use App\Models\Pejabat;
use App\Models\Setting;
use App\Models\TahunAkademik;
use Illuminate\Support\Collection;

class KartuUjianService extends BasePdfService
{
    /**
     * Generate single kartu ujian for one student (full page)
     */
    public function generate(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik): string
    {
        $this->AddPage('P', 'A4');
        $this->SetMargins(15, 15, 15);
        
        $this->addKopSurat();
        $this->addCard($mahasiswa, $tahunAkademik, true);
        
        $filename = 'kartu_ujian_' . $mahasiswa->nim . '_' . $tahunAkademik->id_semester . '.pdf';
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        $this->Output('F', $path);
        
        return $filename;
    }

    /**
     * Generate batch kartu ujian for multiple students (3 per page)
     */
    public function generateBatch(Collection $mahasiswaList, TahunAkademik $tahunAkademik): string
    {
        $cardsPerPage = 3;
        $cardCount = 0;
        
        foreach ($mahasiswaList as $mahasiswa) {
            // Add new page every 3 cards
            if ($cardCount % $cardsPerPage === 0) {
                $this->AddPage('P', 'A4');
                $this->SetMargins(10, 10, 10);
            }
            
            // Calculate Y position for this card (each card ~90mm height)
            $cardHeight = 90;
            $cardIndex = $cardCount % $cardsPerPage;
            $startY = 10 + ($cardIndex * $cardHeight);
            
            $this->SetY($startY);
            $this->addCompactCard($mahasiswa, $tahunAkademik);
            
            $cardCount++;
        }
        
        $filename = 'kartu_ujian_batch_' . $tahunAkademik->id_semester . '_' . time() . '.pdf';
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        $this->Output('F', $path);
        
        return $filename;
    }

    /**
     * Add a full-size card with signature (single student per page)
     */
    private function addCard(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik, bool $includeSignature = true): void
    {
        // Title
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, 'KARTU PESERTA UJIAN', 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, 'Semester: ' . $tahunAkademik->nama_semester, 0, 1, 'C');
        $this->Ln(5);
        
        // Student Info Block
        $this->SetFont('Arial', '', 10);
        $col1 = 30; $separator = 5; $col2 = 70;
        
        // Row 1
        $this->Cell($col1, 6, 'NIM', 0, 0);
        $this->Cell($separator, 6, ':', 0, 0);
        $this->Cell($col2, 6, $mahasiswa->nim, 0, 0);
        
        $photoY = $this->GetY();
        
        $this->Cell($col1, 6, 'Program Studi', 0, 0);
        $this->Cell($separator, 6, ':', 0, 0);
        $this->Cell(0, 6, $mahasiswa->programStudi?->nama_prodi ?? '-', 0, 1);
        
        // Row 2
        $this->Cell($col1, 6, 'Nama', 0, 0);
        $this->Cell($separator, 6, ':', 0, 0);
        $this->Cell($col2, 6, $mahasiswa->nama, 0, 0);
        $this->Cell($col1, 6, 'Angkatan', 0, 0);
        $this->Cell($separator, 6, ':', 0, 0);
        $angkatan = $mahasiswa->angkatan ?? (strlen($mahasiswa->nim) >= 2 ? '20' . substr($mahasiswa->nim, 0, 2) : '-');
        $this->Cell(0, 6, $angkatan, 0, 1);
        
        // Drawing a small box for Photo placeholder
        $this->Rect(160, $photoY - 2, 25, 30);
        $this->SetXY(160, $photoY + 12);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(25, 5, 'FOTO 3x4', 0, 0, 'C');
        
        $this->SetXY(15, $photoY + 12);
        $this->Ln(15);
        
        // Subjects Table
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(10, 8, 'No', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Kode', 1, 0, 'C', true);
        $this->Cell(95, 8, 'Mata Kuliah', 1, 0, 'C', true);
        $this->Cell(45, 8, 'Paraf Pengawas', 1, 1, 'C', true);
        
        $krs = $mahasiswa->krs()
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->with('details.mataKuliah')
            ->first();
        
        $this->SetFont('Arial', '', 10);
        $no = 1;
        
        if ($krs && $krs->details->count() > 0) {
            foreach ($krs->details as $detail) {
                $mk = $detail->mataKuliah;
                $row = [
                    ['text' => (string)$no++, 'width' => 10, 'align' => 'C'],
                    ['text' => $mk->kode_matkul ?? '-', 'width' => 30, 'align' => 'C'],
                    ['text' => $mk->nama_matkul ?? '-', 'width' => 95, 'align' => 'L'],
                    ['text' => '', 'width' => 45, 'align' => 'C'],
                ];
                $this->addRow($row, 8);
            }
        } else {
            $this->Cell(180, 10, 'Belum ada data KRS / Peserta Ujian', 1, 1, 'C');
        }
        
        // Notes
        $this->Ln(5);
        $this->SetFont('Arial', 'I', 9);
        $this->MultiCell(0, 5, "Catatan:\n1. Kartu ini wajib dibawa saat mengikuti ujian.\n2. Mahasiswa wajib berpakaian rapi dan sopan sesuai ketentuan.\n3. Harap hadir 15 menit sebelum ujian dimulai.", 0, 'L');
        
        if ($includeSignature) {
            // Signature
            $signerId = Setting::getValue('signer_krs');
            $signer = Pejabat::find($signerId) ?? Pejabat::active()->where('jabatan', 'like', '%Kaprodi%')->first();
            $this->addCustomSignature($signer, 'Ketua Program Studi');
        }
    }

    /**
     * Add a compact card for batch printing (3 cards per A4 page)
     */
    private function addCompactCard(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik): void
    {
        $startX = 10;
        $cardWidth = 190;
        
        // Draw border for the card
        $cardStartY = $this->GetY();
        
        // Mini header with logo placeholder
        $this->SetFont('Arial', 'B', 8);
        $this->SetX($startX);
        $this->Cell($cardWidth, 5, 'SEKOLAH TINGGI ILMU KESEHATAN HANG TUAH - KARTU PESERTA UJIAN', 1, 1, 'C');
        
        // Student Info - Compact 2 column layout
        $this->SetFont('Arial', '', 8);
        $col1 = 20; $sep = 3; $col2 = 45; $col3 = 25; $col4 = 45;
        
        $this->SetX($startX);
        $this->Cell($col1, 5, 'NIM', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->Cell($col2, 5, $mahasiswa->nim, 0, 0);
        $this->Cell($col3, 5, 'Prodi', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->Cell(0, 5, substr($mahasiswa->programStudi?->nama_prodi ?? '-', 0, 25), 0, 1);
        
        $this->SetX($startX);
        $this->Cell($col1, 5, 'Nama', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->Cell($col2, 5, substr($mahasiswa->nama, 0, 30), 0, 0);
        $this->Cell($col3, 5, 'Semester', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->Cell(0, 5, $tahunAkademik->nama_semester, 0, 1);
        
        // Compact subjects table
        $this->SetFont('Arial', 'B', 7);
        $this->SetFillColor(230, 230, 230);
        $this->SetX($startX);
        $this->Cell(8, 5, 'No', 1, 0, 'C', true);
        $this->Cell(25, 5, 'Kode', 1, 0, 'C', true);
        $this->Cell(80, 5, 'Mata Kuliah', 1, 0, 'C', true);
        $this->Cell(35, 5, 'Hari/Tanggal', 1, 0, 'C', true);
        $this->Cell(42, 5, 'Paraf Pengawas', 1, 1, 'C', true);
        
        $krs = $mahasiswa->krs()
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->with('details.mataKuliah')
            ->first();
        
        $this->SetFont('Arial', '', 7);
        $no = 1;
        $maxRows = 8; // Limit rows to fit in compact card
        
        if ($krs && $krs->details->count() > 0) {
            foreach ($krs->details->take($maxRows) as $detail) {
                $mk = $detail->mataKuliah;
                $this->SetX($startX);
                $this->Cell(8, 5, $no++, 1, 0, 'C');
                $this->Cell(25, 5, $mk->kode_matkul ?? '-', 1, 0, 'C');
                $this->Cell(80, 5, substr($mk->nama_matkul ?? '-', 0, 45), 1, 0, 'L');
                $this->Cell(35, 5, '', 1, 0, 'C');
                $this->Cell(42, 5, '', 1, 1, 'C');
            }
        } else {
            $this->SetX($startX);
            $this->Cell($cardWidth, 5, 'Belum ada data KRS', 1, 1, 'C');
        }
        
        // Compact notes and signature line
        $this->SetFont('Arial', 'I', 6);
        $this->SetX($startX);
        $this->Cell(100, 4, 'Catatan: Kartu ini wajib dibawa saat ujian', 0, 0, 'L');
        
        $this->SetFont('Arial', '', 7);
        $this->Cell(90, 4, 'Tanda Tangan Mahasiswa: ________________', 0, 1, 'R');
        
        // Draw bottom border line
        $this->Line($startX, $this->GetY(), $startX + $cardWidth, $this->GetY());
        $this->Ln(2);
    }
}
