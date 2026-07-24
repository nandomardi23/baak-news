<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuratPengajuanRequest;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\SuratPengajuan;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentPortalController extends Controller
{
    public function showVerify(Mahasiswa $mahasiswa): Response
    {
        return Inertia::render('Landing/VerifyIdentity', [
            'mahasiswa_id' => $mahasiswa->id,
        ]);
    }

    public function processVerify(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $request->validate([
            'nim' => 'required|string',
            'tanggal_lahir' => 'required|date',
        ]);

        if (
            $mahasiswa->nim === $request->nim &&
            $mahasiswa->tanggal_lahir &&
            $mahasiswa->tanggal_lahir->format('Y-m-d') === $request->tanggal_lahir
        ) {
            $request->session()->put('verified_mahasiswa_id', $mahasiswa->id);
            $request->session()->put('verified_mahasiswa_at', now());
            return redirect()->route('landing.dokumen', $mahasiswa->id);
        }

        return back()->withErrors([
            'identity' => 'NIM atau tanggal lahir tidak sesuai.',
        ]);
    }

    public function dokumen(Mahasiswa $mahasiswa): Response
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
        $existingPending = SuratPengajuan::where('mahasiswa_id', $mahasiswa->id)
            ->pending()
            ->exists();

        // Get recent pengajuan
        $recentPengajuan = SuratPengajuan::where('mahasiswa_id', $mahasiswa->id)
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

        return Inertia::render('Landing/Dokumen', [
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
            'dosens' => Dosen::active()->orderBy('nama')->get()->map(fn($d) => [
                'id' => (string) $d->id,
                'nama' => $d->nama_lengkap
            ]),
        ]);
    }

    public function form(Mahasiswa $mahasiswa): Response
    {
        $mahasiswa->load('programStudi');

        // Check for existing pending request for any type
        $existingPending = SuratPengajuan::where('mahasiswa_id', $mahasiswa->id)
            ->pending()
            ->exists();

        // Get available semesters for KRS/KHS
        $semesters = TahunAkademik::orderBy('id_semester', 'desc')
            ->take(6)
            ->get()
            ->map(fn($ta) => [
                'id' => $ta->id,
                'nama' => $ta->nama_semester,
            ]);

        return Inertia::render('Landing/FormPengajuan', [
            'mahasiswa' => [
                'id' => $mahasiswa->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'tempat_lahir' => $mahasiswa->tempat_lahir,
                'tanggal_lahir' => $mahasiswa->tanggal_lahir?->format('Y-m-d'),
                'alamat' => $mahasiswa->alamat,
                'rt' => $mahasiswa->rt,
                'rw' => $mahasiswa->rw,
                'kelurahan' => $mahasiswa->kelurahan,
                'kecamatan' => $mahasiswa->kecamatan,
                'kota_kabupaten' => $mahasiswa->kota_kabupaten,
                'provinsi' => $mahasiswa->provinsi,
                'no_hp' => $mahasiswa->no_hp,
                'prodi' => $mahasiswa->programStudi?->nama_prodi,
                'jenis_program' => $mahasiswa->programStudi?->jenis_program ?? 'reguler',
                'angkatan' => $mahasiswa->angkatan,
                // Parent data
                'nama_ayah' => $mahasiswa->nama_ayah,
                'pekerjaan_ayah' => $mahasiswa->pekerjaan_ayah,
                'nama_ibu' => $mahasiswa->nama_ibu,
                'pekerjaan_ibu' => $mahasiswa->pekerjaan_ibu,
                'alamat_ortu' => $mahasiswa->alamat_ortu,
                'rt_ortu' => $mahasiswa->rt_ortu,
                'rw_ortu' => $mahasiswa->rw_ortu,
                'kelurahan_ortu' => $mahasiswa->kelurahan_ortu,
                'kecamatan_ortu' => $mahasiswa->kecamatan_ortu,
                'kota_kabupaten_ortu' => $mahasiswa->kota_kabupaten_ortu,
                'provinsi_ortu' => $mahasiswa->provinsi_ortu,
            ],
            'existingPending' => $existingPending,
            'semesters' => $semesters,
        ]);
    }

    public function submit(StoreSuratPengajuanRequest $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $validated = $request->validated();

        // Update mahasiswa data if provided
        $mahasiswaData = collect($validated)
            ->only([
                'nama', 'tempat_lahir', 'tanggal_lahir', 
                'alamat', 'rt', 'rw', 'kelurahan', 'kecamatan', 'kota_kabupaten', 'provinsi', 'no_hp', 
                'nama_ayah', 'pekerjaan_ayah', 'nama_ibu', 'pekerjaan_ibu', 
                'alamat_ortu', 'rt_ortu', 'rw_ortu', 'kelurahan_ortu', 'kecamatan_ortu', 'kota_kabupaten_ortu', 'provinsi_ortu'
            ])
            ->filter()
            ->toArray();
        
        if (!empty($mahasiswaData)) {
            // Enforce Title Case for name if present
            if (isset($mahasiswaData['nama'])) {
                $mahasiswaData['nama'] = \Illuminate\Support\Str::title(strtolower($mahasiswaData['nama']));
            }
            
            $mahasiswa->update($mahasiswaData);
        }

        // Build data_tambahan based on jenis_surat
        $dataTambahan = [];
        switch ($validated['jenis_surat']) {
            case 'aktif_kuliah':
                $dataTambahan['keperluan'] = $validated['keperluan'];
                break;
            case 'krs':
            case 'khs':
                $dataTambahan['tahun_akademik_id'] = $validated['tahun_akademik_id'];
                break;
            case 'transkrip':
                $dataTambahan['jenis'] = $validated['jenis_transkrip'] ?? 'reguler';
                break;
        }

        // Create surat pengajuan
        SuratPengajuan::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => $validated['jenis_surat'],
            'keperluan' => $validated['keperluan'] ?? null,
            'data_tambahan' => $dataTambahan,
            'status' => 'pending',
        ]);

        return redirect()->route('landing.status', ['mahasiswa' => $mahasiswa->id])
            ->with('success', 'Pengajuan surat berhasil dikirim');
    }

    public function status(Mahasiswa $mahasiswa): Response
    {
        $pengajuan = SuratPengajuan::where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'jenis_surat' => $item->jenis_surat_label,
                'keperluan' => $item->keperluan,
                'status' => $item->status,
                'status_label' => $item->status_label,
                'status_badge' => $item->status_badge,
                'catatan' => $item->catatan,
                'created_at' => $item->created_at->format('d M Y H:i'),
                'processed_at' => $item->processed_at?->format('d M Y H:i'),
            ]);

        return Inertia::render('Landing/Status', [
            'mahasiswa' => [
                'id' => $mahasiswa->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
            ],
            'pengajuan' => $pengajuan,
        ]);
    }

    public function updateDosenWali(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $request->validate([
            'dosen_wali_id' => 'required|exists:dosen,id',
        ]);

        $mahasiswa->update([
            'dosen_wali_id' => $request->dosen_wali_id,
        ]);

        return redirect()->back()->with('success', 'Dosen Pembimbing Akademik berhasil disimpan.');
    }

    public function yudisium(Mahasiswa $mahasiswa): Response
    {
        $requirements = \App\Models\YudisiumRequirement::active()->get();
        $checklists = $mahasiswa->yudisiumChecklists()->get()->keyBy('yudisium_requirement_id');

        $data = $requirements->map(function ($req) use ($checklists) {
            $checklist = $checklists->get($req->id);
            return [
                'id' => $req->id,
                'nama_syarat' => $req->nama_syarat,
                'deskripsi' => $req->deskripsi,
                'is_upload_required' => $req->is_upload_required,
                'status' => $checklist ? $checklist->status : 'pending',
                'status_label' => $checklist ? $checklist->status_label : 'Belum Ada',
                'status_badge' => $checklist ? $checklist->status_badge : 'pending',
                'catatan' => $checklist ? $checklist->catatan : null,
                'file_url' => $checklist && $checklist->file_path ? asset('storage/' . $checklist->file_path) : null,
            ];
        });

        // Determine general status
        // - approved if all required are approved
        // - pending if any is pending
        // - rejected if any is rejected
        $allApproved = $data->every(fn($item) => $item['status'] === 'approved');
        $anyRejected = $data->contains(fn($item) => $item['status'] === 'rejected');
        $overallStatus = $allApproved ? 'Memenuhi Syarat' : ($anyRejected ? 'Ada Syarat Ditolak' : 'Belum Memenuhi Syarat');

        return Inertia::render('Landing/Yudisium', [
            'mahasiswa' => [
                'id' => $mahasiswa->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
            ],
            'requirements' => $data,
            'overallStatus' => $overallStatus,
        ]);
    }

    public function submitYudisium(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $request->validate([
            'requirement_id' => 'required|exists:yudisium_requirements,id',
            'file' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
        ]);

        $requirement = \App\Models\YudisiumRequirement::findOrFail($request->requirement_id);
        if ($requirement->is_upload_required && !$request->hasFile('file')) {
            return back()->withErrors(['file' => 'File wajib diunggah untuk syarat ini.']);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('yudisium', 'public');
        }

        $checklist = $mahasiswa->yudisiumChecklists()->updateOrCreate(
            ['yudisium_requirement_id' => $requirement->id],
            [
                'file_path' => $filePath,
                'status' => 'pending', // resets to pending if re-uploaded
                'catatan' => null,     // clear old notes
            ]
        );

        return back()->with('success', 'Persyaratan yudisium berhasil diunggah dan sedang menunggu validasi.');
    }
}
