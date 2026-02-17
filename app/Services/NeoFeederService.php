<?php

namespace App\Services;

use App\Models\Setting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class NeoFeederService
{
    private Client $client;
    private Client $quickClient;
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
            'timeout' => 200, // Increased to 200s to handle slow responses (batch 500)
            'connect_timeout' => 30,
            'verify' => false,
        ]);

        // Quick client for GetCount* endpoints that may hang
        $this->quickClient = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
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
    /**
     * Quick request with short timeout for non-critical calls (e.g. GetCount*)
     * No retries - returns null on failure instead of blocking for minutes.
     */
    public function requestQuick(string $action, array $params = []): ?array
    {
        $token = $this->getToken();
        if (!$token) return null;

        try {
            Log::info("Neo Feeder Quick Request: {$action}", ['params' => $params]);
            $response = $this->quickClient->post($this->url, [
                'json' => array_merge([
                    'act' => $action,
                    'token' => $token,
                ], $params),
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            return $data;
        } catch (\Exception $e) {
            Log::warning("Neo Feeder {$action} quick request failed: " . $e->getMessage());
            return null;
        }
    }

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

                Log::info("Neo Feeder Request: {$action}", ['params' => $params]);
                
                $response = $this->client->post($this->url, [
                    'json' => array_merge([
                        'act' => $action,
                        'token' => $token,
                    ], $params),
                ]);

                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                if (is_null($data)) {
                    Log::warning("Neo Feeder {$action} returned non-JSON response", ['body' => substr($body, 0, 2000)]);
                    // Return raw decoded data (null) so callers can handle it
                    return null;
                }

                // If API reports an error related to token (expired/invalid), refresh token and retry once
                if (isset($data['error_code']) && $data['error_code'] != 0) {
                    $desc = strtolower($data['error_desc'] ?? '');
                    if (str_contains($desc, 'token') || str_contains($desc, 'expired') || str_contains($desc, 'invalid')) {
                        Log::info("Neo Feeder {$action} detected token issue; refreshing token and retrying");
                        // Clear cached token and obtain a new one
                        $this->token = null;
                        $token = $this->getToken();
                        if (!$token) {
                            return null;
                        }
                        $attempt++;
                        continue;
                    }
                }

                return $data;
            } catch (ConnectException $e) {
                // Timeout or connection error - retry
                $attempt++;
                $lastException = $e;
                Log::warning("Neo Feeder {$action} timeout (attempt {$attempt}/{$this->maxRetries})", [
                    'message' => $e->getMessage()
                ]);
            } catch (RequestException $e) {
                // HTTP errors - retry for server-side issues (5xx) and rate limits (429)
                $status = null;
                if (method_exists($e, 'getResponse') && $e->getResponse()) {
                    $status = $e->getResponse()->getStatusCode();
                }

                if ($status === 429 || ($status >= 500 && $status < 600)) {
                    $attempt++;
                    $lastException = $e;
                    Log::warning("Neo Feeder {$action} server error (status {$status}) - retrying (attempt {$attempt}/{$this->maxRetries})", ['message' => $e->getMessage()]);
                    continue;
                }

                Log::error("Neo Feeder {$action} request error", ['message' => $e->getMessage(), 'status' => $status]);
                return null;
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
     * Get list of mahasiswa with pagination
     */
    public function getMahasiswa(int $limit = 2000, int $offset = 0): ?array
    {
        return $this->request('GetListMahasiswa', [
            'limit' => $limit,
            'offset' => $offset,
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
     * Get Program Studi list with pagination
     */
    public function getProdi(int $limit = 100, int $offset = 0): ?array
    {
        return $this->request('GetProdi', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Semester list with pagination
     */
    public function getSemester(int $limit = 100, int $offset = 0): ?array
    {
        return $this->request('GetSemester', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Mata Kuliah list with pagination
     */
    public function getMataKuliah(int $limit = 2000, int $offset = 0): ?array
    {
        return $this->request('GetListMataKuliah', [
            'limit' => $limit,
            'offset' => $offset,
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
     * Get list of dosen with pagination
     */
    public function getDosen(int $limit = 500, int $offset = 0): ?array
    {
        return $this->request('GetListDosen', [
            'limit' => $limit,
            'offset' => $offset,
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
        return $this->requestQuick('GetCountKelasKuliah', []);
    }

    /**
     * Get Count of Mahasiswa
     */
    public function getCountMahasiswa(): ?array
    {
        return $this->requestQuick('GetCountMahasiswa', []);
    }

    /**
     * Get Count of Dosen
     */
    public function getCountDosen(): ?array
    {
        return $this->requestQuick('GetCountDosen', []);
    }

    /**
     * Get Count of Mata Kuliah
     */
    public function getCountMataKuliah(): ?array
    {
        return $this->requestQuick('GetCountMataKuliah', []);
    }

    /**
     * Get Count of Nilai Perkuliahan
     */
    public function getCountNilaiPerkuliahan(): ?array
    {
        return $this->requestQuick('GetCountNilaiPerkuliahanKelas', []);
    }

    /**
     * Get Count of Perkuliahan Mahasiswa (for Aktivitas/KRS)
     */
    public function getCountPerkuliahanMahasiswa(): ?array
    {
        return $this->requestQuick('GetCountPerkuliahanMahasiswa', []);
    }

    /**
     * Get Count of Dosen Pengajar Kelas Kuliah
     */
    public function getCountDosenPengajar(): ?array
    {
        return $this->requestQuick('GetCountDosenPengajarKelasKuliah', []);
    }

    /**
     * Get Count of Program Studi
     */
    public function getCountProdi(): ?array
    {
        return $this->requestQuick('GetCountProdi', []);
    }

    /**
     * Get Count of Biodata Mahasiswa
     */
    public function getCountBiodataMahasiswa(): ?array
    {
        return $this->requestQuick('GetCountBiodataMahasiswa', []);
    }

    /**
     * Get Count of Semester (uses quick timeout - this endpoint is known to hang)
     */
    public function getCountSemester(): ?array
    {
        return $this->requestQuick('GetCountSemester', []);
    }
    /**
     * Get Agama (Religion)
     */
    public function getAgama(): ?array
    {
        return $this->request('GetAgama', []);
    }

    /**
     * Get Jenis Tinggal (Residence Type)
     */
    public function getJenisTinggal(): ?array
    {
        return $this->request('GetJenisTinggal', []);
    }

    /**
     * Get Alat Transportasi
     */
    public function getAlatTransportasi(): ?array
    {
        return $this->request('GetAlatTransportasi', []);
    }

    /**
     * Get Pekerjaan (Occupation)
     */
    public function getPekerjaan(): ?array
    {
        return $this->request('GetPekerjaan', []);
    }

    /**
     * Get Penghasilan (Income)
     */
    public function getPenghasilan(): ?array
    {
        return $this->request('GetPenghasilan', []);
    }

    /**
     * Get Kebutuhan Khusus (Special Needs)
     */
    public function getKebutuhanKhusus(): ?array
    {
        return $this->request('GetKebutuhanKhusus', []);
    }

    /**
     * Get Pembiayaan (Scholarship/Funding)
     */
    public function getPembiayaan(): ?array
    {
        return $this->request('GetPembiayaan', []);
    }

    /**
     * Get Wilayah (Regions) with pagination
     * There are thousands of regions, so pagination is needed.
     */
    public function getWilayah(int $limit = 1000, int $offset = 0): ?array
    {
        return $this->request('GetWilayah', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count of Wilayah (uses quick timeout - this endpoint is known to hang)
     */
    public function getCountWilayah(): ?array
    {
        return $this->requestQuick('GetCountWilayah', []);
    }
    /**
     * Get List Kurikulum
     */
    public function getKurikulum(int $limit = 100, int $offset = 0): ?array
    {
        return $this->request('GetListKurikulum', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count Kurikulum
     */
    public function getCountKurikulum(): ?array
    {
        return $this->requestQuick('GetCountKurikulum', []);
    }

    /**
     * Get Mata Kuliah Kurikulum (Courses in a Curriculum)
     */
    public function getMatkulKurikulum(int $limit = 2000, int $offset = 0): ?array
    {
        return $this->request('GetMatkulKurikulum', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count Mata Kuliah Kurikulum
     */
    public function getCountMatkulKurikulum(): ?array
    {
        return $this->requestQuick('GetCountMatkulKurikulum', []);
    }
    /**
     * Get Skala Nilai Prodi
     */
    public function getSkalaNilaiProdi(int $limit = 500, int $offset = 0): ?array
    {
        return $this->request('GetListSkalaNilaiProdi', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count Skala Nilai Prodi
     */
    public function getCountSkalaNilaiProdi(): ?array
    {
        return $this->requestQuick('GetCountSkalaNilaiProdi', []);
    }
    /**
     * Get Aktivitas Mahasiswa (Non-Class Activities: KKN, PKL, MBKM, etc.)
     */
    public function getAktivitasMahasiswa(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListAktivitasMahasiswa', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count Aktivitas Mahasiswa
     */
    public function getCountAktivitasMahasiswa(string $filter = ''): ?array
    {
        return $this->requestQuick('GetCountAktivitasMahasiswa', [
            'filter' => $filter
        ]);
    }

    /**
     * Get Anggota Aktivitas Mahasiswa (Participants)
     */
    public function getAnggotaAktivitasMahasiswa(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListAnggotaAktivitasMahasiswa', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count Anggota Aktivitas Mahasiswa
     */
    public function getCountAnggotaAktivitasMahasiswa(string $filter = ''): ?array
    {
        return $this->requestQuick('GetCountAnggotaAktivitasMahasiswa', [
            'filter' => $filter
        ]);
    }

    /**
     * Get Konversi Kampus Merdeka (Credit Transfer for MBKM)
     */
    public function getKonversiKampusMerdeka(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListKonversiKampusMerdeka', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count Konversi Kampus Merdeka
     */
    public function getCountKonversiKampusMerdeka(string $filter = ''): ?array
    {
        return $this->requestQuick('GetCountKonversiKampusMerdeka', [
            'filter' => $filter
        ]);
    }

    /**
     * Get List Aktivitas Mengajar Dosen
     */
    public function getAktivitasMengajarDosen(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListAktivitasMengajarDosen', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count Aktivitas Mengajar Dosen
     */
    public function getCountAktivitasMengajarDosen(string $filter = ''): ?array
    {
        return $this->requestQuick('GetCountAktivitasMengajarDosen', [
            'filter' => $filter
        ]);
    }

    /**
     * Get List Bimbingan Mahasiswa
     */
    public function getBimbingMahasiswa(int $limit = 500, int $offset = 0): ?array
    {
        return $this->request('GetListBimbingMahasiswa', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count Bimbingan Mahasiswa
     */
    public function getCountBimbingMahasiswa(): ?array
    {
        return $this->requestQuick('GetCountBimbingMahasiswa', []);
    }

    /**
     * Get List Uji Mahasiswa
     */
    public function getUjiMahasiswa(int $limit = 500, int $offset = 0): ?array
    {
        return $this->request('GetListUjiMahasiswa', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count Uji Mahasiswa
     */
    public function getCountUjiMahasiswa(): ?array
    {
        return $this->requestQuick('GetCountUjiMahasiswa', []);
    }

    /**
     * Get List Mahasiswa Lulus DO
     */
    public function getMahasiswaLulusDO(int $limit = 500, int $offset = 0): ?array
    {
        return $this->request('GetListMahasiswaLulusDO', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count Mahasiswa Lulus DO
     */
    public function getCountMahasiswaLulusDO(): ?array
    {
        return $this->requestQuick('GetCountMahasiswaLulusDO', []);
    }

    /**
     * Get List Riwayat Pendidikan Mahasiswa
     */
    public function getRiwayatPendidikanMahasiswa(int $limit = 500, int $offset = 0): ?array
    {
        return $this->request('GetListRiwayatPendidikanMahasiswa', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get Count Riwayat Pendidikan Mahasiswa
     */
    public function getCountRiwayatPendidikanMahasiswa(): ?array
    {
        return $this->requestQuick('GetCountRiwayatPendidikanMahasiswa', []);
    }
}

