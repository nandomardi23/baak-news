<?php

namespace App\Services\Sync;

use App\Models\Mahasiswa;
use App\Models\MahasiswaLulusDO;
use App\Models\RiwayatPendidikanMahasiswa;

class StudentSyncService extends BaseSyncService
{
    public function syncMahasiswa(int $offset = 0, int $limit = 500, ?string $syncSince = null): array
    {
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountMahasiswa();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("SyncMahasiswa: GetCount failed. Error: " . $e->getMessage());
        }

        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getMahasiswa($limit, $offset, $filter);

        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        if (!empty($data)) {
            $records = [];
            $prodiMap = \App\Models\ProgramStudi::pluck('id', 'id_prodi')->toArray();
            foreach ($data as $item) {
                $angkatan = substr((string) $item['id_periode'], 0, 4);

                // Parse date - NeoFeeder returns dd-mm-yyyy format, MySQL needs yyyy-mm-dd
                $tanggalLahir = null;
                if (!empty($item['tanggal_lahir'])) {
                    try {
                        $tanggalLahir = \Carbon\Carbon::createFromFormat('d-m-Y', $item['tanggal_lahir'])->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Try other common formats
                        try {
                            $tanggalLahir = \Carbon\Carbon::parse($item['tanggal_lahir'])->format('Y-m-d');
                        } catch (\Exception $e2) {
                            $tanggalLahir = null;
                        }
                    }
                }

                $nim = $item['nim'];
                if (empty($nim)) {
                    // Fallback for missing NIM to prevent crash
                    // Use id_mahasiswa as temporary NIM or skip
                    // Log warning
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

            $this->batchUpsert(Mahasiswa::class, $records, ['id_registrasi_mahasiswa'], [
                'id_mahasiswa',
                'nim',
                'nama',
                'jenis_kelamin',
                'tanggal_lahir',
                'angkatan',
                'id_prodi',
                'program_studi_id',
                'status_mahasiswa',
                'updated_at'
            ]);
            $synced = count($records);
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = ($totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit)) && ($batchCount > 0);
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

    public function syncBiodata(int $offset = 0, int $limit = 500, ?string $syncSince = null): array
    {
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountBiodataMahasiswa();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("SyncBiodata: GetCount failed. Error: " . $e->getMessage());
        }

        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getBiodataMahasiswa(null, $limit, $offset, $filter);

        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        foreach ($data as $bio) {
            try {
                // Find student by ID Mahasiswa
                $mahasiswa = Mahasiswa::where('id_mahasiswa', $bio['id_mahasiswa'])->first();

                if (!$mahasiswa && isset($bio['nim'])) {
                    // Start looking by NIM if ID not found (fallback)
                    $mahasiswa = Mahasiswa::where('nim', $bio['nim'])->first();
                }

                if ($mahasiswa) {
                    $mahasiswa->tempat_lahir = $bio['tempat_lahir'];
                    $mahasiswa->id_agama = $bio['id_agama'];
                    $mahasiswa->nama_agama = $bio['nama_agama'];
                    $mahasiswa->nik = $bio['nik'];
                    $mahasiswa->nisn = $bio['nisn'];
                    $mahasiswa->npwp = $bio['npwp'];
                    $mahasiswa->kewarganegaraan = $bio['kewarganegaraan'];
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
                    $mahasiswa->telepon = $bio['telepon'];
                    $mahasiswa->handphone = $bio['handphone'];
                    $mahasiswa->email = $bio['email'];
                    $mahasiswa->nik_ayah = $bio['nik_ayah'];
                    $mahasiswa->nama_ayah = $bio['nama_ayah'];
                    $mahasiswa->tanggal_lahir_ayah = $this->parseDate($bio['tanggal_lahir_ayah']);
                    $mahasiswa->id_pendidikan_ayah = $bio['id_pendidikan_ayah'];
                    $mahasiswa->nama_pendidikan_ayah = $bio['nama_pendidikan_ayah'];
                    $mahasiswa->id_pekerjaan_ayah = $bio['id_pekerjaan_ayah'];
                    $mahasiswa->nama_pekerjaan_ayah = $bio['nama_pekerjaan_ayah'];
                    $mahasiswa->id_penghasilan_ayah = $bio['id_penghasilan_ayah'];
                    $mahasiswa->nama_penghasilan_ayah = $bio['nama_penghasilan_ayah'];
                    $mahasiswa->nik_ibu = $bio['nik_ibu'];
                    $mahasiswa->nama_ibu = $bio['nama_ibu_kandung'];
                    $mahasiswa->tanggal_lahir_ibu = $this->parseDate($bio['tanggal_lahir_ibu']);
                    $mahasiswa->id_pendidikan_ibu = $bio['id_pendidikan_ibu'];
                    $mahasiswa->nama_pendidikan_ibu = $bio['nama_pendidikan_ibu'];
                    $mahasiswa->id_pekerjaan_ibu = $bio['id_pekerjaan_ibu'];
                    $mahasiswa->nama_pekerjaan_ibu = $bio['nama_pekerjaan_ibu'];
                    $mahasiswa->id_penghasilan_ibu = $bio['id_penghasilan_ibu'];
                    $mahasiswa->nama_penghasilan_ibu = $bio['nama_penghasilan_ibu'];
                    $mahasiswa->nama_wali = $bio['nama_wali'];
                    $mahasiswa->tanggal_lahir_wali = $this->parseDate($bio['tanggal_lahir_wali']);
                    $mahasiswa->id_pendidikan_wali = $bio['id_pendidikan_wali'];
                    $mahasiswa->nama_pendidikan_wali = $bio['nama_pendidikan_wali'];
                    $mahasiswa->id_pekerjaan_wali = $bio['id_pekerjaan_wali'];
                    $mahasiswa->nama_pekerjaan_wali = $bio['nama_pekerjaan_wali'];
                    $mahasiswa->id_penghasilan_wali = $bio['id_penghasilan_wali'];
                    $mahasiswa->nama_penghasilan_wali = $bio['nama_penghasilan_wali'];
                    $mahasiswa->id_kebutuhan_khusus_mahasiswa = $bio['id_kebutuhan_khusus_mahasiswa'];
                    $mahasiswa->nama_kebutuhan_khusus_mahasiswa = $bio['nama_kebutuhan_khusus_mahasiswa'];
                    $mahasiswa->id_kebutuhan_khusus_ayah = $bio['id_kebutuhan_khusus_ayah'];
                    $mahasiswa->nama_kebutuhan_khusus_ayah = $bio['nama_kebutuhan_khusus_ayah'];
                    $mahasiswa->id_kebutuhan_khusus_ibu = $bio['id_kebutuhan_khusus_ibu'];
                    $mahasiswa->nama_kebutuhan_khusus_ibu = $bio['nama_kebutuhan_khusus_ibu'];

                    $mahasiswa->save();
                    $synced++;
                }
            } catch (\Exception $e) {
                $errors[] = "Biodata {$bio['nama_mahasiswa']}: " . $e->getMessage();
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = ($totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit)) && ($batchCount > 0);
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

    public function syncMahasiswaLulusDO(int $offset = 0, int $limit = 500, ?string $syncSince = null): array
    {
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountMahasiswaLulusDO();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("SyncLulusDO: GetCount failed. Error: " . $e->getMessage());
        }

        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getMahasiswaLulusDO($limit, $offset, $filter);
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        if (!empty($data)) {
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

            $this->batchUpsert(MahasiswaLulusDO::class, $records, ['id_registrasi_mahasiswa'], [
                'id_mahasiswa',
                'nim',
                'nama_mahasiswa',
                'id_jenis_keluar',
                'nama_jenis_keluar',
                'tanggal_keluar',
                'id_periode_keluar',
                'keterangan_keluar',
                'nomor_sk_yudisium',
                'tanggal_sk_yudisium',
                'ipk',
                'nomor_ijazah',
                'jalur_skripsi',
                'judul_skripsi',
                'bulan_awal_bimbingan',
                'bulan_akhir_bimbingan',
                'updated_at'
            ]);
            $synced = count($records);
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = ($totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit)) && ($batchCount > 0);
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

    public function syncRiwayatPendidikan(int $offset = 0, int $limit = 500, ?string $syncSince = null): array
    {
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountRiwayatPendidikanMahasiswa();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("SyncRiwayatPendidikan: GetCount failed. Error: " . $e->getMessage());
        }

        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getRiwayatPendidikanMahasiswa($limit, $offset, $filter);
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        if (!empty($data)) {
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

            $this->batchUpsert(RiwayatPendidikanMahasiswa::class, $records, ['id_registrasi_mahasiswa'], [
                'id_mahasiswa',
                'nim',
                'nama_mahasiswa',
                'id_jenis_daftar',
                'nama_jenis_daftar',
                'id_jalur_daftar',
                'nama_jalur_daftar',
                'id_periode_masuk',
                'tanggal_daftar',
                'id_perguruan_tinggi_asal',
                'nama_perguruan_tinggi_asal',
                'id_prodi_asal',
                'nama_prodi_asal',
                'sks_diakui',
                'biaya_masuk',
                'updated_at'
            ]);
            $synced = count($records);
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = ($totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit)) && ($batchCount > 0);
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
    public function getCountMahasiswa(): int
    {
        try {
            $response = $this->neoFeeder->getCountMahasiswa();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountBiodata(): int
    {
        try {
            $response = $this->neoFeeder->getCountBiodataMahasiswa();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountMahasiswaLulusDO(): int
    {
        try {
            $response = $this->neoFeeder->getCountMahasiswaLulusDO();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountRiwayatPendidikan(): int
    {
        try {
            $response = $this->neoFeeder->getCountRiwayatPendidikanMahasiswa();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
