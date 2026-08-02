<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Traits\GeneratesPdf;
use Illuminate\Http\Request;

class StudentDocumentController extends Controller
{
    use GeneratesPdf;

    public function dokumen(Mahasiswa $mahasiswa): \Inertia\Response
    {
        $mahasiswa->load(['programStudi', 'krs.tahunAkademik', 'nilai.tahunAkademik', 'nilai.mataKuliah', 'dosenWali']);

        // Calculate actual SKS and IPK from grades
        $totalSks = 0;
        $totalBobot = 0;
        foreach ($mahasiswa->nilai as $nilai) {
            if ($mk = $nilai->mataKuliah) {
                if ($nilai->nilai_huruf) {
                    $bobot = $mk->sks_mata_kuliah * ($nilai->nilai_indeks ?? 0);
                    $totalSks += $mk->sks_mata_kuliah;
                    $totalBobot += $bobot;
                }
            }
        }
        
        $calculatedIpk = $totalSks > 0 ? $totalBobot / $totalSks : 0;
        $ipk = (float)($mahasiswa->ipk ?? 0) > 0 ? (float)$mahasiswa->ipk : $calculatedIpk;
        $sks = ($mahasiswa->sks_tempuh ?? 0) > 0 ? $mahasiswa->sks_tempuh : $totalSks;

        // Get all semesters where student has KRS or Nilai
        $krsSemesters = $mahasiswa->krs->pluck('tahunAkademik')->filter()->unique('id');
        $nilaiSemesters = $mahasiswa->nilai->pluck('tahunAkademik')->filter()->unique('id');
        
        $allSemesters = $krsSemesters->merge($nilaiSemesters)
            ->unique('id')
            ->sortByDesc('id_semester')
            ->values()
            ->map(fn($ta) => [
                'id' => $ta->id,
                'nama' => $ta->nama_semester,
                'has_krs' => $krsSemesters->contains('id', $ta->id),
                'has_nilai' => $nilaiSemesters->contains('id', $ta->id),
            ]);

        // Check for existing pending request
        $existingPending = \App\Models\SuratPengajuan::where('mahasiswa_id', $mahasiswa->id)
            ->pending()
            ->exists();

        // Get recent pengajuan
        $recentPengajuan = \App\Models\SuratPengajuan::where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'jenis_surat' => $item->jenis_surat_label,
                'status' => $item->status,
                'status_label' => $item->status_label,
                'status_badge' => $item->status_badge,
                'created_at' => $item->created_at->format('d M Y'),
            ]);

        return \Inertia\Inertia::render('Landing/Dokumen', [
            'mahasiswa' => [
                'id' => $mahasiswa->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'prodi' => $mahasiswa->programStudi?->nama_prodi,
                'angkatan' => $mahasiswa->angkatan,
                'ipk' => number_format($ipk, 2),
                'sks_tempuh' => $sks,
                'dosen_wali_id' => $mahasiswa->dosen_wali_id ? (string) $mahasiswa->dosen_wali_id : null,
                'dosen_wali_nama' => $mahasiswa->dosenWali?->nama_lengkap ?? null,
            ],
            'semesters' => $allSemesters,
            'existingPending' => $existingPending,
            'recentPengajuan' => $recentPengajuan,
            'dosens' => \App\Models\Dosen::active()->orderBy('nama')->get()->map(fn($d) => [
                'id' => (string) $d->id,
                'nama' => $d->nama_lengkap
            ]),
        ]);
    }

    public function updateDosenWali(Request $request, Mahasiswa $mahasiswa): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'dosen_wali_id' => 'required|exists:dosen,id',
        ]);

        $mahasiswa->update([
            'dosen_wali_id' => $request->dosen_wali_id,
        ]);

        return redirect()->back()->with('success', 'Dosen Pembimbing Akademik berhasil disimpan.');
    }

    public function printKrs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->pdfInlineResponse('krs', $mahasiswa, $tahunAkademik);
    }

    public function printKhs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->pdfInlineResponse('khs', $mahasiswa, $tahunAkademik);
    }

    public function printTranskrip(Mahasiswa $mahasiswa, string $jenis = 'reguler')
    {
        return $this->pdfInlineResponse('transkrip', $mahasiswa, null, $jenis);
    }

    public function printKartuUjian(Request $request, Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->pdfInlineResponse('kartu_ujian', $mahasiswa, $tahunAkademik, $request->input('jenis', 'uts'));
    }
}
