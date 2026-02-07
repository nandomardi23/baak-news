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
        
        // Check for PDF template from database or file system
        // The TemplateDesignerController saves as: template-surat/type_timestamp.pdf
        // We want the latest 'surat' type template.
        
        $templatePath = null;
        
        // Try DB first
        $dbTemplate = \App\Models\LetterTemplate::where('type', 'surat')
            ->where('is_active', true)
            ->latest()
            ->first();

        if ($dbTemplate && file_exists(storage_path('app/public/' . $dbTemplate->file_path))) {
            $templatePath = storage_path('app/public/' . $dbTemplate->file_path);
        } else {
             // Fallback to glob
            $files = glob(storage_path('app/public/template-surat/surat_*.pdf'));
            if (!empty($files)) {
                usort($files, function ($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $templatePath = $files[0];
            }
        }
        
        if ($templatePath && file_exists($templatePath)) {
            $this->AddPage();
            $this->setSourceFile($templatePath);
            $templateId = $this->importPage(1);
            $this->useTemplate($templateId, 0, 0, null, null, true);
        } else {
            $this->AddPage();
            $this->addHeaderFromSuratService();
        }

        // Title
        $this->SetFont('Arial', '', 11);
        $this->SetY(38); // Adjusted Y again from 42 to 38
        $this->Cell(0, 5, 'SURAT KETERANGAN', 0, 1, 'C');
        
        $romanMonth = $this->getRomanMonth((int) date('n'));
        $defaultNomor = 'Sket /       /' . $romanMonth . '/' . date('Y');
        
        $displayNomor = $nomorSurat;
        if (empty($displayNomor)) {
            $displayNomor = $defaultNomor;
        } elseif (str_starts_with($displayNomor, '/')) {
            // If nomor is just suffix like /II/2026, prepend Sket
            $displayNomor = 'Sket /       ' . $displayNomor;
        }

        $this->Cell(0, 5, 'Nomor : ' . $displayNomor, 0, 1, 'C');
        
        $this->Ln(15); // Increased from 8 to 15 (approx 2 enters)

        // Student Data
        $startX = 30; // Left margin for labels
        $labelWidth = 40;
        
        $fields = [
            'Nama' => $mahasiswa->nama,
            'Nim' => $mahasiswa->nim,
            'Tempat/Tgl Lahir' => $mahasiswa->ttl,
            'Jenis Kelamin' => $mahasiswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
            'Alamat' => $mahasiswa->alamat_lengkap
        ];

        foreach ($fields as $label => $value) {
             $this->SetX($startX);
             $this->Cell($labelWidth, 6, $label, 0, 0);
             $this->Cell(5, 6, ':', 0, 0);
             
             // Handle multi-line value alignment (hanging indent)
             $currentX = $this->GetX();
             $originalMargin = $this->lMargin;
             
             $this->SetLeftMargin($currentX);
             $this->MultiCell(100, 6, $value, 0, 'L'); // Limit width to 100mm
             $this->SetLeftMargin($originalMargin);
        }

        $this->Ln(8);
        $this->SetX($startX);
        $this->Cell(0, 6, 'Anak dari :', 0, 1);

        // Parents Data
        $parentFields = [
            ['label' => 'Nama Ayah', 'value' => $this->formatText($mahasiswa->nama_ayah)],
            ['label' => 'Pekerjaan', 'value' => $this->formatText($mahasiswa->pekerjaan_ayah)],
            ['label' => 'Nama Ibu', 'value' => $this->formatText($mahasiswa->nama_ibu)],
            ['label' => 'Pekerjaan', 'value' => $this->formatText($mahasiswa->pekerjaan_ibu)],
            ['label' => 'Alamat', 'value' => $this->formatText($mahasiswa->alamat_ortu_lengkap ?? $mahasiswa->alamat_lengkap)]
        ];

        foreach ($parentFields as $field) {
            $this->SetX($startX);
            $this->Cell($labelWidth, 6, $field['label'], 0, 0);
            $this->Cell(5, 6, ':', 0, 0);
            
            // Handle multi-line value alignment (hanging indent)
            $currentX = $this->GetX();
            $originalMargin = $this->lMargin;
            
            $this->SetLeftMargin($currentX);
            $this->MultiCell(100, 6, $field['value'] ?: '-', 0, 'L'); // Limit width to 100mm
            $this->SetLeftMargin($originalMargin);
        }

        $this->Ln(8);
        $this->SetX($startX); // Align with data ($startX = 30)
        // Match right edge of data: Start 30 + Width 145 = 175 (Data value ends at 75 + 100 = 175)
        $this->MultiCell(145, 6, "        Adalah benar yang bersangkutan mahasiswa semester " . $this->getSemesterRoman($mahasiswa) . " Program Studi " . ($mahasiswa->programStudi?->nama_prodi ?? '-') . " Stikes Hang Tuah Tanjungpinang.", 0, 'J');

        // Signature
        $this->Ln(15);
        
        $kota = Setting::getValue('kota_terbit', 'Tanjungpinang');
        $bs = [
             1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
             'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $tanggalStr = date('j') . ' ' . $bs[(int)date('n')] . ' ' . date('Y');

        $this->SetX(120);
        $this->Cell(60, 5, $kota . ', ' . $tanggalStr, 0, 1, 'C');
        
        $this->SetX(120);
        $this->Cell(60, 5, Setting::getValue('kop_nama_kampus', 'Stikes Hang Tuah Tanjungpinang'), 0, 1, 'C');

        $this->SetX(120);
        $this->Cell(60, 5, 'Ketua', 0, 1, 'C');
        
        $this->Ln(25);
        
        // Signer Name
        $this->SetX(120);
        $signerName = $customSigner?->nama_lengkap ?? 'apt. Dra. Mila Abdullah, M.M';
        $this->Cell(60, 5, $signerName, 0, 1, 'C');

        // Pangkat / Golongan (e.g. Kolonel Laut (K/W) Purn)
        if ($customSigner?->pangkat_golongan) {
            $this->SetX(120);
            $this->Cell(60, 5, $customSigner->pangkat_golongan, 0, 1, 'C');
        }

        // Signer ID (Always 'NIK' label)
        // Prioritize NIP (NIK Kepegawaian), then NIDN, then KTP
        $idNumber = $customSigner?->nip ?? $customSigner?->nidn ?? $customSigner?->nik ?? '12060';
        
        $this->SetX(120);
        $this->Cell(60, 5, 'NIK: ' . $idNumber, 0, 1, 'C');

        $filename = 'surat_aktif_kuliah_' . $mahasiswa->nim . '_' . date('YmdHis') . '.pdf';
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $this->Output('F', $path);
        
        return $filename;
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
