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
        $this->SetFont('Arial', 'B', 12);
        $this->SetY(52); // Dikembalikan ke posisi 52 agar ada spasi yang cukup dengan garis kop surat
        $this->Cell(0, 5, 'SURAT KETERANGAN', 0, 1, 'C');

        $romanMonth = $this->getRomanMonth((int) date('n'));
        $defaultNomor = 'SKet /             / ' . $romanMonth . ' / ' . date('Y') . ' / STIKes';

        $displayNomor = $nomorSurat;
        if (empty($displayNomor)) {
            $displayNomor = $defaultNomor;
        } elseif (str_starts_with($displayNomor, '/')) {
            // If nomor is just suffix, prepend SKet
            $displayNomor = 'SKet /             ' . $displayNomor;
        }

        $this->Cell(0, 5, 'Nomor : ' . $displayNomor, 0, 1, 'C');

        $this->Ln(12); // Jarak antara nomor dan identitas (disesuaikan menjadi 12)

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

        $this->Ln(12); // Dibuat lebih renggang dari data mhs ke anak dari (sebelumnya 8)
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

        $this->Ln(12); // Dibuat lebih renggang dari data ortu ke paragraf penutup (sebelumnya 8)
        $this->SetX($startX); // Align with data ($startX = 30)
        // Match right edge of data: Start 30 + Width 145 = 175 (Data value ends at 75 + 100 = 175)
        $this->MultiCell(145, 6, "        Adalah benar yang bersangkutan mahasiswa semester " . $this->getSemesterRoman($mahasiswa) . " Program Studi " . ($mahasiswa->programStudi?->nama_cetak ?? '-') . " Stikes Hang Tuah Tanjungpinang.", 0, 'J');

        // Signature
        $this->Ln(20); // Dibuat lebih renggang dari paragraf ke tanggal wilayah TTD (sebelumnya 15)

        $kota = Setting::getValue('kota_terbit', 'Tanjungpinang');
        $bs = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $tanggalStr = date('j') . ' ' . $bs[(int) date('n')] . ' ' . date('Y');

        $this->SetX(120);
        $this->Cell(60, 5, $kota . ', ' . $tanggalStr, 0, 1, 'C');

        $this->SetX(120);
        $this->Cell(60, 5, 'STIKes Hang Tuah Tanjungpinang', 0, 1, 'C');

        $this->SetX(120);
        $jabatan = $customSigner?->jabatan ?? 'Ketua';
        $this->Cell(60, 5, $jabatan, 0, 1, 'C');

        $this->Ln(25);

        // Signer Name
        $this->SetX(120);
        $signerName = $customSigner?->nama_lengkap ?? 'apt. Dra. Mila Abdullah, M.M';
        $this->Cell(60, 5, $signerName, 0, 1, 'C');

        // Pangkat / Golongan (Militer dsb)
        if ($customSigner && $customSigner->pangkat_golongan) {
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
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(25); // Offset for logo
        $this->Cell(0, 5, Setting::getValue('kop_nama_yayasan', 'YAYASAN NALA'), 0, 1, 'C');

        $this->SetFont('Arial', 'B', 14);
        $this->Cell(25);
        $this->Cell(0, 7, 'STIKES HANG TUAH TANJUNGPINANG', 0, 1, 'C');
        
        $this->Cell(25);
        $this->Cell(0, 7, 'KEPULAUAN RIAU', 0, 1, 'C');

        $this->SetFont('Arial', '', 8);
        $this->Cell(25);
        $alamat = Setting::getValue('kop_alamat', 'Jl. WR. Supratman, No. 11, Air Raja, Tanjungpinang Timur, Kota Tanjungpinang, Kepulauan Riau. Tlp / Wa (0771) 4440071 / 081170066770');
        $this->MultiCell(0, 4, $alamat, 0, 'C');

        $this->Ln(1);
        $this->SetLineWidth(0.8);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->SetLineWidth(0.2);
        $this->Line(15, $this->GetY() + 0.8, 195, $this->GetY() + 0.8);
        $this->Ln(5);
    }
}
