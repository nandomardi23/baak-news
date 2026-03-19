<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratPengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public form, no auth needed
    }

    public function rules(): array
    {
        return [
            'jenis_surat' => 'required|in:aktif_kuliah,krs,khs,transkrip',
            'keperluan' => 'required_if:jenis_surat,aktif_kuliah|nullable|string|max:255',
            'tahun_akademik_id' => 'required_if:jenis_surat,krs,khs|nullable|exists:tahun_akademik,id',
            'jenis_transkrip' => 'required_if:jenis_surat,transkrip|nullable|in:reguler,rpl',
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string|max:500',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kota_kabupaten' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            // Parent data
            'nama_ayah' => 'nullable|string|max:100',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'nama_ibu' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'alamat_ortu' => 'nullable|string|max:500',
            'rt_ortu' => 'nullable|string|max:10',
            'rw_ortu' => 'nullable|string|max:10',
            'kelurahan_ortu' => 'nullable|string|max:100',
            'kecamatan_ortu' => 'nullable|string|max:100',
            'kota_kabupaten_ortu' => 'nullable|string|max:100',
            'provinsi_ortu' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_surat.required' => 'Jenis surat harus dipilih.',
            'jenis_surat.in' => 'Jenis surat tidak valid.',
            'keperluan.required_if' => 'Keperluan harus diisi untuk surat aktif kuliah.',
            'tahun_akademik_id.required_if' => 'Tahun akademik harus dipilih untuk KRS/KHS.',
            'nama.required' => 'Nama harus diisi.',
        ];
    }
}
