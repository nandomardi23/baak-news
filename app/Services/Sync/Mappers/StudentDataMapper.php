<?php

namespace App\Services\Sync\Mappers;

class StudentDataMapper
{
    public static function mapMahasiswa(array $data): array
    {
        $records = [];
        $prodiMap = \App\Models\ProgramStudi::pluck('id', 'id_prodi')->toArray();
        
        foreach ($data as $item) {
            $angkatan = substr((string) $item['id_periode'], 0, 4);

            $tanggalLahir = null;
            if (!empty($item['tanggal_lahir'])) {
                try {
                    $tanggalLahir = \Carbon\Carbon::createFromFormat('d-m-Y', $item['tanggal_lahir'])->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $tanggalLahir = \Carbon\Carbon::parse($item['tanggal_lahir'])->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $tanggalLahir = null;
                    }
                }
            }

            $nim = $item['nim'];
            if (empty($nim)) {
                \Illuminate\Support\Facades\Log::warning("SyncMahasiswa: Missing NIM for student {$item['nama_mahasiswa']} (ID: {$item['id_mahasiswa']}). Using ID as NIM.");
                $nim = $item['id_mahasiswa'];
            }

            $records[] = [
                'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                'id_mahasiswa' => $item['id_mahasiswa'],
                'nim' => $nim,
                'nama' => $item['nama_mahasiswa'],
                'jenis_kelamin' => $item['jenis_kelamin'],
                'tanggal_lahir' => $tanggalLahir,
                'angkatan' => $angkatan,
                'id_prodi' => $item['id_prodi'],
                'program_studi_id' => $prodiMap[$item['id_prodi']] ?? null,
                'status_mahasiswa' => $item['nama_status_mahasiswa'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $uniqueRecords = [];
        foreach ($records as $record) {
            $uniqueRecords[$record['nim']] = $record;
        }
        return array_values($uniqueRecords);
    }

    public static function mapMahasiswaLulusDO(array $data): array
    {
        $records = [];
        foreach ($data as $item) {
            $records[] = [
                'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                'id_mahasiswa' => $item['id_mahasiswa'],
                'nim' => $item['nim'],
                'nama_mahasiswa' => $item['nama_mahasiswa'],
                'id_jenis_keluar' => $item['id_jenis_keluar'],
                'nama_jenis_keluar' => $item['nama_jenis_keluar'],
                'tanggal_keluar' => $item['tanggal_keluar'],
                'id_periode_keluar' => $item['id_periode_keluar'],
                'keterangan_keluar' => $item['keterangan_keluar'] ?? null,
                'nomor_sk_yudisium' => $item['nomor_sk_yudisium'],
                'tanggal_sk_yudisium' => $item['tanggal_sk_yudisium'],
                'ipk' => $item['ipk'],
                'nomor_ijazah' => $item['nomor_ijazah'],
                'jalur_skripsi' => $item['jalur_skripsi'] ?? 0,
                'judul_skripsi' => $item['judul_skripsi'] ?? null,
                'bulan_awal_bimbingan' => $item['bulan_awal_bimbingan'] ?? null,
                'bulan_akhir_bimbingan' => $item['bulan_akhir_bimbingan'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        return $records;
    }

    public static function mapRiwayatPendidikan(array $data): array
    {
        $records = [];
        foreach ($data as $item) {
            $records[] = [
                'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                'id_mahasiswa' => $item['id_mahasiswa'],
                'nim' => $item['nim'],
                'nama_mahasiswa' => $item['nama_mahasiswa'],
                'id_jenis_daftar' => $item['id_jenis_daftar'],
                'nama_jenis_daftar' => $item['nama_jenis_daftar'],
                'id_jalur_daftar' => $item['id_jalur_daftar'] ?? null,
                'nama_jalur_daftar' => $item['nama_jalur_daftar'] ?? null,
                'id_periode_masuk' => $item['id_periode_masuk'],
                'tanggal_daftar' => $item['tanggal_daftar'],
                'id_perguruan_tinggi_asal' => $item['id_perguruan_tinggi_asal'] ?? null,
                'nama_perguruan_tinggi_asal' => $item['nama_perguruan_tinggi_asal'] ?? null,
                'id_prodi_asal' => $item['id_prodi_asal'] ?? null,
                'nama_prodi_asal' => $item['nama_prodi_asal'] ?? null,
                'sks_diakui' => $item['sks_diakui'] ?? 0,
                'biaya_masuk' => $item['biaya_masuk'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        return $records;
    }
}
