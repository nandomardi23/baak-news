<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pejabat;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LetterSettingsController extends Controller
{
    public function show()
    {
        return Inertia::render('Admin/Settings/Surat', [
            'settings' => [
                // Identitas Instansi
                'kop_nama_yayasan' => Setting::getValue('kop_nama_yayasan', 'YAYASAN NALA'),
                'kop_nama_kampus' => Setting::getValue('kop_nama_kampus', 'SEKOLAH TINGGI ILMU KESEHATAN HANG TUAH TANJUNGPINANG KEPULAUAN RIAU'),
                'kop_alamat' => Setting::getValue('kop_alamat', 'Jl. WR. Supratman, Air Raja, Tanjungpinang Timur, Kota Tanjungpinang, Kepulauan Riau. Tlp (0771) 4440071'),
                'kop_website' => Setting::getValue('kop_website', 'www.stikeshangtuah-tpi.ac.id'),
                'kop_email' => Setting::getValue('kop_email', 'stikestpi@gmail.com'),
                
                // Format Surat
                'kota_terbit' => Setting::getValue('kota_terbit', 'Tanjungpinang'),

                // Default Signers
                'signer_aktif_kuliah' => Setting::getValue('signer_aktif_kuliah'),
                'signer_kartu_ujian' => Setting::getValue('signer_kartu_ujian'),
                'signer_krs' => Setting::getValue('signer_krs'),
                'signer_khs' => Setting::getValue('signer_khs'),
                'signer_transkrip' => Setting::getValue('signer_transkrip'),
            ],
            'pejabat' => Pejabat::active()->orderBy('nama')->get()->map(fn($p) => [
                'id' => $p->id,
                'nama' => $p->nama_lengkap, // Using accessor
                'jabatan' => $p->jabatan,
            ]),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'kop_nama_yayasan' => 'nullable|string|max:255',
            'kop_nama_kampus' => 'required|string|max:255',
            'kop_alamat' => 'required|string|max:500',
            'kop_website' => 'nullable|string|max:255',
            'kop_email' => 'nullable|email|max:255',
            'kota_terbit' => 'required|string|max:100',
            'signer_aktif_kuliah' => 'nullable|exists:pejabat,id',
            'signer_kartu_ujian' => 'nullable|exists:pejabat,id',
            'signer_krs' => 'nullable|exists:pejabat,id',
            'signer_khs' => 'nullable|exists:pejabat,id',
            'signer_transkrip' => 'nullable|exists:pejabat,id',
        ]);

        foreach ($validated as $key => $value) {
            Setting::setValue($key, $value);
        }

        return redirect()->back()->with('success', 'Pengaturan surat berhasil disimpan.');
    }
}
