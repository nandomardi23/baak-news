<?php

namespace App\Traits\NeoFeeder;

trait HandlesDosen
{
    public function getDosen(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListDosen', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountDosen(): ?array
    {
        return $this->requestQuick('GetCountDosen', []);
    }

    public function getDosenPengajarKelasKuliah(string $idKelasKuliah): ?array
    {
        return $this->request('GetDosenPengajarKelasKuliah', [
            'filter' => "id_kelas_kuliah = '{$idKelasKuliah}'",
            'limit' => 10, // Usually only 1-2 lecturers per class
        ]);
    }

    public function getAllDosenPengajarKelasKuliah(int $limit = 2000, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetDosenPengajarKelasKuliah', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountDosenPengajar(): ?array
    {
        return $this->requestQuick('GetCountDosenPengajarKelasKuliah', []);
    }

    public function getBimbinganMahasiswa(int $limit = 500, int $offset = 0): ?array
    {
        return $this->request('GetListBimbinganMahasiswa', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getBimbingMahasiswa(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetListBimbingMahasiswa', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountBimbingMahasiswa(): ?array
    {
        return $this->requestQuick('GetCountBimbingMahasiswa', []);
    }

    public function getAktivitasMengajarDosen(int $limit = 500, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetAktivitasMengajarDosen', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountAktivitasMengajarDosen(string $filter = ''): ?array
    {
        return $this->requestQuick('GetCountAktivitasMengajarDosen', [
            'filter' => $filter
        ]);
    }
}
