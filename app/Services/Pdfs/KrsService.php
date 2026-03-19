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
        // Set standard margins for a symmetrical 180mm content area on A4 (210mm)
        $this->SetLeftMargin(15);
        $this->SetRightMargin(15);
        $this->SetAutoPageBreak(true, 15);

        // Find background template
        $this->useBackgroundTemplate('krs');

        // Move content higher to match Siakad reference
        $this->SetY(32);

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
        $w1 = 48; // Left label width
        $sep = 3; // Separator width
        $w2 = 52; // Left value width
        $w3 = 18; // Right label width
        $w4 = 56; // Right value width

        // Row 1: NAMA MAHASISWA & JURUSAN
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($w1, 5, 'NAMA MAHASISWA', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->writeAdaptiveCell($w2, 5, strtoupper($mahasiswa->nama), 0, 0);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell($w3, 5, 'JURUSAN', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->writeAdaptiveCell($w4, 5, strtoupper($mahasiswa->programStudi?->nama_cetak ?? '-'), 0, 1);

        // Row 2: N I M & SEMESTER
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($w1, 5, 'N I M', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell($w2, 5, $mahasiswa->nim, 0, 0);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell($w3, 5, 'SEMESTER', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $semesterNum = $this->getMahasiswaSemester($mahasiswa, $tahunAkademik);
        $this->Cell($w4, 5, $semesterNum, 0, 1);

        // Row 3: SKS & IPK
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($w1, 5, 'JUMLAH SKS YANG DIPEROLEH', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell($w2, 5, $totalSks, 0, 0);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell($w3, 5, 'IPK', 0, 0);
        $this->Cell($sep, 5, ':', 0, 0);
        $this->SetFont('Arial', '', 9);

        // IPK logic remains the same
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
                if (!$n->mata_kuliah_id || $n->nilai_indeks === null)
                    continue;
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

        $this->SetFont('Arial', '', 9);
        $this->Cell($w4, 5, number_format((float) ($ipk ?? 0), 2), 0, 1);

        $this->Ln(2);

        $this->SetFont('Arial', 'B', 12);
        $titleWidth = 180;
        $this->SetX(15);
        $this->Cell($titleWidth, 6, 'KARTU RENCANA STUDI', 1, 1, 'C');
        $this->SetX(15);
        $this->SetFont('Arial', '', 10);
        $this->Cell($titleWidth, 5, strtoupper($tahunAkademik->nama_semester), 'LRB', 1, 'C');
        
        $this->Ln(4);
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 5, 'Mata Kuliah yang ditempuh, antara lain :', 0, 1);

        // Table Header
        $this->SetFont('Arial', 'B', 8);
        $cols = [
            'no' => 7,
            'kode' => 17,
            'mk' => 65,
            'sks' => 8,
            'kelas' => 12,
            'dosen' => 71
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

        // Load relations if not already loaded (avoid n+1)
        if ($krs instanceof \Illuminate\Database\Eloquent\Model) {
            $krs->loadMissing(['details.kelasKuliah.dosenPengajar']);
        }

        if ($krs && $krs->details->count() > 0) {
            // Sort details by kode_matkul
            $sortedDetails = $krs->details->sortBy(function ($detail) {
                return $detail->mataKuliah->kode_matkul ?? '';
            });

            foreach ($sortedDetails as $detail) {
                $mk = $detail->mataKuliah;
                $sks = $mk->sks_mata_kuliah ?? 0;

                // Dosen Information: Prioritize list from KelasKuliah instead of simple nama_dosen
                $dosenList = [];
                if ($detail->kelasKuliah && $detail->kelasKuliah->dosenPengajar->count() > 0) {
                    foreach ($detail->kelasKuliah->dosenPengajar as $dp) {
                        $dosenName = $dp->nama_lengkap ?? $dp->nama;
                        if ($dosenName) {
                            $dosenList[] = $dosenName;
                        }
                    }
                }

                $dosenStr = !empty($dosenList) ? implode("\n", $dosenList) : ($detail->nama_dosen ?? $detail->dosen?->nama_lengkap ?? $detail->dosen?->nama ?? '-');

                $row = [
                    ['text' => $no++ . '.', 'width' => $cols['no'], 'align' => 'C'],
                    ['text' => $mk->kode_matkul ?? '-', 'width' => $cols['kode'], 'align' => 'C'],
                    ['text' => $mk->nama_matkul ?? '-', 'width' => $cols['mk'], 'align' => 'L'],
                    ['text' => (string) $sks, 'width' => $cols['sks'], 'align' => 'C'],
                    ['text' => $detail->nama_kelas ?? $detail->kelasKuliah?->nama_kelas_kuliah ?? '-', 'width' => $cols['kelas'], 'align' => 'C'],
                    ['text' => $dosenStr, 'width' => $cols['dosen'], 'align' => 'L'],
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
        $this->SetXY(15, $ySign + 5);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(70, 5, 'MAHASISWA,', 0, 1, 'C');
        $this->Ln(15);
        $this->SetFont('Arial', 'BU', 9);
        $this->SetX(15);
        $this->Cell(70, 5, strtoupper($mahasiswa->nama), 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell(70, 5, $mahasiswa->nim, 0, 0, 'C');

        // Right Side: Pembimbing Akademik
        $kota = Setting::getValue('kota_terbit', 'Tanjungpinang');
        $this->SetXY(125, $ySign);
        $this->SetFont('Arial', '', 9);
        $this->Cell(70, 5, $kota . ', ' . $this->formatTanggal(date('Y-m-d')), 0, 1, 'C');
        $this->SetX(125);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(70, 5, 'PEMBIMBING AKADEMIK,', 0, 1, 'C');
        $this->Ln(15);

        // Attempt to find PA if possible, otherwise placeholder
        $signer = $mahasiswa->dosenWali;
        $isDosen = true; // Flag to handle property differences

        if (!$signer) {
            $isDosen = false;
            $signerId = Setting::getValue('signer_krs');
            $signer = $customSigner;

            if (!$signer && $signerId) {
                $signer = Pejabat::find($signerId);
            }
        }

        // Only display if signer is found, otherwise dots
        if ($signer) {
            $namaSigner = $isDosen ? ($signer->nama_lengkap ?? $signer->nama) : $signer->nama_lengkap;

            $this->SetFont('Arial', 'BU', 9);
            $this->SetX(125);
            $this->Cell(70, 5, strtoupper($namaSigner), 0, 1, 'C');

            $this->SetFont('Arial', '', 9);
            $this->SetX(125);
            $this->Cell(70, 5, 'NIK: ' . ($signer->nik ?? $signer->nip ?? $signer->nidn ?? '-'), 0, 1, 'C');
        } else {
            $this->SetFont('Arial', 'B', 9);
            $this->SetX(125);
            $this->Cell(70, 5, '............................................', 0, 1, 'C');
        }

        // Final Output - Add timestamp to avoid browser caching old versions
        $timestamp = time();
        $filename = 'krs_' . $mahasiswa->nim . '_' . $tahunAkademik->id_semester . '_' . $timestamp . '.pdf';
        $path = storage_path('app/public/surat/' . $filename);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $this->Output('F', $path);

        return $filename;
    }
}
