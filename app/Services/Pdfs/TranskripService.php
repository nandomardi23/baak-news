<?php

namespace App\Services\Pdfs;

use App\Models\Mahasiswa;
use App\Models\Pejabat;
use App\Models\Setting;

class TranskripService extends BasePdfService
{
    public function generate(Mahasiswa $mahasiswa, string $jenis = 'reguler'): string
    {
        $this->AddPage('P', 'A4');
        $this->SetMargins(15, 15, 15);
        $this->useBackgroundTemplate('transkrip');
        
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, 'TRANSKRIP NILAI', 0, 1, 'C');
        if ($jenis === 'rpl') {
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 5, '(Program Rekognisi Pembelajaran Lampau)', 0, 1, 'C');
        }
        $this->Ln(5);
        
        // Student Info
        $this->addStudentBasicInfo($mahasiswa);
        $this->Ln(5);
        
        // Logic for table
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(240, 240, 240);
        $this->Cell(10, 7, 'No', 1, 0, 'C', true);
        $this->Cell(20, 7, 'Kode', 1, 0, 'C', true);
        $this->Cell(75, 7, 'Mata Kuliah', 1, 0, 'C', true);
        $this->Cell(12, 7, 'SKS', 1, 0, 'C', true);
        $this->Cell(12, 7, 'Nilai', 1, 0, 'C', true);
        $this->Cell(12, 7, 'Angka', 1, 0, 'C', true);
        $this->Cell(15, 7, 'Bobot', 1, 0, 'C', true);
        $this->Cell(20, 7, 'Semester', 1, 1, 'C', true);
        
        $nilaiList = $mahasiswa->nilai()->with(['mataKuliah', 'tahunAkademik'])->orderBy('tahun_akademik_id')->get();
        
        $this->SetFont('Arial', '', 8);
        $totalSks = 0; $totalBobot = 0; $no = 1;
        foreach ($nilaiList as $nilai) {
            $mk = $nilai->mataKuliah;
            if ($mk && $nilai->nilai_huruf) {
                $bobot = $mk->sks_mata_kuliah * ($nilai->nilai_indeks ?? 0);
                
                $row = [
                    ['text' => (string)$no++, 'width' => 10, 'align' => 'C'],
                    ['text' => $mk->kode_matkul ?? '-', 'width' => 20, 'align' => 'C'],
                    ['text' => $mk->nama_matkul ?? '-', 'width' => 75, 'align' => 'L'],
                    ['text' => (string)$mk->sks_mata_kuliah, 'width' => 12, 'align' => 'C'],
                    ['text' => $nilai->nilai_huruf, 'width' => 12, 'align' => 'C'],
                    ['text' => number_format($nilai->nilai_indeks ?? 0, 2), 'width' => 12, 'align' => 'C'],
                    ['text' => number_format($bobot, 2), 'width' => 15, 'align' => 'C'],
                    ['text' => $nilai->tahunAkademik?->nama_semester ?? '-', 'width' => 20, 'align' => 'C'],
                ];
                
                $h = $this->addRow($row, 5);
                if ($h > 0) {
                     $this->AddPage();
                     $this->useBackgroundTemplate('transkrip');
                     $this->addRow($row, 5);
                }

                $totalSks += $mk->sks_mata_kuliah;
                $totalBobot += $bobot;
            }
        }
        
        // Summary
        $ipk = $totalSks > 0 ? $totalBobot / $totalSks : 0;
        $this->SetFont('Arial', 'B', 9);
        $this->Ln(5);
        $this->Cell(50, 6, 'Total SKS', 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0);
        $this->Cell(30, 6, $totalSks, 0, 1, 'L');
        $this->Cell(50, 6, 'Indeks Prestasi Kumulatif (IPK)', 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0);
        $this->Cell(30, 6, number_format($ipk, 2), 0, 1, 'L');
        $this->Cell(50, 6, 'Predikat Kelulusan', 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0);
        $this->Cell(30, 6, $this->getPredikat($ipk), 0, 1, 'L');
        
        // Signatures
        $this->Ln(10);
        $kaprodi = Pejabat::active()->where('jabatan', 'like', '%Kaprodi%')->first();
        $signerId = Setting::getValue('signer_transkrip');
        $ketua = Pejabat::find($signerId) ?? Pejabat::active()->byJabatan('Ketua')->first();
        
        $this->SetFont('Arial', '', 10);
        $this->Cell(95, 6, 'Kepala Program Studi', 0, 0, 'C');
        $this->Cell(95, 6, ($ketua?->jabatan ?? 'Ketua'), 0, 1, 'C');
        $this->Ln(20);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(95, 6, $kaprodi?->nama_lengkap ?? '........................', 0, 0, 'C');
        $this->Cell(95, 6, $ketua?->nama_lengkap ?? '........................', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(95, 5, 'NIP: ' . ($kaprodi?->nip ?? '........................'), 0, 0, 'C');
        $this->Cell(95, 5, 'NIK: ' . ($ketua?->nidn ?? $ketua?->nip ?? '........................'), 0, 1, 'C');
        
        $filename = 'transkrip_' . $jenis . '_' . $mahasiswa->nim . '_' . date('YmdHis') . '.pdf';
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        $this->Output('F', $path);
        
        return $filename;
    }
}
