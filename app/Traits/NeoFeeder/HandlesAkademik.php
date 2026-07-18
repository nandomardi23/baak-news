<?php

namespace App\Traits\NeoFeeder;

trait HandlesAkademik
{
    public function getMataKuliah(int $limit = 2000, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListMataKuliah', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountMataKuliah(): ?array
    {
        return $this->requestQuick('GetCountMataKuliah', []);
    }

    public function getKurikulum(int $limit = 100, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListKurikulum', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountKurikulum(): ?array
    {
        return $this->requestQuick('GetCountKurikulum', []);
    }

    public function getMatkulKurikulum(int $limit = 2000, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetMatkulKurikulum', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountMatkulKurikulum(): ?array
    {
        return $this->requestQuick('GetCountMatkulKurikulum', []);
    }

    public function getSkalaNilaiProdi(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListSkalaNilaiProdi', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountSkalaNilaiProdi(): ?array
    {
        return $this->requestQuick('GetCountSkalaNilaiProdi', []);
    }

    public function getKelasKuliah(string $idSemester, int $limit = 2000, int $offset = 0, string $filter = ''): ?array
    {
        $baseFilter = "id_semester = '{$idSemester}'";
        $combinedFilter = $filter ? "$baseFilter AND $filter" : $baseFilter;

        return $this->request('GetListKelasKuliah', [
            'filter' => $combinedFilter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getAllKelasKuliah(int $limit = 2000, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListKelasKuliah', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getDetailKelasKuliah(string $idKelasKuliah): ?array
    {
        return $this->request('GetDetailKelasKuliah', [
            'id_kelas_kuliah' => $idKelasKuliah,
        ]);
    }

    public function getPesertaKelasKuliah(string $idKelasKuliah): ?array
    {
        return $this->request('GetPesertaKelasKuliah', [
            'filter' => "id_kelas_kuliah = '{$idKelasKuliah}'",
            'limit' => 500,
        ]);
    }

    public function getCountKelasKuliah(): ?array
    {
        return $this->requestQuick('GetCountKelasKuliah', []);
    }

    public function getNilaiMahasiswa(string $idRegistrasiMahasiswa, string $idSemester): ?array
    {
        return $this->request('GetDetailNilaiPerkuliahanKelas', [
            'filter' => "id_registrasi_mahasiswa = '{$idRegistrasiMahasiswa}' AND id_semester = '{$idSemester}'",
            'limit' => 500,
        ]);
    }

    public function getRiwayatNilaiMahasiswa(string $idRegistrasiMahasiswa): ?array
    {
        return $this->request('GetDetailNilaiPerkuliahanKelas', [
            'filter' => "id_registrasi_mahasiswa = '{$idRegistrasiMahasiswa}'",
            'limit' => 2000,
        ]);
    }

    public function getNilaiBySemester(string $idSemester, int $limit = 5000, int $offset = 0, string $extraFilter = ''): ?array
    {
        $baseFilter = "id_semester = '{$idSemester}'";
        $filter = $extraFilter ? "$baseFilter AND $extraFilter" : $baseFilter;

        return $this->request('GetDetailNilaiPerkuliahanKelas', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getNilaiByMahasiswa(string $idRegistrasiMahasiswa): ?array
    {
        return $this->request('GetDetailNilaiPerkuliahanKelas', [
            'filter' => "id_registrasi_mahasiswa = '{$idRegistrasiMahasiswa}'",
            'limit' => 1000,
        ]);
    }

    public function getCountNilaiPerkuliahan(): ?array
    {
        return $this->requestQuick('GetCountNilaiPerkuliahanKelas', []);
    }

    public function getAktivitasKuliahMahasiswa(string $idRegistrasiMahasiswa): ?array
    {
        return $this->request('GetAktivitasKuliahMahasiswa', [
            'filter' => "id_registrasi_mahasiswa = '{$idRegistrasiMahasiswa}'",
            'limit' => 50,
        ]);
    }

    public function getKrsMahasiswa(string $filter, int $limit = 500, int $offset = 0): ?array
    {
        return $this->request('GetKRSMahasiswa', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    public function getKrsBySemester(string $idSemester, int $limit = 1000, int $offset = 0, string $extraFilter = ''): ?array
    {
        $filter = "id_periode = '{$idSemester}'";
        if ($extraFilter) {
            $filter .= " AND $extraFilter";
        }
        return $this->getKrsMahasiswa($filter, $limit, $offset);
    }

    public function getCountPerkuliahanMahasiswa(): ?array
    {
        return $this->requestQuick('GetCountPerkuliahanMahasiswa', []);
    }

    public function getAktivitasMahasiswa(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListAktivitasMahasiswa', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountAktivitasMahasiswa(string $filter = ''): ?array
    {
        return $this->requestQuick('GetCountAktivitasMahasiswa', [
            'filter' => $filter
        ]);
    }

    public function getAnggotaAktivitasMahasiswa(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListAnggotaAktivitasMahasiswa', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountAnggotaAktivitasMahasiswa(string $filter = ''): ?array
    {
        return ['data' => 0];
    }

    public function getKonversiKampusMerdeka(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListKonversiKampusMerdeka', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountKonversiKampusMerdeka(string $filter = ''): ?array
    {
        return $this->requestQuick('GetCountKonversiKampusMerdeka', [
            'filter' => $filter
        ]);
    }

    public function getUjiMahasiswa(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListUjiMahasiswa', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountUjiMahasiswa(): ?array
    {
        return ['data' => 0];
    }
}
