<?php

namespace App\Services\Sync;

use App\Models\Mahasiswa;
use App\Models\MahasiswaLulusDO;
use App\Models\RiwayatPendidikanMahasiswa;

class StudentSyncService extends BaseSyncService
{
    public function syncMahasiswa(int $offset = 0, int $limit = 2000): array
    {
        $totalAll = 0;
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountMahasiswa();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncMahasiswa: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getMahasiswa($limit, $offset);
        
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                // Determine angkatan from id_periode (e.g. 20231 -> 2023)
                $angkatan = substr((string)$item['id_periode'], 0, 4);

                Mahasiswa::updateOrCreate(
                    ['id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa']],
                    [
                        'id_mahasiswa' => $item['id_mahasiswa'],
                        'nim' => $item['nim'],
                        'nama' => $item['nama_mahasiswa'], 
                        'jenis_kelamin' => $item['jenis_kelamin'],
                        'tanggal_lahir' => $item['tanggal_lahir'],
                        'angkatan' => $angkatan,
                        'id_prodi' => $item['id_prodi'],
                        'status_mahasiswa' => $item['nama_status_mahasiswa'],
                        'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Mhs {$item['nim']}: " . $e->getMessage();
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = $totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit);
        $progress = $totalAll > 0 ? min(100, round($nextOffset / $totalAll * 100)) : 100;

        return [
            'total' => $batchCount,
            'synced' => $synced,
            'errors' => $errors,
            'total_all' => $totalAll,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => $progress,
        ];
    }

    public function syncBiodata(Mahasiswa $mahasiswa): ?string
    {
        try {
            $response = $this->neoFeeder->getBiodataMahasiswa($mahasiswa->id_mahasiswa);
            
            if ($response && isset($response['data']) && count($response['data']) > 0) {
                $bio = $response['data'][0];
                
                // Update basic biodata
                $mahasiswa->tempat_lahir = $bio['tempat_lahir'];
                $mahasiswa->id_agama = $bio['id_agama'];
                $mahasiswa->nama_agama = $bio['nama_agama'];
                $mahasiswa->nik = $bio['nik'];
                $mahasiswa->nisn = $bio['nisn'];
                $mahasiswa->npwp = $bio['npwp'];
                $mahasiswa->kewarganegaraan = $bio['kewarganegaraan'];
                
                // Address
                $mahasiswa->jalan = $bio['jalan'];
                $mahasiswa->dusun = $bio['dusun'];
                $mahasiswa->rt = $bio['rt'];
                $mahasiswa->rw = $bio['rw'];
                $mahasiswa->kelurahan = $bio['kelurahan'];
                $mahasiswa->kode_pos = $bio['kode_pos'];
                $mahasiswa->id_wilayah = $bio['id_wilayah'];
                $mahasiswa->nama_wilayah = $bio['nama_wilayah'];
                $mahasiswa->id_jenis_tinggal = $bio['id_jenis_tinggal'];
                $mahasiswa->nama_jenis_tinggal = $bio['nama_jenis_tinggal'];
                $mahasiswa->id_alat_transportasi = $bio['id_alat_transportasi'];
                $mahasiswa->nama_alat_transportasi = $bio['nama_alat_transportasi'];
                
                // Contact
                $mahasiswa->telepon = $bio['telepon'];
                $mahasiswa->handphone = $bio['handphone'];
                $mahasiswa->email = $bio['email'];
                
                // Parents - Father
                $mahasiswa->nik_ayah = $bio['nik_ayah'];
                $mahasiswa->nama_ayah = $bio['nama_ayah'];
                $mahasiswa->tanggal_lahir_ayah = $bio['tanggal_lahir_ayah'];
                $mahasiswa->id_pendidikan_ayah = $bio['id_pendidikan_ayah'];
                $mahasiswa->nama_pendidikan_ayah = $bio['nama_pendidikan_ayah'];
                $mahasiswa->id_pekerjaan_ayah = $bio['id_pekerjaan_ayah'];
                $mahasiswa->nama_pekerjaan_ayah = $bio['nama_pekerjaan_ayah'];
                $mahasiswa->id_penghasilan_ayah = $bio['id_penghasilan_ayah'];
                $mahasiswa->nama_penghasilan_ayah = $bio['nama_penghasilan_ayah'];
                
                // Parents - Mother
                $mahasiswa->nik_ibu = $bio['nik_ibu'];
                $mahasiswa->nama_ibu = $bio['nama_ibu'];
                $mahasiswa->tanggal_lahir_ibu = $bio['tanggal_lahir_ibu'];
                $mahasiswa->id_pendidikan_ibu = $bio['id_pendidikan_ibu'];
                $mahasiswa->nama_pendidikan_ibu = $bio['nama_pendidikan_ibu'];
                $mahasiswa->id_pekerjaan_ibu = $bio['id_pekerjaan_ibu'];
                $mahasiswa->nama_pekerjaan_ibu = $bio['nama_pekerjaan_ibu'];
                $mahasiswa->id_penghasilan_ibu = $bio['id_penghasilan_ibu'];
                $mahasiswa->nama_penghasilan_ibu = $bio['nama_penghasilan_ibu'];
                
                // Guardian
                $mahasiswa->nama_wali = $bio['nama_wali'];
                $mahasiswa->tanggal_lahir_wali = $bio['tanggal_lahir_wali'];
                $mahasiswa->id_pendidikan_wali = $bio['id_pendidikan_wali'];
                $mahasiswa->nama_pendidikan_wali = $bio['nama_pendidikan_wali'];
                $mahasiswa->id_pekerjaan_wali = $bio['id_pekerjaan_wali'];
                $mahasiswa->nama_pekerjaan_wali = $bio['nama_pekerjaan_wali'];
                $mahasiswa->id_penghasilan_wali = $bio['id_penghasilan_wali'];
                $mahasiswa->nama_penghasilan_wali = $bio['nama_penghasilan_wali'];
                
                // Additional
                $mahasiswa->id_kebutuhan_khusus_mahasiswa = $bio['id_kebutuhan_khusus_mahasiswa'];
                $mahasiswa->nama_kebutuhan_khusus_mahasiswa = $bio['nama_kebutuhan_khusus_mahasiswa'];
                $mahasiswa->id_kebutuhan_khusus_ayah = $bio['id_kebutuhan_khusus_ayah'];
                $mahasiswa->nama_kebutuhan_khusus_ayah = $bio['nama_kebutuhan_khusus_ayah'];
                $mahasiswa->id_kebutuhan_khusus_ibu = $bio['id_kebutuhan_khusus_ibu'];
                $mahasiswa->nama_kebutuhan_khusus_ibu = $bio['nama_kebutuhan_khusus_ibu'];
                
                if ($mahasiswa->isDirty()) {
                    $mahasiswa->save();
                    return 'updated';
                }
                
                return 'skipped';
            }
        } catch (\Exception $e) {
            // Log error silently or rethrow depending on needs
            return null;
        }
        
        return null;
    }

    public function syncMahasiswaLulusDO(int $offset = 0, int $limit = 500): array
    {
        $totalAll = 0;
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountMahasiswaLulusDO();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncLulusDO: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getMahasiswaLulusDO($limit, $offset);
        
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                // Update Mahasiswa Status
                $mahasiswa = Mahasiswa::where('id_registrasi_mahasiswa', $item['id_registrasi_mahasiswa'])->first();
                if ($mahasiswa) {
                    $mahasiswa->id_status_mahasiswa = $item['id_jenis_keluar'];
                    $mahasiswa->nama_status_mahasiswa = $item['nama_jenis_keluar'];
                    $mahasiswa->keterangan_keluar = $item['keterangan_keluar'] ?? null;
                    $mahasiswa->tanggal_keluar = $item['tanggal_keluar'];
                    $mahasiswa->id_periode_keluar = $item['id_periode_keluar'];
                    $mahasiswa->nomor_sk_yudisium = $item['nomor_sk_yudisium'];
                    $mahasiswa->tanggal_sk_yudisium = $item['tanggal_sk_yudisium'];
                    $mahasiswa->ipk = $item['ipk'];
                    $mahasiswa->nomor_ijazah = $item['nomor_ijazah'];
                    $mahasiswa->save();
                }

                // Create History Record
                MahasiswaLulusDO::updateOrCreate(
                    ['id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa']],
                    [
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
                    ]
                );
                
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "LulusDO {$item['nim']}: " . $e->getMessage();
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = $totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit);
        $progress = $totalAll > 0 ? min(100, round($nextOffset / $totalAll * 100)) : 100;

        return [
            'total' => $batchCount,
            'synced' => $synced,
            'errors' => $errors,
            'total_all' => $totalAll,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => $progress,
        ];
    }

    public function syncRiwayatPendidikan(int $offset = 0, int $limit = 500): array
    {
        $totalAll = 0;
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountRiwayatPendidikanMahasiswa();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncRiwayatPendidikan: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getRiwayatPendidikanMahasiswa($limit, $offset);
        
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                RiwayatPendidikanMahasiswa::updateOrCreate(
                    ['id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa']],
                    [
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
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Riwayat {$item['nim']}: " . $e->getMessage();
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = $totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit);
        $progress = $totalAll > 0 ? min(100, round($nextOffset / $totalAll * 100)) : 100;

        return [
            'total' => $batchCount,
            'synced' => $synced,
            'errors' => $errors,
            'total_all' => $totalAll,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => $progress,
        ];
    }
}
