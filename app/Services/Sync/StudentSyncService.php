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
            $records = $this->mapMahasiswaData($data);

            $updateColumns = [
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
            ];

            try {
                $this->batchUpsert(Mahasiswa::class, $records, ['id_registrasi_mahasiswa'], $updateColumns);
                $synced = count($records);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("SyncMahasiswa: Batch upsert failed, falling back to one-by-one. Error: " . $e->getMessage());
                foreach ($records as $record) {
                    try {
                        Mahasiswa::upsert([$record], ['id_registrasi_mahasiswa'], $updateColumns);
                        $synced++;
                    } catch (\Exception $inner) {
                        $errors[] = "Mahasiswa {$record['nim']} ({$record['nama']}): " . $inner->getMessage();
                        \Illuminate\Support\Facades\Log::warning("SyncMahasiswa: Failed to upsert NIM {$record['nim']}: " . $inner->getMessage());
                    }
                }
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
                    $this->updateMahasiswaBiodata($mahasiswa, $bio);
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
            $records = $this->mapMahasiswaLulusDOData($data);

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
            $records = $this->mapRiwayatPendidikanData($data);

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

    private function mapMahasiswaData(array $data): array
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

    private function updateMahasiswaBiodata(Mahasiswa $mahasiswa, array $bio): void
    {
        $mahasiswa->tempat_lahir = $bio['tempat_lahir'] ?? null;
        $mahasiswa->id_agama = $bio['id_agama'] ?? null;
        $mahasiswa->nama_agama = $bio['nama_agama'] ?? null;
        $mahasiswa->nik = $bio['nik'] ?? null;
        $mahasiswa->nisn = $bio['nisn'] ?? null;
        $mahasiswa->npwp = $bio['npwp'] ?? null;
        $mahasiswa->kewarganegaraan = $bio['kewarganegaraan'] ?? null;
        $mahasiswa->jalan = $bio['jalan'] ?? null;
        $mahasiswa->dusun = $bio['dusun'] ?? null;
        $mahasiswa->rt = $bio['rt'] ?? null;
        $mahasiswa->rw = $bio['rw'] ?? null;
        $mahasiswa->kelurahan = $bio['kelurahan'] ?? null;
        $mahasiswa->kode_pos = $bio['kode_pos'] ?? null;
        $mahasiswa->id_wilayah = $bio['id_wilayah'] ?? null;
        $mahasiswa->nama_wilayah = $bio['nama_wilayah'] ?? null;
        $mahasiswa->id_jenis_tinggal = $bio['id_jenis_tinggal'] ?? null;
        $mahasiswa->nama_jenis_tinggal = $bio['nama_jenis_tinggal'] ?? null;
        $mahasiswa->id_alat_transportasi = $bio['id_alat_transportasi'] ?? null;
        $mahasiswa->nama_alat_transportasi = $bio['nama_alat_transportasi'] ?? null;
        $mahasiswa->telepon = $bio['telepon'] ?? null;
        $mahasiswa->handphone = $bio['handphone'] ?? null;
        $mahasiswa->email = $bio['email'] ?? null;
        
        $mahasiswa->nik_ayah = $bio['nik_ayah'] ?? null;
        $mahasiswa->nama_ayah = $bio['nama_ayah'] ?? null;
        $mahasiswa->tanggal_lahir_ayah = $this->parseDate($bio['tanggal_lahir_ayah'] ?? null);
        $mahasiswa->id_pendidikan_ayah = $bio['id_pendidikan_ayah'] ?? null;
        $mahasiswa->nama_pendidikan_ayah = $bio['nama_pendidikan_ayah'] ?? null;
        $mahasiswa->id_pekerjaan_ayah = $bio['id_pekerjaan_ayah'] ?? null;
        $mahasiswa->nama_pekerjaan_ayah = $bio['nama_pekerjaan_ayah'] ?? null;
        $mahasiswa->id_penghasilan_ayah = $bio['id_penghasilan_ayah'] ?? null;
        $mahasiswa->nama_penghasilan_ayah = $bio['nama_penghasilan_ayah'] ?? null;
        
        $mahasiswa->nik_ibu = $bio['nik_ibu'] ?? null;
        $mahasiswa->nama_ibu = $bio['nama_ibu_kandung'] ?? null;
        $mahasiswa->tanggal_lahir_ibu = $this->parseDate($bio['tanggal_lahir_ibu'] ?? null);
        $mahasiswa->id_pendidikan_ibu = $bio['id_pendidikan_ibu'] ?? null;
        $mahasiswa->nama_pendidikan_ibu = $bio['nama_pendidikan_ibu'] ?? null;
        $mahasiswa->id_pekerjaan_ibu = $bio['id_pekerjaan_ibu'] ?? null;
        $mahasiswa->nama_pekerjaan_ibu = $bio['nama_pekerjaan_ibu'] ?? null;
        $mahasiswa->id_penghasilan_ibu = $bio['id_penghasilan_ibu'] ?? null;
        $mahasiswa->nama_penghasilan_ibu = $bio['nama_penghasilan_ibu'] ?? null;
        
        $mahasiswa->nama_wali = $bio['nama_wali'] ?? null;
        $mahasiswa->tanggal_lahir_wali = $this->parseDate($bio['tanggal_lahir_wali'] ?? null);
        $mahasiswa->id_pendidikan_wali = $bio['id_pendidikan_wali'] ?? null;
        $mahasiswa->nama_pendidikan_wali = $bio['nama_pendidikan_wali'] ?? null;
        $mahasiswa->id_pekerjaan_wali = $bio['id_pekerjaan_wali'] ?? null;
        $mahasiswa->nama_pekerjaan_wali = $bio['nama_pekerjaan_wali'] ?? null;
        $mahasiswa->id_penghasilan_wali = $bio['id_penghasilan_wali'] ?? null;
        $mahasiswa->nama_penghasilan_wali = $bio['nama_penghasilan_wali'] ?? null;
        
        $mahasiswa->id_kebutuhan_khusus_mahasiswa = $bio['id_kebutuhan_khusus_mahasiswa'] ?? null;
        $mahasiswa->nama_kebutuhan_khusus_mahasiswa = $bio['nama_kebutuhan_khusus_mahasiswa'] ?? null;
        $mahasiswa->id_kebutuhan_khusus_ayah = $bio['id_kebutuhan_khusus_ayah'] ?? null;
        $mahasiswa->nama_kebutuhan_khusus_ayah = $bio['nama_kebutuhan_khusus_ayah'] ?? null;
        $mahasiswa->id_kebutuhan_khusus_ibu = $bio['id_kebutuhan_khusus_ibu'] ?? null;
        $mahasiswa->nama_kebutuhan_khusus_ibu = $bio['nama_kebutuhan_khusus_ibu'] ?? null;
        
        $mahasiswa->save();
    }

    private function mapMahasiswaLulusDOData(array $data): array
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

    private function mapRiwayatPendidikanData(array $data): array
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