<?php

namespace App\Services\Pdfs;

use App\Models\Mahasiswa;
use App\Models\Pejabat;
use App\Models\Setting;
use App\Models\TahunAkademik;

class KrsService extends BasePdfService
{
    public function generate(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik, ?Pejabat $customSigner = null): string
    {
        $this->AddPage('P', 'A4');
        $this->SetMargins(15, 10, 15);
        $this->SetAutoPageBreak(true, 15);
        
        // Find background template
        $this->useBackgroundTemplate('krs');
        
        // If template was used, we probably need an offset. If not, addKopSurat was called.
        // Let's assume we start content after the header.
        $this->SetY(45);
        
        // Fetch KRS data first to get total SKS
        $krs = $mahasiswa->krs()
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->with(['details.mataKuliah'])
            ->first();
            
        $totalSks = 0;
        if ($krs) {
            foreach ($krs->details as $detail) {
                $totalSks += ($detail->mataKuliah->sks_mata_kuliah ?? 0);
            }
        }

        // Student Info Block (Two columns)
        $this->SetFont('Arial', 'B', 9);
        $w1 = 55; $sep = 5; $w2 = 50;
        
        // Row 1
        $this->Cell($w1, 5, 'NAMA MAHASISWA', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell($w2, 5, strtoupper($mahasiswa->nama), 0, 0);
        
        // Jurusan
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(25, 5, 'JURUSAN', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, strtoupper($mahasiswa->programStudi?->nama_prodi ?? '-'), 0, 1);
        
        // Row 2
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($w1, 5, 'N I M', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell($w2, 5, $mahasiswa->nim, 0, 0);
        
        // Semester (Derived from tahun akademik)
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(25, 5, 'SEMESTER', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        
        $semesterNum = $this->getMahasiswaSemester($mahasiswa, $tahunAkademik);
        
        $this->Cell(0, 5, $semesterNum, 0, 1);
        
        // Row 3
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($w1, 5, 'JUMLAH SKS YANG DIPEROLEH', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell($w2, 5, $totalSks, 0, 0);
        
        // IPK
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(25, 5, 'IPK', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, number_format((float)($mahasiswa->ipk ?? 0), 2), 0, 1);
        
        $this->Ln(5);
        
        // Title Block (Boxed)
        $this->SetFont('Arial', 'B', 11);
        $titleWidth = 160;
        $this->SetX((210 - $titleWidth) / 2);
        $this->Cell($titleWidth, 7, 'KARTU RENCANA STUDI', 1, 1, 'C');
        $this->SetX((210 - $titleWidth) / 2);
        $this->SetFont('Arial', '', 10);
        $this->Cell($titleWidth, 6, strtoupper($tahunAkademik->nama_semester), 'LRB', 1, 'C');
        
        $this->Ln(5);
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 5, 'Mata Kuliah yang ditempuh, antara lain :', 0, 1);
        
        // Table Header
        $this->SetFont('Arial', 'B', 8);
        $cols = [
            'no' => 10,
            'kode' => 20,
            'mk' => 60,
            'sks' => 10,
            'kelas' => 15,
            'dosen' => 65
        ];
        
        $this->Cell($cols['no'], 6, 'NO.', 1, 0, 'C');
        $this->Cell($cols['kode'], 6, 'KODE MK', 1, 0, 'C');
        $this->Cell($cols['mk'], 6, 'MATA KULIAH', 1, 0, 'C');
        $this->Cell($cols['sks'], 6, 'SKS', 1, 0, 'C');
        $this->Cell($cols['kelas'], 6, 'KELAS', 1, 0, 'C');
        $this->Cell($cols['dosen'], 6, 'DOSEN PEMBINA', 1, 1, 'C');
        
        // Table Rows
        $this->SetFont('Arial', '', 8);
        $no = 1;
        
        if ($krs && $krs->details->count() > 0) {
            foreach ($krs->details as $detail) {
                $mk = $detail->mataKuliah;
                $sks = $mk->sks_mata_kuliah ?? 0;
                
                // Placeholder for Dosen
                $dosen = '-'; 
                
                $row = [
                    ['text' => $no++ . '.', 'width' => $cols['no'], 'align' => 'C'],
                    ['text' => $mk->kode_matkul ?? '-', 'width' => $cols['kode'], 'align' => 'C'],
                    ['text' => $mk->nama_matkul ?? '-', 'width' => $cols['mk'], 'align' => 'L'],
                    ['text' => (string)$sks, 'width' => $cols['sks'], 'align' => 'C'],
                    ['text' => $detail->nama_kelas ?? '-', 'width' => $cols['kelas'], 'align' => 'C'],
                    ['text' => $dosen, 'width' => $cols['dosen'], 'align' => 'L'],
                ];
                
                // Draw row with multi-line support
                $h = $this->addRow($row, 6);
                if ($h > 0) {
                    $this->AddPage();
                    $this->useBackgroundTemplate('krs');
                    $this->SetY(45);
                    $this->addRow($row, 6);
                }
            }
        } else {
            $this->Cell(array_sum($cols), 10, 'Belum ada data KRS untuk semester ini', 1, 1, 'C');
        }
        
        $this->Ln(10);
        
        // Signatures Section
        if ($this->GetY() + 40 > $this->PageBreakTrigger) {
            $this->AddPage();
            $this->useBackgroundTemplate('krs');
            $this->SetY(45);
        }

        $ySign = $this->GetY();
        $this->SetFont('Arial', '', 9);
        
        // Left Side: Mahasiswa
        $this->SetXY(30, $ySign + 5);
        $this->Cell(50, 5, 'MAHASISWA,', 0, 1, 'C');
        $this->Ln(20);
        $this->SetFont('Arial', 'BU', 9);
        $this->SetX(30);
        $this->Cell(50, 5, strtoupper($mahasiswa->nama), 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->SetX(30);
        $this->Cell(50, 5, $mahasiswa->nim, 0, 0, 'C');
        
        // Right Side: Pembimbing Akademik
        $kota = Setting::getValue('kota_terbit', 'Tanjungpinang');
        $this->SetXY(130, $ySign);
        $this->Cell(50, 5, $kota . ', ' . $this->formatTanggal(date('Y-m-d')), 0, 1, 'C');
        $this->SetX(130);
        $this->Cell(50, 5, 'PEMBIMBING AKADEMIK,', 0, 1, 'C');
        $this->Ln(20);
        
        // Attempt to find PA if possible, otherwise placeholder
        $signerId = Setting::getValue('signer_krs');
        $signer = $customSigner;
        
        if (!$signer && $signerId) {
             $signer = Pejabat::find($signerId);
        }

        // Only display if signer is found, otherwise dots
        if ($signer) {
            $this->SetFont('Arial', 'BU', 9);
            $this->SetX(130);
            $this->Cell(50, 5, strtoupper($signer->nama_lengkap), 0, 1, 'C');
            
            $this->SetFont('Arial', '', 9);
            $this->SetX(130);
            $this->Cell(50, 5, 'NIDN/NIP: ' . ($signer->nidn ?? $signer->nip ?? '-'), 0, 1, 'C');
        } else {
            $this->SetFont('Arial', 'B', 9);
            $this->SetX(130);
            $this->Cell(50, 5, '............................................', 0, 1, 'C');
        }
        
        // Final Output
        $filename = 'krs_' . $mahasiswa->nim . '_' . $tahunAkademik->id_semester . '.pdf';
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        $this->Output('F', $path);
        
        return $filename;
    }
}
