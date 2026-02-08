<?php

namespace App\Services;

use App\Models\Setting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;

class NeoFeederService
{
    private Client $client;
    private string $url;
    private string $username;
    private string $password;
    private ?string $token = null;
    private int $maxRetries = 3;
    private int $retryDelay = 2; // seconds

    public function __construct()
    {
        // Get credentials from database (encrypted password auto-decrypted)
        $this->url = Setting::getValue('neo_feeder_url', '');
        $this->username = Setting::getValue('neo_feeder_username', '');
        $this->password = Setting::getValue('neo_feeder_password', '');
        
        $this->client = new Client([
            'timeout' => 60, // Reduced to 60s to fail faster
            'connect_timeout' => 10,
            'verify' => false,
        ]);
    }

    /**
     * Get authentication token from Neo Feeder
     */
    public function getToken(): ?string
    {
        if ($this->token) {
            return $this->token;
        }

        try {
            $response = $this->client->post($this->url, [
                'json' => [
                    'act' => 'GetToken',
                    'username' => $this->username,
                    'password' => $this->password,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['data']['token'])) {
                $this->token = $data['data']['token'];
                return $this->token;
            }

            Log::error('Neo Feeder GetToken failed', ['response' => $data]);
            return null;
        } catch (GuzzleException $e) {
            Log::error('Neo Feeder GetToken error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Make API request to Neo Feeder with retry logic
     */
    public function request(string $action, array $params = []): ?array
    {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                // Add small delay between requests to prevent overwhelming the server
                if ($attempt > 0) {
                    $delay = $this->retryDelay * pow(2, $attempt - 1); // Exponential backoff
                    Log::info("Neo Feeder {$action} retry #{$attempt}, waiting {$delay}s");
                    sleep($delay);
                }

                $response = $this->client->post($this->url, [
                    'json' => array_merge([
                        'act' => $action,
                        'token' => $token,
                    ], $params),
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                return $data;
            } catch (ConnectException $e) {
                // Timeout or connection error - retry
                $attempt++;
                $lastException = $e;
                Log::warning("Neo Feeder {$action} timeout (attempt {$attempt}/{$this->maxRetries})", [
                    'message' => $e->getMessage()
                ]);
            } catch (GuzzleException $e) {
                // Other errors - don't retry
                Log::error("Neo Feeder {$action} error", ['message' => $e->getMessage()]);
                return null;
            }
        }

        // All retries exhausted
        Log::error("Neo Feeder {$action} failed after {$this->maxRetries} retries", [
            'message' => $lastException?->getMessage()
        ]);
        return null;
    }

    /**
     * Get list of mahasiswa (all, no filter - API doesn't support id_prodi filter)
     */
    public function getMahasiswa(): ?array
    {
        return $this->request('GetListMahasiswa', [
            'limit' => 10000,
            'offset' => 0,
        ]);
    }

    /**
     * Get detail mahasiswa by ID
     */
    public function getDetailMahasiswa(string $idMahasiswa): ?array
    {
        return $this->request('GetDetailMahasiswa', [
            'id_mahasiswa' => $idMahasiswa,
        ]);
    }

    /**
     * Get biodata mahasiswa
     */
    public function getBiodataMahasiswa(string $idMahasiswa): ?array
    {
        return $this->request('GetBiodataMahasiswa', [
            'filter' => "id_mahasiswa='{$idMahasiswa}'",
        ]);
    }

    /**
     * Get Program Studi list
     */
    public function getProdi(): ?array
    {
        return $this->request('GetProdi');
    }

    /**
     * Get Semester list
     */
    public function getSemester(): ?array
    {
        return $this->request('GetSemester');
    }

    /**
     * Get Mata Kuliah list (all, no filter - API doesn't support id_prodi filter)
     */
    public function getMataKuliah(): ?array
    {
        return $this->request('GetListMataKuliah', [
            'limit' => 10000,
        ]);
    }



    /**
     * Get Nilai (KHS) Mahasiswa
     */
    /**
     * Get Nilai (KHS) Mahasiswa
     * Uses GetDetailNilaiPerkuliahanKelas (Faster)
     */
    public function getNilaiMahasiswa(string $idRegistrasiMahasiswa, string $idSemester): ?array
    {
        return $this->request('GetDetailNilaiPerkuliahanKelas', [
            'filter' => "id_registrasi_mahasiswa = '{$idRegistrasiMahasiswa}' AND id_semester = '{$idSemester}'",
            'limit' => 500,
        ]);
    }

    /**
     * Get Riwayat Nilai Mahasiswa (untuk Transkrip)
     * Uses GetDetailNilaiPerkuliahanKelas (Faster)
     */
    public function getRiwayatNilaiMahasiswa(string $idRegistrasiMahasiswa): ?array
    {
        return $this->request('GetDetailNilaiPerkuliahanKelas', [
            'filter' => "id_registrasi_mahasiswa = '{$idRegistrasiMahasiswa}'",
            'limit' => 2000, // Fetch all history
        ]);
    }

    /**
     * Get riwayat status mahasiswa
     */
    public function getRiwayatStatusMahasiswa(string $idRegistrasiMahasiswa): ?array
    {
        return $this->request('GetRiwayatRegistrasiMahasiswa', [
            'filter' => "id_registrasi_mahasiswa = '{$idRegistrasiMahasiswa}'",
        ]);
    }

    /**
     * Get aktivitas kuliah mahasiswa (IPK, SKS)
     */
    public function getAktivitasKuliahMahasiswa(string $idRegistrasiMahasiswa): ?array
    {
        return $this->request('GetAktivitasKuliahMahasiswa', [
            'filter' => "id_registrasi_mahasiswa = '{$idRegistrasiMahasiswa}'",
            'limit' => 50,
        ]);
    }

    /**
     * Get KRS (Class Enrollment) Mahasiswa
     * Can filter by Student ID (specific) or Semester ID (Bulk)
     */
    public function getKrsMahasiswa(string $filter, int $limit = 500, int $offset = 0): ?array
    {
        return $this->request('GetKRSMahasiswa', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Get KRS By Semester (Bulk)
     */
    public function getKrsBySemester(string $idSemester, int $limit = 1000, int $offset = 0): ?array
    {
        return $this->getKrsMahasiswa("id_periode = '{$idSemester}'", $limit, $offset);
    }

    /**
     * Get list of dosen (all, no filter)
     */
    public function getDosen(): ?array
    {
        return $this->request('GetListDosen', [
            'limit' => 10000,
        ]);
    }

    /**
     * Get Nilai by Semester (BULK - for efficient sync)
     * Uses GetDetailNilaiPerkuliahanKelas which is much faster
     * 
     * @param string $idSemester Semester ID (e.g., "20241" for 2024/2025 Ganjil)
     * @param int $limit Max records to fetch
     * @param int $offset Offset for pagination
     */
    public function getNilaiBySemester(string $idSemester, int $limit = 5000, int $offset = 0): ?array
    {
        return $this->request('GetDetailNilaiPerkuliahanKelas', [
            'filter' => "id_semester = '{$idSemester}'",
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Nilai by Mahasiswa (using correct endpoint)
     * Uses GetDetailNilaiPerkuliahanKelas with filter
     * 
     * @param string $idRegistrasiMahasiswa
     */
    public function getNilaiByMahasiswa(string $idRegistrasiMahasiswa): ?array
    {
        return $this->request('GetDetailNilaiPerkuliahanKelas', [
            'filter' => "id_registrasi_mahasiswa = '{$idRegistrasiMahasiswa}'",
            'limit' => 1000,
        ]);
    }

    /**
     * Get Dosen Pengajar Kelas Kuliah
     * Used to find out who teaches a specific class
     */
    public function getDosenPengajarKelasKuliah(string $idKelasKuliah): ?array
    {
        return $this->request('GetDosenPengajarKelasKuliah', [
            'filter' => "id_kelas_kuliah = '{$idKelasKuliah}'",
            'limit' => 10, // Usually only 1-2 lecturers per class
        ]);
    }

    /**
     * Get Kelas Kuliah (Classes) by Semester
     * Used to sync all classes for a specific semester
     */
    public function getKelasKuliah(string $idSemester, int $limit = 2000, int $offset = 0): ?array
    {
        return $this->request('GetListKelasKuliah', [
            'filter' => "id_semester = '{$idSemester}'",
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get All Kelas Kuliah (no filter)
     * Used for full sync
     */
    public function getAllKelasKuliah(int $limit = 2000, int $offset = 0): ?array
    {
        return $this->request('GetListKelasKuliah', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Detail Kelas Kuliah
     * Used to get detailed information about a specific class
     */
    public function getDetailKelasKuliah(string $idKelasKuliah): ?array
    {
        return $this->request('GetDetailKelasKuliah', [
            'id_kelas_kuliah' => $idKelasKuliah,
        ]);
    }

    /**
     * Get Peserta Kelas Kuliah (Students enrolled in a class)
     * Used to get list of students in a specific class
     */
    public function getPesertaKelasKuliah(string $idKelasKuliah): ?array
    {
        return $this->request('GetPesertaKelasKuliah', [
            'filter' => "id_kelas_kuliah = '{$idKelasKuliah}'",
            'limit' => 500,
        ]);
    }

    /**
     * Get Count of Kelas Kuliah
     * Used to get total count for pagination
     */
    public function getCountKelasKuliah(): ?array
    {
        return $this->request('GetCountKelasKuliah', []);
    }

    /**
     * Get Count of Mahasiswa
     */
    public function getCountMahasiswa(): ?array
    {
        return $this->request('GetCountMahasiswa', []);
    }

    /**
     * Get Count of Dosen
     */
    public function getCountDosen(): ?array
    {
        return $this->request('GetCountDosen', []);
    }

    /**
     * Get Count of Mata Kuliah
     */
    public function getCountMataKuliah(): ?array
    {
        return $this->request('GetCountMataKuliah', []);
    }

    /**
     * Get Count of Nilai Perkuliahan
     */
    public function getCountNilaiPerkuliahan(): ?array
    {
        return $this->request('GetCountNilaiPerkuliahanKelas', []);
    }

    /**
     * Get Count of Perkuliahan Mahasiswa (for Aktivitas/KRS)
     */
    public function getCountPerkuliahanMahasiswa(): ?array
    {
        return $this->request('GetCountPerkuliahanMahasiswa', []);
    }

    /**
     * Get Count of Dosen Pengajar Kelas Kuliah
     */
    public function getCountDosenPengajar(): ?array
    {
        return $this->request('GetCountDosenPengajarKelasKuliah', []);
    }

    /**
     * Get Count of Program Studi
     */
    public function getCountProdi(): ?array
    {
        return $this->request('GetCountProdi', []);
    }

    /**
     * Get Count of Biodata Mahasiswa
     */
    public function getCountBiodataMahasiswa(): ?array
    {
        return $this->request('GetCountBiodataMahasiswa', []);
    }
}

