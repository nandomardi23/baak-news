<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MahasiswaExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected ?int $prodiId = null;
    protected ?string $search = null;

    public function __construct(?int $prodiId = null, ?string $search = null)
    {
        $this->prodiId = $prodiId;
        $this->search = $search;
    }

    public function query()
    {
        $query = Mahasiswa::with('programStudi');

        if ($this->prodiId) {
            $query->where('program_studi_id', $this->prodiId);
        }

        if ($this->search) {
            $query->search($this->search);
        }

        return $query->orderBy('nama');
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Program Studi',
            'Angkatan',
            'Status',
            'Email',
            'No HP',
            'IPK',
            'SKS Tempuh',
        ];
    }

    public function map($mahasiswa): array
    {
        return [
            $mahasiswa->nim,
            $mahasiswa->nama,
            $mahasiswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($mahasiswa->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
            $mahasiswa->tempat_lahir ?? '-',
            $mahasiswa->tanggal_lahir?->format('d-m-Y') ?? '-',
            $mahasiswa->programStudi?->nama_prodi ?? '-',
            $mahasiswa->angkatan ?? '-',
            $mahasiswa->status_mahasiswa ?? '-',
            $mahasiswa->email ?? '-',
            $mahasiswa->no_hp ?? '-',
            $mahasiswa->ipk ?? '-',
            $mahasiswa->total_sks_diambil ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
