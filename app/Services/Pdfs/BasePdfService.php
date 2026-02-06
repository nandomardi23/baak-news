<?php

namespace App\Services\Pdfs;

use App\Models\Pejabat;
use App\Models\Setting;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use setasign\Fpdi\Fpdi;

abstract class BasePdfService extends Fpdi
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Use template as background if exists (Database driven)
     */
    protected function useBackgroundTemplate(string $type): void
    {
        $template = \App\Models\LetterTemplate::where('type', $type)
            ->where('is_active', true)
            ->whereNotNull('file_path')
            ->latest()
            ->first();

        if ($template && file_exists(storage_path('app/public/' . $template->file_path))) {
            $pageCount = $this->setSourceFile(storage_path('app/public/' . $template->file_path));
            $tplIdx = $this->importPage(1);
            $this->useTemplate($tplIdx, 0, 0, null, null, true);
        } else {
            // Fallback to file search if not in database
            $this->useFileTemplate($type);
        }
    }

    /**
     * Search for template files in storage
     */
    protected function useFileTemplate(string $prefix): void
    {
        $dir = storage_path('app/public/template-surat');
        $files = [];
        
        if (is_dir($dir)) {
            $files = glob($dir . '/' . $prefix . '_*.pdf');
            if (empty($files)) {
                $staticFile = $dir . '/' . $prefix . '.pdf';
                if (file_exists($staticFile)) {
                    $files = [$staticFile];
                }
            }
        }

        if (!empty($files)) {
            usort($files, function ($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $latestFile = $files[0];
            $this->setSourceFile($latestFile);
            $tplIdx = $this->importPage(1);
            $this->useTemplate($tplIdx, 0, 0, null, null, true);
        } else {
            $this->addKopSurat();
        }
    }

    /**
     * Add Kop Surat from Settings
     */
    protected function addKopSurat(): void
    {
        // Add absolute Y position to prevent clipping
        $this->SetY(10); 
        
        $namaYayasan = Setting::getValue('kop_nama_yayasan', 'YAYASAN NALA');
        $namaKampus = Setting::getValue('kop_nama_kampus', 'SEKOLAH TINGGI ILMU KESEHATAN HANG TUAH TANJUNGPINANG KEPULAUAN RIAU');
        $alamat = Setting::getValue('kop_alamat', 'Jl. WR. Supratman, Air Raja, Tanjungpinang Timur, Kota Tanjungpinang, Kepulauan Riau. Tlp (0771) 4440071');
        
        // Yayasan
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 5, $namaYayasan, 0, 1, 'C');
        
        // Kampus
        $this->SetFont('Arial', 'B', 14);
        $this->MultiCell(0, 6, $namaKampus, 0, 'C');
        
        // Alamat
        $this->SetFont('Arial', '', 8);
        $this->MultiCell(0, 4, $alamat, 0, 'C');
        
        // Decorative Lines
        $this->Ln(1);
        $this->SetLineWidth(0.8);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->SetLineWidth(0.2);
        $this->Line(15, $this->GetY() + 0.8, 195, $this->GetY() + 0.8);
        $this->Ln(5);
    }

    protected function addCustomSignature(?Pejabat $pejabat, ?string $fallbackJabatan = null): void
    {
        $kota = Setting::getValue('kota_terbit', 'Tanjungpinang');
        
        $this->Ln(15);
        $this->SetFont('Arial', '', 11);
        $this->Cell(100, 6, '', 0, 0);
        $this->Cell(0, 6, $kota . ', ' . date('d F Y') . ' ' . Setting::getValue('kop_nama_kampus', 'Stikes Hang Tuah'), 0, 1, 'C');
        
        $jabatan = $pejabat?->jabatan ?? $fallbackJabatan ?? 'Pimpinan';
        $this->Cell(100, 6, '', 0, 0);
        $this->Cell(0, 6, $jabatan, 0, 1, 'C');

        $this->Ln(20);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(100, 6, '', 0, 0);
        $this->Cell(0, 6, $pejabat?->nama_lengkap ?? '........................', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 11);
        $this->Cell(100, 6, '', 0, 0);
        
        if ($pejabat && $pejabat->pangkat_golongan) {
            $this->Cell(0, 6, $pejabat->pangkat_golongan, 0, 1, 'C');
            $this->Cell(100, 6, '', 0, 0);
        }
        
        $idNumber = $pejabat?->nidn ?? $pejabat?->nip;
        $idLabel = $pejabat?->nidn ? 'NIDN' : ($pejabat?->nip ? 'NIP' : 'NIK');
        
        $this->Cell(0, 6, $idLabel . ': ' . ($idNumber ?? '........................'), 0, 1, 'C');
    }

    protected function writeAdaptiveCell($w, $h, $text, $border = 0, $ln = 0, $align = '', $fill = false)
    {
        $initialFontSize = $this->FontSizePt;
        $width = $this->GetStringWidth($text);
        
        while ($width > ($w - 1) && $this->FontSizePt > 6) {
            $this->SetFontSize($this->FontSizePt - 0.5);
            $width = $this->GetStringWidth($text);
        }
        
        $this->Cell($w, $h, $text, $border, $ln, $align, $fill);
        $this->SetFontSize($initialFontSize);
    }

    protected function formatTanggal($dateString): string
    {
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        if (!$dateString) return '-';
        
        $date = date_parse($dateString);
        if ($date['error_count'] === 0 && checkdate($date['month'], $date['day'], $date['year'])) {
            return $date['day'] . ' ' . $bulanIndo[$date['month']] . ' ' . $date['year'];
        }

        return '-';
    }

    protected function getRomanMonth(int $month): string
    {
        $romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romans[$month - 1] ?? '';
    }

    protected function formatText(?string $text): string
    {
        if (!$text) return '-';
        
        $formatted = ucwords(strtolower($text));
        
        // Fix specific abbreviations
        $formatted = preg_replace('/\bRt\b/', 'RT', $formatted);
        $formatted = preg_replace('/\bRw\b/', 'RW', $formatted);
        $formatted = preg_replace('/\bSim\b/', 'SIM', $formatted);
        $formatted = preg_replace('/\bKtp\b/', 'KTP', $formatted);
        
        return $formatted;
    }

    protected function getSemesterRoman($ta) {
        return '.......'; 
    }

    protected function hexToRgb($hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6) return ['r' => 0, 'g' => 0, 'b' => 0];
        
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }
    
    protected function base64ToImage($base64_string) {
        $data = explode(',', $base64_string);
        return isset($data[1]) ? base64_decode($data[1]) : '';
    }

    protected function getPredikat($ipk) {
        if ($ipk >= 3.51) return 'Dengan Pujian';
        if ($ipk >= 2.76) return 'Sangat Memuaskan';
        if ($ipk >= 2.00) return 'Memuaskan';
        return 'Cukup';
    }

    protected function replacePlaceholder(string $key, array $data): string
    {
        $key = trim($key, '{} ');
        if ($key === 'tabel_krs' || $key === 'tabel_nilai') {
            return '';
        }
        return $data[$key] ?? $data[str_replace('_', '', $key)] ?? '{{'.$key.'}}';
    }

    protected function replacePlaceholdersInText(string $text, array $data): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($data) {
            return $this->replacePlaceholder($matches[1], $data);
        }, $text);
    }

    protected function addStudentBasicInfo(Mahasiswa $mahasiswa): void
    {
        $this->SetFont('Arial', '', 10);
        $this->Cell(30, 6, 'NIM', 0, 0);
        $this->Cell(5, 6, ':', 0, 0);
        $this->Cell(0, 6, $mahasiswa->nim, 0, 1);
        $this->Cell(30, 6, 'Nama', 0, 0);
        $this->Cell(5, 6, ':', 0, 0);
        $this->Cell(0, 6, $mahasiswa->nama, 0, 1);
        $this->Cell(30, 6, 'Program Studi', 0, 0);
        $this->Cell(5, 6, ':', 0, 0);
        $this->Cell(0, 6, $mahasiswa->programStudi?->nama_prodi ?? '-', 0, 1);
        $this->Cell(30, 6, 'Angkatan', 0, 0);
        $this->Cell(5, 6, ':', 0, 0);
        $angkatan = $mahasiswa->angkatan ?? (strlen($mahasiswa->nim) >= 2 ? '20' . substr($mahasiswa->nim, 0, 2) : '-');
        $this->Cell(0, 6, $angkatan, 0, 1);
    }
    /**
     * Generate PDF from Letter Template
     */
    public function generateFromTemplate(\App\Models\LetterTemplate $template, array $data): string
    {
        ini_set('memory_limit', '512M');

        $this->AddPage($template->orientation === 'landscape' ? 'L' : 'P', $template->page_size);
        $this->SetMargins(0, 0, 0);
        $this->SetAutoPageBreak(false);

        $canvasData = $template->canvas_data;
        if (!$canvasData || !isset($canvasData['objects'])) {
            return ''; 
        }

        foreach ($canvasData['objects'] as $obj) {
            $this->renderCanvasObject($obj, $data);
        }
        
        $nim = $data['nim'] ?? 'unknown';
        $filename = $template->slug . '_' . $nim . '_' . date('YmdHis') . '.pdf';
        
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        $this->Output('F', $path);
        
        return $filename;
    }

    protected function renderCanvasObject(array $obj, array $data): void
    {
        $scale = 0.264583;
        
        $type = $obj['type'];
        $left = ($obj['left'] ?? 0) * $scale;
        $top = ($obj['top'] ?? 0) * $scale;
        $width = ($obj['width'] ?? 0) * ($obj['scaleX'] ?? 1) * $scale;
        $height = ($obj['height'] ?? 0) * ($obj['scaleY'] ?? 1) * $scale;

        if ($type === 'textbox' || $type === 'text' || $type === 'i-text') {
            $text = $obj['text'] ?? '';
            
            if (isset($obj['isPlaceholder']) && $obj['isPlaceholder']) {
                $key = $obj['placeholderKey'] ?? $text;
                $text = $this->replacePlaceholder($key, $data);
            } else {
                $text = $this->replacePlaceholdersInText($text, $data);
            }

            $fontFamily = 'Arial'; 
            $fontStyle = '';
            if (($obj['fontWeight'] ?? 'normal') === 'bold') $fontStyle .= 'B';
            if (($obj['fontStyle'] ?? 'normal') === 'italic') $fontStyle .= 'I';
            if (($obj['underline'] ?? false)) $fontStyle .= 'U';
            
            $color = $this->hexToRgb($obj['fill'] ?? '#000000');
            $this->SetTextColor($color['r'], $color['g'], $color['b']);
            
            $fontSize = ($obj['fontSize'] ?? 12) * 0.75; 
            $this->SetFont($fontFamily, $fontStyle, $fontSize);
            
            $this->SetXY($left, $top);
            $this->MultiCell($width, 6, $text, 0, $obj['textAlign'] ?? 'left');
        
        } elseif ($type === 'image') {
            if (isset($obj['src'])) {
                $imgData = $obj['src'];
                if (strpos($imgData, 'data:image') === 0) {
                    $imgData = $this->base64ToImage($imgData);
                }
                
                $tempImg = tempnam(sys_get_temp_dir(), 'img');
                file_put_contents($tempImg, file_get_contents($imgData));
                
                $this->Image($tempImg, $left, $top, $width, $height);
                unlink($tempImg);
            }
        } elseif ($type === 'rect') {
            $fill = ($obj['fill'] ?? 'transparent') !== 'transparent' ? $this->hexToRgb($obj['fill']) : null;
            $stroke = ($obj['stroke'] ?? null) ? $this->hexToRgb($obj['stroke']) : null;
            $style = '';
            
            if ($fill) {
                $this->SetFillColor($fill['r'], $fill['g'], $fill['b']);
                $style .= 'F';
            }
            if ($stroke) {
                $this->SetDrawColor($stroke['r'], $stroke['g'], $stroke['b']);
                $this->SetLineWidth(($obj['strokeWidth'] ?? 1) * $scale);
                $style .= 'D';
            }
            
            if ($style) {
                $this->Rect($left, $top, $width, $height, $style);
            }
        } elseif ($type === 'line') {
            $x1 = (($obj['x1'] ?? 0) + ($obj['left'] ?? 0)) * $scale;
            $y1 = (($obj['y1'] ?? 0) + ($obj['top'] ?? 0)) * $scale;
            $x2 = (($obj['x2'] ?? 0) + ($obj['left'] ?? 0)) * $scale;
            $y2 = (($obj['y2'] ?? 0) + ($obj['top'] ?? 0)) * $scale;
            
            $stroke = $this->hexToRgb($obj['stroke'] ?? '#000000');
            $this->SetDrawColor($stroke['r'], $stroke['g'], $stroke['b']);
            $this->SetLineWidth(($obj['strokeWidth'] ?? 1) * $scale);
            $this->Line($x1, $y1, $x2, $y2);
        }
    }
    /**
     * Get calculated semester number based on angkatan and tahun akademik
     */
    protected function getMahasiswaSemester(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik): int
    {
        $angkatan = $mahasiswa->angkatan;
        
        if (!$angkatan) {
            $nim = $mahasiswa->nim;
            if (strlen($nim) >= 4) {
                // Prioritize index 2-3 (YY) which is common in many NIM structures
                $yy23 = substr($nim, 2, 2);
                $yy01 = substr($nim, 0, 2);
                $currentYY = (int) date('y');
                
                // Smart guess: which one looks like a valid year (e.g. 15 to current+1)?
                if (is_numeric($yy23) && $yy23 >= 15 && $yy23 <= $currentYY + 1) {
                    $angkatan = '20' . $yy23;
                } elseif (is_numeric($yy01) && $yy01 >= 15 && $yy01 <= $currentYY + 1) {
                    $angkatan = '20' . $yy01;
                }
            }
        }
        
        if (!$angkatan) {
            $angkatan = date('Y'); // Last resort
        }

        $yearDiff = (int) $tahunAkademik->tahun - (int) $angkatan;
        $semesterType = strtolower($tahunAkademik->semester);
        
        $semesterNum = ($yearDiff * 2) + ($semesterType === 'ganjil' ? 1 : 2);
        
        return (int) $semesterNum;
    }

    /**
     * Draw a table row with synchronized column heights
     */
    protected function addRow(array $cols, float $lineHeight = 6, $border = 1)
    {
        // 1. Calculate max lines/height
        $maxLines = 1;
        foreach ($cols as $col) {
            $lines = $this->NbLines($col['width'], $col['text']);
            if ($lines > $maxLines) $maxLines = $lines;
        }
        $h = $lineHeight * $maxLines;
        
        // 2. Check page break (Manual check to trigger signal)
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            return $h; // Return height to indicate how much was needed
        }
        
        // 3. Draw row
        $startX = $this->GetX();
        $startY = $this->GetY();
        
        foreach ($cols as $col) {
            $x = $this->GetX();
            $y = $this->GetY();
            
            // Draw border for the full height of the row
            if ($border) {
                $this->Rect($x, $y, $col['width'], $h);
            }
            
            // Draw text
            $this->MultiCell($col['width'], $lineHeight, $col['text'], 0, $col['align'] ?? 'L');
            
            // Restore position for next column
            $this->SetXY($x + $col['width'], $y);
        }
        
        // Move position to the next line after the entire row
        $this->SetXY($startX, $startY + $h);
        return 0; // Success
    }

    public function NbLines($w, $txt)
    {
        $cw = 500;
        if($w==0) $w = 210 - 30; // Default A4 minus margins
        $wmax = ($w - 2 * 2) * 1000 / ($this->FontSize ?: 10);
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if($nb > 0 && $s[$nb-1] == "\n") $nb--;
        $sep = -1;
        $i = 0; $j = 0; $l = 0; $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++; $sep = -1; $j = $i; $l = 0; $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += $cw;
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else $i = $sep+1;
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else $i++;
        }
        return $nl;
    }
}
