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
        $this->SetLeftMargin(15);
        $this->SetRightMargin(15);
        $this->SetAutoPageBreak(true, 15);

        $this->useBackgroundTemplate('khs');

        $this->SetY(41);

        // Title Block
        $this->SetFont('Arial', 'BU', 11);
        $this->Cell(0, 5, 'KARTU HASIL STUDI (KHS)', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, $tahunAkademik->nama_semester ?? '', 0, 1, 'C');
        $this->Ln(5);

        // Student Info Block
        $this->SetFont('Arial', '', 8);

        $w1 = 25; // Left label width
        $sep = 3; // Separator width
        $w2 = 65; // Left value width
        $w3 = 30; // Right label width
        $w4 = 57; // Right value width

        $semesterNum = $this->getMahasiswaSemester($mahasiswa, $tahunAkademik);

        // Row 1: SEMESTER & PROGRAM STUDI
        $this->Cell($w1, 5, 'SEMESTER', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->Cell($w2, 5, $semesterNum, 0, 0);

        $this->Cell($w3, 5, 'PROGRAM STUDI', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->writeAdaptiveCell($w4, 5, strtoupper($mahasiswa->programStudi?->nama_cetak ?? '-'), 0, 1);

        // Row 2: NIM & NAMA
        $this->Cell($w1, 5, 'NIM', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->Cell($w2, 5, $mahasiswa->nim, 0, 0);

        $this->Cell($w3, 5, 'NAMA', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->writeAdaptiveCell($w4, 5, strtoupper($mahasiswa->nama), 0, 1);

        // Row 3: PEMBIMBING AKADEMIK
        $this->Cell($w1, 5, 'PEMBIMBING', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        
        $pembimbingName = '';
        if ($mahasiswa->dosenWali) {
            $pembimbingName = strtoupper($mahasiswa->dosenWali->nama_lengkap ?? $mahasiswa->dosenWali->nama ?? '');
        }
        $this->Cell($w2, 5, $pembimbingName, 0, 1);
        
        $this->Cell($w1, 5, 'AKADEMIK', 0, 1);
        $this->Ln(2);

        // Table Header
        $this->SetFont('Arial', 'B', 8);
        $x = $this->GetX();
        $y = $this->GetY();
        
        // Main headers setup
        $this->Rect($x, $y, 8, 10);
        $this->SetXY($x, $y); 
        $this->Cell(8, 10, 'No.', 0, 0, 'C');
        
        $this->Rect($this->GetX(), $y, 70, 10);
        $this->Cell(70, 10, 'NAMA MATA KULIAH', 0, 0, 'C');
        
        $this->Rect($this->GetX(), $y, 25, 10);
        $this->Cell(25, 10, 'KODE', 0, 0, 'C');
        
        $this->Rect($this->GetX(), $y, 12, 10);
        $this->Cell(12, 10, 'SKS', 0, 0, 'C');
        
        // NILAI AKHIR spanned column
        $this->Rect($this->GetX(), $y, 30, 5);
        $this->Cell(30, 5, 'NILAI AKHIR', 0, 0, 'C');
        
        $this->Rect($this->GetX(), $y, 35, 10);
        $this->Cell(35, 10, 'Ket', 0, 0, 'C');
        
        // Sub-headers for NILAI AKHIR
        $this->SetXY($x + 8 + 70 + 25 + 12, $y + 5);
        $this->Rect($this->GetX(), $y + 5, 10, 5); 
        $this->Cell(10, 5, 'HM', 0, 0, 'C');
        
        $this->Rect($this->GetX(), $y + 5, 10, 5); 
        $this->Cell(10, 5, 'NM', 0, 0, 'C');
        
        $this->Rect($this->GetX(), $y + 5, 10, 5); 
        $this->Cell(10, 5, 'KN', 0, 1, 'C');

        // Records
        $nilaiList = $mahasiswa->nilai()
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->with('mataKuliah')
            ->get();

        $this->SetFont('Arial', '', 8);
        $totalSKS = 0;
        $totalKN = 0;
        $no = 1;

        if ($nilaiList->count() > 0) {
            foreach ($nilaiList as $nilai) {
                $mk = $nilai->mataKuliah;
                if ($mk) {
                    $sks = $mk->sks_mata_kuliah ?? 0;
                    $indeks = $nilai->nilai_indeks ?? 0;
                    $hm = $nilai->nilai_huruf ?? '-';
                    $kn = $sks * $indeks;
                    
                    // Ket logic based on index or grades
                    $ket = ($indeks > 0 && strtoupper($hm) !== 'E') ? 'LULUS' : 'TIDAK LULUS';

                    $row = [
                        ['text' => (string) $no++, 'width' => 8, 'align' => 'C'],
                        ['text' => $mk->nama_matkul ?? '-', 'width' => 70, 'align' => 'L'],
                        ['text' => $mk->kode_matkul ?? '-', 'width' => 25, 'align' => 'C'],
                        ['text' => (string) $sks, 'width' => 12, 'align' => 'C'],
                        ['text' => $hm, 'width' => 10, 'align' => 'C'],
                        ['text' => number_format($indeks, 2), 'width' => 10, 'align' => 'C'],
                        ['text' => is_numeric($kn) ? (string) round($kn) : '-', 'width' => 10, 'align' => 'C'],
                        ['text' => $ket, 'width' => 35, 'align' => 'C'],
                    ];

                    $h = $this->addRow($row, 6);
                    if ($h > 0) {
                        $this->AddPage();
                        $this->useBackgroundTemplate('khs');
                        $this->SetY(41);
                        $this->addRow($row, 6);
                    }

                    $totalSKS += $sks;
                    $totalKN += $kn;
                }
            }
        } else {
            $this->Cell(180, 10, 'Belum ada data Nilai untuk semester ini', 1, 1, 'C');
        }

        // Summary Row
        $this->SetFont('Arial', '', 8);
        $this->Cell(8 + 70 + 25, 6, 'Jumlah', 1, 0, 'C');
        $this->Cell(12, 6, $totalSKS > 0 ? (string)$totalSKS : '', 1, 0, 'C');
        $this->Cell(10, 6, '', 1, 0, 'C');
        $this->Cell(10, 6, '', 1, 0, 'C');
        $this->Cell(10, 6, $totalKN > 0 ? (string)round($totalKN) : '', 1, 0, 'C');
        $this->Cell(35, 6, '', 1, 1, 'C');

        $this->Ln(6);

        // IPS, IPK, Beban SKS
        $ips = $totalSKS > 0 ? $totalKN / $totalSKS : 0;

        // Cumulative IPK logic
        $ipk = $mahasiswa->ipk;
        $aktivitas = \App\Models\AktivitasKuliah::where('nim', $mahasiswa->nim)
            ->where('id_semester', '<=', $tahunAkademik->id_semester)
            ->orderBy('id_semester', 'desc')
            ->where('ipk', '>', 0)
            ->first();

        if ($aktivitas && $aktivitas->ipk > 0) {
            $ipk = $aktivitas->ipk;
        } else {
            $nilais = $mahasiswa->nilai()
                ->where('id_periode', '<=', $tahunAkademik->id_semester)
                ->with('mataKuliah')->get();
            $mkGrades = [];
            foreach ($nilais as $n) {
                if (!$n->mata_kuliah_id || $n->nilai_indeks === null) continue;
                if (!isset($mkGrades[$n->mata_kuliah_id]) || $mkGrades[$n->mata_kuliah_id]['indeks'] < $n->nilai_indeks) {
                    $mkGrades[$n->mata_kuliah_id] = [
                        'sks' => $n->mataKuliah->sks_mata_kuliah ?? $n->sks_mata_kuliah ?? 0,
                        'indeks' => $n->nilai_indeks
                    ];
                }
            }
            $totalMKSks = 0;
            $totalBobot = 0;
            foreach ($mkGrades as $grade) {
                $totalMKSks += $grade['sks'];
                $totalBobot += ($grade['sks'] * $grade['indeks']);
            }
            if ($totalMKSks > 0) {
                $ipk = $totalBobot / $totalMKSks;
            }
        }

        // Print Bottom Info Block & Signatures Page Break Check
        if ($this->GetY() + 45 > $this->PageBreakTrigger) {
            $this->AddPage();
            $this->useBackgroundTemplate('khs');
            $this->SetY(60);
        }

        $yCurrent = $this->GetY();
        $kota = Setting::getValue('kota_terbit', 'Tanjungpinang');

        // Row 1: IPS & Date
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(65, 5, 'Indeks Prestasi Semester (IPS)', 0, 0);
        $this->Cell(20, 5, number_format($ips, 2), 0, 0, 'L');
        
        $this->SetXY(120, $yCurrent);
        $this->SetFont('Arial', '', 8);
        $this->Cell(75, 5, $kota . ', ' . $this->formatTanggal(date('Y-m-d')), 0, 1, 'C');

        // Row 2: IPK & Signer Title
        $yCurrent = $this->GetY();
        $this->SetXY(15, $yCurrent);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(65, 5, 'Indeks Prestasi Kumulatif (IPK)', 0, 0);
        $this->Cell(20, 5, number_format($ipk, 2), 0, 0, 'L');

        $this->SetXY(120, $yCurrent);
        $this->SetFont('Arial', '', 8);
        $this->Cell(75, 5, 'Ketua Prodi ' . ucwords(strtolower($mahasiswa->programStudi?->nama_cetak ?? '')), 0, 1, 'C');

        // Row 3: Beban SKS Maks
        $this->SetXY(15, $this->GetY());
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(65, 5, 'Beban SKS Maks Sem. yang akan Datang', 0, 1);

        // Signer name and NIDN
        $this->Ln(15);
        // Find Kaprodi dynamically based on Program Studi
        $prodiName = $mahasiswa->programStudi?->nama_prodi ?? '';
        $prodiAlias = $mahasiswa->programStudi?->nama_alias ?? '';

        $dynamicKaprodi = null;
        if ($mahasiswa->programStudi?->pejabat_id) {
            $dynamicKaprodi = Pejabat::find($mahasiswa->programStudi->pejabat_id);
        }

        if (!$dynamicKaprodi && (!empty($prodiName) || !empty($prodiAlias))) {
            // Create a dynamic search query, prioritizing ones with Head titles
            $dynamicKaprodi = Pejabat::active()
                ->where(function ($q) {
                    $q->where('jabatan', 'like', '%Ketua%')
                      ->orWhere('jabatan', 'like', '%Ka%')
                      ->orWhere('jabatan', 'like', '%Kaprodi%')
                      ->orWhere('jabatan', 'like', '%Koordinator%');
                })
                ->where(function ($q) use ($prodiName, $prodiAlias) {
                    if ($prodiName) $q->orWhere('jabatan', 'like', '%' . $prodiName . '%');
                    if ($prodiAlias) $q->orWhere('jabatan', 'like', '%' . $prodiAlias . '%');
                })->first();

            // Fallback: Just match anything containing the Prodi name (dynamic)
            if (!$dynamicKaprodi) {
                $dynamicKaprodi = Pejabat::active()
                    ->where(function ($q) use ($prodiName, $prodiAlias) {
                        if ($prodiName) $q->orWhere('jabatan', 'like', '%' . $prodiName . '%');
                        if ($prodiAlias) $q->orWhere('jabatan', 'like', '%' . $prodiAlias . '%');
                    })->first();
            }
        }

        $signerId = Setting::getValue('signer_khs');
        // Priority: Custom Signer -> Dynamic Kaprodi by Prodi -> Setting Signer Khs -> Fallback first Kaprodi
        $signer = $customSigner ?? $dynamicKaprodi ?? Pejabat::find($signerId) ?? Pejabat::active()->where(function($q) {
            $q->where('jabatan', 'like', '%Kaprodi%')
              ->orWhere('jabatan', 'like', '%Ketua Prodi%')
              ->orWhere('jabatan', 'like', '%Ka%');
        })->first();

        // Need the prefix logic (like apt LILI SARTIKA S.FARM, APT, S.Farm, M.Farm)
        $signerName = $signer?->nama_lengkap ?? '............................................';
        $signerNik = $signer?->nik ?? $signer?->nip ?? $signer?->nidn ?? '......................';

        $this->SetFont('Arial', '', 8);
        $this->SetX(120);
        $this->Cell(75, 5, $signerName, 0, 1, 'C');
        $this->SetX(120);
        $this->Cell(75, 5, 'NIK. ' . $signerNik, 0, 1, 'C');

        // Final Output
        $timestamp = time();
        $filename = 'khs_' . $mahasiswa->nim . '_' . $tahunAkademik->id_semester . '_' . $timestamp . '.pdf';
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $this->Output('F', $path);

        return $filename;
    }
}
