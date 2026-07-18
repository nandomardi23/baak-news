<?php

namespace App\Traits\NeoFeeder;

trait HandlesReferensi
{
    public function getDictionary(): ?array
    {
        return $this->requestQuick('GetDictionary', []);
    }

    public function getAgama(): ?array
    {
        return $this->request('GetAgama', []);
    }

    public function getJenisTinggal(): ?array
    {
        return $this->request('GetJenisTinggal', []);
    }

    public function getAlatTransportasi(): ?array
    {
        return $this->request('GetAlatTransportasi', []);
    }

    public function getPekerjaan(): ?array
    {
        return $this->request('GetPekerjaan', []);
    }

    public function getPenghasilan(): ?array
    {
        return $this->request('GetPenghasilan', []);
    }

    public function getKebutuhanKhusus(): ?array
    {
        return $this->request('GetKebutuhanKhusus', []);
    }

    public function getPembiayaan(): ?array
    {
        return $this->request('GetPembiayaan', []);
    }

    public function getWilayah(int $limit = 1000, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetWilayah', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountWilayah(): ?array
    {
        return $this->requestQuick('GetCountWilayah', []);
    }

    public function getProdi(int $limit = 100, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetProdi', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountProdi(): ?array
    {
        return $this->requestQuick('GetCountProdi', []);
    }

    public function getSemester(int $limit = 100, int $offset = 0, string $filter = ''): ?array
    {
        return $this->request('GetSemester', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function getCountSemester(): ?array
    {
        return $this->requestQuick('GetCountSemester', []);
    }
}
