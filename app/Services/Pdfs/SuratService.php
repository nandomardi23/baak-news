<?php

namespace App\Services\Pdfs;

use App\Models\Mahasiswa;
use App\Models\Pejabat;
use App\Models\Setting;
use setasign\Fpdi\Fpdi;

class SuratService extends BasePdfService
{
    public function generateSuratAktifKuliah(Mahasiswa $mahasiswa, array $dataTambahan = [], ?Pejabat $customSigner = null): string
    {
        $mahasiswa->load(['programStudi']);
        $nomorSurat = $dataTambahan['nomor_surat'] ?? null;
        
        // Check for PDF template
        $templatePath = storage_path('app/public/template-surat/template.pdf');
        
        if (file_exists($templatePath)) {
            $this->AddPage();
            $this->setSourceFile($templatePath);
            $templateId = $this->importPage(1);
            $this->useTemplate($templateId);
        } else {
            $this->AddPage();
            $this->addHeaderFromSuratService();
        }

        $this->SetFont('Arial', 'BU', 12);
        $this->SetY(45);
        $this->Cell(0, 7, 'SURAT KETERANGAN AKTIF KULIAH', 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 5, 'Nomor: ' . ($nomorSurat ?? '......./......./......./' . date('Y')), 0, 1, 'C');
        
        $this->Ln(10);
        $this->SetFont('Arial', '', 11);
        $this->MultiCell(0, 6, "Yang bertanda tangan di bawah ini,  Ketua Sekolah Tinggi Ilmu Kesehatan (STIKes) Hang Tuah Tanjungpinang, dengan ini menerangkan bahwa :", 0, 'J');
        
        $this->Ln(5);
        $this->addDataRow('Nama', $mahasiswa->nama);
        $this->addDataRow('NIM', $mahasiswa->nim);
        $this->addDataRow('Tempat / Tgl @Lahir', $mahasiswa->ttl);
        $this->addDataRow('Program Studi', $mahasiswa->programStudi?->nama_prodi);
        $this->addDataRow('Semester', $this->getSemesterRoman($mahasiswa));
        $this->addDataRow('Alamat', $mahasiswa->alamat_lengkap);

        $this->Ln(5);
        $this->MultiCell(0, 6, "Adalah benar mahasiswa Sekolah Tinggi Ilmu Kesehatan (STIKes) Hang Tuah Tanjungpinang dan masih aktif mengikuti perkuliahan pada Tahun Akademik " . date('Y') . "/" . (date('Y') + 1) . ".", 0, 'J');
        
        $this->Ln(5);
        $this->MultiCell(0, 6, "Orang tua / Wali mahasiswa tersebut adalah :", 0, 'J');
        
        $this->Ln(5);
        $this->addDataRow('Nama', $mahasiswa->nama_ayah ?: $mahasiswa->nama_ibu);
        $this->addDataRow('NIP / NRP', $dataTambahan['nip_ortu'] ?? '-');
        $this->addDataRow('Pangkat / Gol', $dataTambahan['pangkat_ortu'] ?? '-');
        $this->addDataRow('Instansi / Pekerjaan', $mahasiswa->pekerjaan_ayah ?: $mahasiswa->pekerjaan_ibu);

        $this->Ln(5);
        $this->MultiCell(0, 6, "Surat keterangan ini diberikan kepada yang bersangkutan untuk dapat dipergunakan sebagaimana mestinya.", 0, 'J');
        
        $this->Ln(5);
        $this->addCustomSignature($customSigner, 'Ketua');

        $filename = 'surat_aktif_kuliah_' . $mahasiswa->nim . '_' . date('YmdHis') . '.pdf';
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $this->Output('F', $path);
        
        return $filename;
    }

    protected function addDataRow(string $label, ?string $value): void
    {
        $this->SetFont('Arial', '', 11);
        $this->Cell(10, 6, '', 0, 0);
        $this->Cell(45, 6, $label, 0, 0);
        $this->Cell(5, 6, ':', 0, 0);
        $this->MultiCell(0, 6, $value ?? '-', 0, 'L');
    }

    protected function addHeaderFromSuratService(): void
    {
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 15, 10, 25);
        }

        $this->SetY(10);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(25); // Offset for logo
        $this->Cell(0, 6, Setting::getValue('kop_nama_yayasan', 'YAYASAN NALA'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(25);
        $this->MultiCell(0, 7, Setting::getValue('kop_nama_kampus', 'STIKES HANG TUAH TANJUNGPINANG'), 0, 'C');
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(25);
        $this->MultiCell(0, 5, Setting::getValue('kop_alamat', 'Jl. WR. Supratman No. 11 Tanjungpinang'), 0, 'C');
        
        $this->Ln(2);
        $this->SetLineWidth(0.8);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->SetLineWidth(0.2);
        $this->Line(15, $this->GetY() + 0.8, 195, $this->GetY() + 0.8);
        $this->Ln(5);
    }
}
