<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuratPengajuanRequest;
use App\Models\Mahasiswa;
use App\Models\SuratPengajuan;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentPengajuanController extends Controller
{
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
                'status' => $mahasiswa->status_text,
                // Parent data
                'nama_ayah' => $mahasiswa->nama_ayah,
                'pekerjaan_ayah' => $mahasiswa->pekerjaan_ayah,
                'nama_ibu' => $mahasiswa->nama_ibu,
                'pekerjaan_ibu' => $mahasiswa->pekerjaan_ibu,
                'alamat_ortu' => $mahasiswa->alamat_ortu,
                'rt_ortu' => $mahasiswa->rt_ortu,
                'rw_ortu' => $mahasiswa->rw_ortu,
                'kelurahan_ortu' => $mahasiswa->kecamatan_ortu,
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

        // Hanya mahasiswa berstatus 'Aktif' yang boleh mengajukan surat aktif kuliah
        if ($validated['jenis_surat'] === 'aktif_kuliah' && strtolower($mahasiswa->status_text) !== 'aktif') {
            return back()->withErrors(['jenis_surat' => "Mahasiswa berstatus {$mahasiswa->status_text} tidak dapat mengajukan Surat Keterangan Aktif Kuliah. Status harus Aktif."]);
        }

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
}
