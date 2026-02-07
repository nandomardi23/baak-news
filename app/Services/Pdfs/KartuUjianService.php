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
        $this->SetMargins(0, 0, 0); // Zero margins
        $this->SetAutoPageBreak(false);

        // Check for template
        $templatePath = $this->getLatestTemplate('kartu_ujian');
        $useTemplate = false;

        if ($templatePath) {
            $this->setSourceFile($templatePath);
            $tplIdx = $this->importPage(1);
            $this->useTemplate($tplIdx, 0, 0, null, null, true);
            $useTemplate = true;
        }
        
        // Render single card at top position
        $this->addDesignCard($mahasiswa, $tahunAkademik, 0, $useTemplate);
        
        $filename = 'kartu_ujian_' . $mahasiswa->nim . '_' . $tahunAkademik->id_semester . '.pdf';
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        $this->Output('F', $path);
        
        return $filename;
    }

    /**
     * Generate batch kartu ujian for multiple students
     * Layout: 2 Cards per A4 Page (Portrait) - Full Bleed
     */
    public function generateBatch(Collection $mahasiswaList, TahunAkademik $tahunAkademik): string
    {
        $cardsPerPage = 1;
        $cardCount = 0;
        
        // Settings for layout A4
        $cardHeight = 148.5; // Irrelevant if 1 per page, but keeping for logic
        $marginTop = 0; 

        // Check for template
        $templatePath = $this->getLatestTemplate('kartu_ujian');
        $tplIdx = null;

        if ($templatePath) {
            $this->setSourceFile($templatePath);
            $tplIdx = $this->importPage(1);
        }
        
        foreach ($mahasiswaList as $mahasiswa) {
            // Add new page for each card (cardsPerPage=1) -> $cardCount % 1 == 0 (always true)
            // But logic was: if ($cardCount % $cardsPerPage === 0) -> AddPage
            
            if ($cardCount % $cardsPerPage === 0) {
                $this->AddPage('P', 'A4');
                $this->SetMargins(0, 0, 0); 
                $this->SetAutoPageBreak(false);
            }
            
            // Calculate Y position
            $positionIndex = $cardCount % $cardsPerPage;
            $startY = $marginTop + ($positionIndex * $cardHeight);
            
            // Draw Template Background
            if ($tplIdx) {
                // But code was drawing it per card? 
                // " $this->useTemplate($tplIdx, 0, $startY, 210); "
                // If template IS the card design, then yes.
                // Let's stick to previous logic just updating size.
                $this->useTemplate($tplIdx, 0, $startY, 215); 
            }

            // Draw Card Content
            $this->SetY($startY);
            $this->addDesignCard($mahasiswa, $tahunAkademik, $startY, !!$tplIdx);
            
            $cardCount++;
        }
        
        $filename = 'kartu_ujian_batch_' . $tahunAkademik->id_semester . '_' . time() . '.pdf';
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        $this->Output('F', $path);
        
        return $filename;
    }

    /**
     * Helper to get latest template file
     */
    private function getLatestTemplate(string $type): ?string 
    {
        $dir = storage_path('app/public/template-surat');
        if (!is_dir($dir)) return null;

        $files = glob($dir . '/' . $type . '_*.pdf');
        if (empty($files)) return null;

        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return $files[0];
    }

    /**
     * Draw a rounded rectangle
     */
    private function RoundedRect(float $x, float $y, float $w, float $h, float $r, string $style = ''): void
    {
        $k = $this->k;
        $hp = $this->h;
        
        if ($style == 'F') {
            $op = 'f';
        } elseif ($style == 'FD' || $style == 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }
        
        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        
        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
        $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
        
        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
        
        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
        
        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        
        $this->_out($op);
    }
    
    private function _Arc(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3): void
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1 * $this->k,
            ($h - $y1) * $this->k,
            $x2 * $this->k,
            ($h - $y2) * $this->k,
            $x3 * $this->k,
            ($h - $y3) * $this->k));
    }

    /**
     * Draw individual card with custom design
     */
    private function addDesignCard(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik, float $startY, bool $useTemplate = false): void
    {
        $startX = 10;
        
        // ---------------------------------------------------------
        // 1. Header Section (SKIP IF TEMPLATE)
        // ---------------------------------------------------------
        if (!$useTemplate) {
            // Logo
            $logoPath = public_path('images/logo.png');
            if (!file_exists($logoPath)) {
                $logoPath = storage_path('app/public/logo.png');
            }

            if (file_exists($logoPath)) {
                $this->Image($logoPath, $startX + 5, $startY + 5, 20); 
            } else {
                $this->Rect($startX + 5, $startY + 5, 20, 20);
                $this->SetXY($startX + 5, $startY + 12);
                $this->SetFont('Arial', 'I', 6);
                $this->Cell(20, 4, 'LOGO', 0, 0, 'C');
            }

            // Header Text
            $this->SetY($startY + 5);
            $this->SetX($startX);
            $this->SetFont('Arial', 'I', 9);
            $this->Cell(0, 5, 'Bidang Administrasi Akademik Kemahasiswaan', 0, 1, 'C');
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 6, 'SEKOLAH TINGGI ILMU KESEHATAN HANG TUAH', 0, 1, 'C');
            $this->Cell(0, 6, 'TANJUNGPINANG KEPULAUAN RIAU', 0, 1, 'C');
            $this->SetFont('Arial', '', 7);
            $this->Cell(0, 4, 'Jl. WR. Supratman, Kelurahan Air Raja, Kecamatan Tanjungpinang Timur, Kota Tanjungpinang,', 0, 1, 'C');
            $this->Cell(0, 4, 'Kepulauan Riau. Tlp (0771) 4440071', 0, 1, 'C');
            
            // Thick Line
            $this->SetLineWidth(0.5);
            $this->Line($startX, $startY + 32, $startX + 190, $startY + 32);
            $this->SetLineWidth(0.2); 
        }

        // ---------------------------------------------------------
        // 2. Student Info (ALWAYS PRINT Labels & Data)
        // ---------------------------------------------------------
        // ---------------------------------------------------------
        // 2. Student Info (ALWAYS PRINT Labels & Data)
        // ---------------------------------------------------------
        // Lowered by request
        $infoY = $startY + 41; 
        $this->SetY($infoY);
        $this->SetFont('Arial', '', 11); // Increased from 9 to 11
        
        $fields = [
            'NAMA' => strtoupper($mahasiswa->nama),
            'NIM' => $mahasiswa->nim,
            'PRODI' => $mahasiswa->programStudi?->nama_prodi ?? '-',
            'SEMESTER' => $tahunAkademik->nama_semester, 
        ];
        
        foreach ($fields as $label => $value) {
            $this->SetX($startX + 10);
            $this->Cell(25, 7, $label, 0, 0); // Height 6->7
            $this->Cell(3, 7, ':', 0, 0);     // Height 6->7
            
            // Value
            $currentX = $this->GetX();
            $currentY = $this->GetY();
            $this->Cell(80, 7, $value, 0, 1); // Height 6->7
            
            // Dashed Line (Always print)
            $lineY = $currentY + 6.5; // Adjusted for height 7
            $this->SetLineWidth(0.1);
            for ($i = $currentX; $i < ($currentX + 80); $i += 2) {
                $this->Line($i, $lineY, $i+1, $lineY);
            }
        }

        // "Kartu Ujian" Box (ALWAYS PRINT) - with rounded corners
        $boxX = 150;
        $boxY = $infoY;
        $this->SetFillColor(220, 220, 220); 
        $this->RoundedRect($boxX, $boxY, 40, 12, 2, 'DF'); // Radius 2mm
        $this->SetXY($boxX, $boxY);
        $this->SetFont('Arial', 'B', 12); // Font 10->12
        $this->Cell(40, 12, 'Kartu Ujian', 0, 0, 'C');

        // ---------------------------------------------------------
        // 3. Exam Table (ALWAYS PRINT with Headers & Borders)
        // ---------------------------------------------------------
        $tableY = $infoY + 35; // Increased spacing
        $this->SetY($tableY);
        $this->SetX($startX);
        
        $cols = [
            ['w' => 10, 't' => 'NO'],   // Width adjusted for bigger font
            ['w' => 45, 't' => 'TANGGAL'],
            ['w' => 93, 't' => 'MATA KULIAH'], // Adjusted to keep total width ~190
            ['w' => 42, 't' => 'PARAF'], 
        ];

        // Header (Always Print)
        $this->SetFillColor(0, 191, 255); 
        $this->SetTextColor(0, 0, 0); 
        $this->SetFont('Arial', 'B', 10); // Font 8->10
        foreach ($cols as $col) {
            $this->Cell($col['w'], 7, $col['t'], 1, 0, 'C', true); // Height 6->7
        }
        $this->Ln();
        
        // Data Rows
        $this->SetFillColor(255, 255, 255); 
        $this->SetFont('Arial', '', 10); // Font 8->10
        $this->SetTextColor(0, 0, 0);
        
        $krs = $mahasiswa->krs()
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->with('details.mataKuliah')
            ->first();
            
        $maxRows = 8;
        $rowCount = 0;
        $border = 1; // Always use border
        
        if ($krs && $krs->details->count() > 0) {
            foreach ($krs->details as $index => $detail) {
                if ($rowCount >= $maxRows) break;
                
                $mk = $detail->mataKuliah;
                $this->SetX($startX);
                
                $this->Cell($cols[0]['w'], 7, $index + 1, $border, 0, 'C'); // Height 6->7
                $this->Cell($cols[1]['w'], 7, '', $border, 0, 'C'); 
                $this->Cell($cols[2]['w'], 7, substr($mk->nama_matkul ?? '-', 0, 50), $border, 0, 'L');
                $this->Cell($cols[3]['w'], 7, '', $border, 1, 'C'); 
                $rowCount++;
            }
        }
        
        // Fill remaining rows
        while ($rowCount < $maxRows) {
            $this->SetX($startX);
            $this->Cell($cols[0]['w'], 7, $rowCount + 1, 1, 0, 'C');
            $this->Cell($cols[1]['w'], 7, '', 1, 0, 'C');
            $this->Cell($cols[2]['w'], 7, '', 1, 0, 'L');
            $this->Cell($cols[3]['w'], 7, '', 1, 1, 'C');
            $rowCount++;
        }

        // ---------------------------------------------------------
        // 4. Footer & Signature (ALWAYS PRINT)
        // ---------------------------------------------------------
        $footerY = $this->GetY() + 5;
        $this->SetY($footerY);
        $this->SetX($startX);
        $this->SetFont('Arial', 'B', 9); // Font 7->9
        $this->Cell(20, 5, 'Catatan :', 0, 1);
        $this->SetFont('Arial', '', 9); // Font 7->9
        
        $notes = [
             chr(149) . " Kartu Ujian harap dibawa setiap kali ujian",
             chr(149) . " Bagi Peserta yang tidak membawa kartu ujian tidak diperkenankan",
             "   mengikuti ujian",
             chr(149) . " Peserta di wajibkan menggunakan Seragam dan Atribut lengkap"
        ];
        
        foreach ($notes as $note) {
            $this->SetX($startX);
            $this->Cell(0, 4.5, $note, 0, 1); // Height 3.5->4.5
        }
        
        // Signature
        $sigY = $this->GetY() + 14; // Increased spacing (approx 2 enters)
        if ($sigY < $startY + 110) $sigY = $startY + 110;

        // City & Date
        $this->SetXY(140, $sigY);
        $kota = Setting::getValue('kota_terbit', 'Tanjungpinang');
        
        // Indonesian month names
        $bulanIndo = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $tanggal = date('d') . ' ' . $bulanIndo[(int)date('n')] . ' ' . date('Y');
        
        $this->SetFont('Arial', '', 10); 
        $this->Cell(50, 5, $kota . ', ' . $tanggal, 0, 1, 'C'); 

        $this->SetX(140);
        $this->SetFont('Arial', '', 10); 
        $this->Cell(50, 5, 'Ka. BAAK', 0, 1, 'C'); 

        // Name
        $this->SetXY(140, $sigY + 25); // Adjusted to +25 for font size 10
        $this->SetFont('Arial', 'U', 10); 
        
        // Get signer from setting, or fallback to Ka. BAAK
        $signerId = Setting::getValue('signer_kartu_ujian');
        $signer = null;
        
        if ($signerId) {
            $signer = Pejabat::find($signerId);
        }
        
        if (!$signer) {
            $signer = Pejabat::where('jabatan', 'like', '%BAAK%')->first();
        }

        $nama = $signer?->nama_lengkap ?? 'Budi Prasetyo, S.Kom';
        $nidn = $signer?->nidn ?? $signer?->nip ?? '11074';

        $this->Cell(50, 5, $nama, 0, 1, 'C'); 
        
        // NIK
        $this->SetX(140);
        $this->SetFont('Arial', '', 10); 
        $this->Cell(50, 5, 'NIK : ' . $nidn, 0, 1, 'C'); 

        // Batas Potong (Cut Line)
        $cutY = $this->GetY() + 10; // Dynamic spacing 10mm after NIK
        $this->SetY($cutY);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        
        // Draw dashed line
        $this->SetLineWidth(0.2);
        // Dash pattern: 2mm line, 1mm space
        for ($i = 5; $i < 205; $i += 3) {
             $this->Line($i, $cutY, $i + 2, $cutY);
        }
        $this->Text(10, $cutY - 1, 'Batas Potong / Cut Here');
        $this->SetTextColor(0, 0, 0); // Reset color
    }
}
