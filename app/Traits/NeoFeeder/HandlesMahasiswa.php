<?php

namespace App\Traits\NeoFeeder;

trait HandlesMahasiswa
{
    public function getMahasiswa(int $limit = 2000, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListMahasiswa', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountMahasiswa(): ?array
    {
        return $this->requestQuick('GetCountMahasiswa', []);
    }

    public function getDetailMahasiswa(string $idMahasiswa): ?array
    {
        return $this->request('GetDetailMahasiswa', [
            'id_mahasiswa' => $idMahasiswa,
        ]);
    }

    public function getBiodataMahasiswa(?string $idMahasiswa = null, int $limit = 500, int $offset = 0, string $extraFilter = ''): ?array
    {
        $params = [
            'limit' => $limit,
            'offset' => $offset,
        ];

        $filters = [];
        if ($idMahasiswa) {
            $filters[] = "id_mahasiswa='{$idMahasiswa}'";
        }
        if ($extraFilter) {
            $filters[] = $extraFilter;
        }

        if (!empty($filters)) {
            $params['filter'] = implode(' AND ', $filters);
        }

        return $this->request('GetBiodataMahasiswa', $params);
    }

    public function getCountBiodataMahasiswa(): ?array
    {
        return $this->requestQuick('GetCountBiodataMahasiswa', []);
    }

    public function getRiwayatPendidikanMahasiswa(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListRiwayatPendidikanMahasiswa', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountRiwayatPendidikanMahasiswa(): ?array
    {
        return $this->requestQuick('GetCountRiwayatPendidikanMahasiswa', []);
    }

    public function getRiwayatStatusMahasiswa(string $idRegistrasiMahasiswa): ?array
    {
        return $this->request('GetRiwayatRegistrasiMahasiswa', [
            'filter' => "id_registrasi_mahasiswa = '{$idRegistrasiMahasiswa}'",
        ]);
    }

    public function getMahasiswaLulusDO(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListMahasiswaLulusDO', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountMahasiswaLulusDO(): ?array
    {
        return $this->requestQuick('GetCountMahasiswaLulusDO', []);
    }
}
