<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterTemplate extends Model
{
    const TYPE_SURAT = 'surat';
    const TYPE_KRS = 'krs';
    const TYPE_KHS = 'khs';
    const TYPE_TRANSKRIP = 'transkrip';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'canvas_data',
        'file_path',
        'page_size',
        'orientation',
        'width',
        'height',
        'is_active',
    ];

    protected $casts = [
        'canvas_data' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get available placeholders for templates
     */
    public static function getPlaceholders(): array
    {
        return [
            ['key' => '{{nama}}', 'label' => 'Nama Mahasiswa'],
            ['key' => '{{nim}}', 'label' => 'NIM'],
            ['key' => '{{prodi}}', 'label' => 'Program Studi'],
            ['key' => '{{jenjang}}', 'label' => 'Jenjang (S1/D3)'],
            ['key' => '{{semester}}', 'label' => 'Semester'],
            ['key' => '{{angkatan}}', 'label' => 'Angkatan'],
            ['key' => '{{ttl}}', 'label' => 'Tempat, Tanggal Lahir'],
            ['key' => '{{jenis_kelamin}}', 'label' => 'Jenis Kelamin'],
            ['key' => '{{alamat}}', 'label' => 'Alamat Mahasiswa'],
            
            // Data Orang Tua
            ['key' => '{{nama_ayah}}', 'label' => 'Nama Ayah'],
            ['key' => '{{pekerjaan_ayah}}', 'label' => 'Pekerjaan Ayah'],
            ['key' => '{{nama_ibu}}', 'label' => 'Nama Ibu'],
            ['key' => '{{pekerjaan_ibu}}', 'label' => 'Pekerjaan Ibu'],
            ['key' => '{{alamat_ortu}}', 'label' => 'Alamat Orang Tua'],

            ['key' => '{{tanggal}}', 'label' => 'Tanggal Cetak'],
            ['key' => '{{ipk}}', 'label' => 'IPK'],
            ['key' => '{{sks_tempuh}}', 'label' => 'SKS Tempuh'],
            ['key' => '{{nama_pejabat}}', 'label' => 'Nama Pejabat'],
            ['key' => '{{jabatan_pejabat}}', 'label' => 'Jabatan Pejabat'],
            ['key' => '{{nip_pejabat}}', 'label' => 'NIP Pejabat'],
            ['key' => '{{nomor_surat}}', 'label' => 'Nomor Surat'],
        ];
    }

    /**
     * Get page dimensions based on size and orientation
     */
    public static function getPageDimensions(string $size = 'A4', string $orientation = 'portrait'): array
    {
        $sizes = [
            'A4' => ['width' => 794, 'height' => 1123],
            'Letter' => ['width' => 816, 'height' => 1056],
            'Legal' => ['width' => 816, 'height' => 1344],
        ];

        $dim = $sizes[$size] ?? $sizes['A4'];

        if ($orientation === 'landscape') {
            return ['width' => $dim['height'], 'height' => $dim['width']];
        }

        return $dim;
    }
}

