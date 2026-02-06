<?php

namespace App\Services\Pdfs;

use App\Models\Mahasiswa;
use App\Models\Pejabat;
use App\Models\Setting;
use App\Models\TahunAkademik;

class KhsService extends BasePdfService
{
    public function generate(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik, ?Pejabat $customSigner = null): string
    {
        $this->AddPage('P', 'A4');
        $this->SetMargins(15, 10, 15);
        $this->SetAutoPageBreak(true, 15);
        
        $this->useBackgroundTemplate('khs');
        
        $this->SetY(45);
        
        // Student Info Block (Two columns, matching KRS style)
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
        
        // Semester
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(25, 5, 'SEMESTER', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        
        $semesterNum = $this->getMahasiswaSemester($mahasiswa, $tahunAkademik);
        
        $this->Cell(0, 5, $semesterNum, 0, 1);
        
        // IPK (Row 3)
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($w1, 5, 'I P K', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell($w2, 5, number_format((float)($mahasiswa->ipk ?? 0), 2), 0, 1);
        
        $this->Ln(5);
        
        // Title Block
        $this->SetFont('Arial', 'B', 11);
        $titleWidth = 160;
        $this->SetX((210 - $titleWidth) / 2);
        $this->Cell($titleWidth, 7, 'KARTU HASIL STUDI', 1, 1, 'C');
        $this->SetX((210 - $titleWidth) / 2);
        $this->SetFont('Arial', '', 10);
        $this->Cell($titleWidth, 6, strtoupper($tahunAkademik->nama_semester), 'LRB', 1, 'C');
        
        $this->Ln(5);
        
        // Table Header
        $this->SetFont('Arial', 'B', 8);
        $cols = [
            'no' => 10,
            'kode' => 25,
            'mk' => 95,
            'sks' => 15,
            'nilai' => 15,
            'bobot' => 20
        ];
        
        $this->Cell($cols['no'], 8, 'NO', 1, 0, 'C');
        $this->Cell($cols['kode'], 8, 'KODE MK', 1, 0, 'C');
        $this->Cell($cols['mk'], 8, 'MATA KULIAH', 1, 0, 'C');
        $this->Cell($cols['sks'], 8, 'SKS', 1, 0, 'C');
        $this->Cell($cols['nilai'], 8, 'NILAI', 1, 0, 'C');
        $this->Cell($cols['bobot'], 8, 'BOBOT', 1, 1, 'C');
        
        // Rows
        $nilaiList = $mahasiswa->nilai()
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->with('mataKuliah')
            ->get();
            
        $this->SetFont('Arial', '', 9);
        $totalSks = 0; $totalBobot = 0; $no = 1;
        
        if ($nilaiList->count() > 0) {
            foreach ($nilaiList as $nilai) {
                $mk = $nilai->mataKuliah;
                if ($mk) {
                    $sks = $mk->sks_mata_kuliah ?? 0;
                    $indeks = $nilai->nilai_indeks ?? 0;
                    $bobot = $sks * $indeks;
                    
                    $row = [
                        ['text' => (string)$no++, 'width' => $cols['no'], 'align' => 'C'],
                        ['text' => $mk->kode_matkul ?? '-', 'width' => $cols['kode'], 'align' => 'C'],
                        ['text' => $mk->nama_matkul ?? '-', 'width' => $cols['mk'], 'align' => 'L'],
                        ['text' => (string)$sks, 'width' => $cols['sks'], 'align' => 'C'],
                        ['text' => $nilai->nilai_huruf ?? '-', 'width' => $cols['nilai'], 'align' => 'C'],
                        ['text' => number_format($bobot, 2), 'width' => $cols['bobot'], 'align' => 'C'],
                    ];
                    
                    $h = $this->addRow($row, 7);
                    if ($h > 0) {
                        $this->AddPage();
                        $this->useBackgroundTemplate('khs');
                        $this->SetY(45);
                        $this->addRow($row, 7);
                    }
                    
                    $totalSks += $sks;
                    $totalBobot += $bobot;
                }
            }
        } else {
            $this->Cell(array_sum($cols), 10, 'Belum ada data Nilai untuk semester ini', 1, 1, 'C');
        }
        
        $ips = $totalSks > 0 ? $totalBobot / $totalSks : 0;
        
        // Summary Block
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(130, 8, 'TOTAL', 1, 0, 'R');
        $this->Cell(15, 8, $totalSks, 1, 0, 'C');
        $this->Cell(15, 8, '', 1, 0, 'C');
        $this->Cell(20, 8, number_format($totalBobot, 2), 1, 1, 'C');
        
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(45, 6, 'JUMLAH KREDIT (SKS)', 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(30, 6, $totalSks, 0, 1, 'L');
        
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(45, 6, 'INDEKS PRESTASI (IPS)', 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(30, 6, number_format($ips, 2), 0, 1, 'L');
        
        $this->Ln(10);
        
        // Signature
        if ($this->GetY() + 40 > $this->PageBreakTrigger) {
            $this->AddPage();
            $this->useBackgroundTemplate('khs');
            $this->SetY(45);
        }

        $ySign = $this->GetY();
        $kota = Setting::getValue('kota_terbit', 'Tanjungpinang');
        
        $this->SetXY(130, $ySign);
        $this->Cell(50, 5, $kota . ', ' . $this->formatTanggal(date('Y-m-d')), 0, 1, 'C');
        $this->SetX(130);
        $this->Cell(50, 5, 'KETUA PROGRAM STUDI,', 0, 1, 'C');
        $this->Ln(20);
        
        $signerId = Setting::getValue('signer_khs');
        $signer = $customSigner ?? Pejabat::find($signerId) ?? Pejabat::active()->where('jabatan', 'like', '%Kaprodi%')->first();
        
        $this->SetFont('Arial', 'BU', 10);
        $this->SetX(130);
        $this->Cell(50, 5, strtoupper($signer?->nama_lengkap ?? '........................'), 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->SetX(130);
        $this->Cell(50, 5, 'NIDN/NIP: ' . ($signer?->nidn ?? $signer?->nip ?? '........................'), 0, 1, 'C');
        
        // Final Output
        $filename = 'khs_' . $mahasiswa->nim . '_' . $tahunAkademik->id_semester . '.pdf';
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        $this->Output('F', $path);
        
        return $filename;
    }
}
