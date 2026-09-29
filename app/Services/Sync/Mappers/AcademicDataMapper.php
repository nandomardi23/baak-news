<?php

namespace App\Services\Sync\Mappers;

/**
 * Handles data transformation from Neo Feeder API format to local database format.
 * Extracted from AcademicSyncService to follow Single Responsibility Principle.
 */
class AcademicDataMapper
{
    /**
     * Map KelasKuliah API data to local DB records.
     */
    public static function mapKelasKuliah(array $data): array
    {
        $idMatkuls = collect($data)->pluck('id_matkul')->unique()->filter()->toArray();
        $idProdis = collect($data)->pluck('id_prodi')->unique()->filter()->toArray();
        $idSemesters = collect($data)->pluck('id_semester')->unique()->filter()->toArray();

        $matkulMap = \App\Models\MataKuliah::whereIn('id_matkul', $idMatkuls)
            ->get(['id', 'id_matkul', 'kode_matkul', 'nama_matkul'])
            ->keyBy('id_matkul');

        $prodiMap = \App\Models\ProgramStudi::whereIn('id_prodi', $idProdis)
            ->pluck('id', 'id_prodi');

        $semesterMap = \App\Models\TahunAkademik::whereIn('id_semester', $idSemesters)
            ->pluck('id', 'id_semester');

        $records = [];
        foreach ($data as $item) {
            $matkul = $matkulMap[$item['id_matkul']] ?? null;

            $records[] = [
                'id_kelas_kuliah' => $item['id_kelas_kuliah'],
                'id_prodi' => $item['id_prodi'],
                'program_studi_id' => $prodiMap[$item['id_prodi']] ?? null,
                'id_semester' => $item['id_semester'],
                'tahun_akademik_id' => $semesterMap[$item['id_semester']] ?? null,
                'id_matkul' => $item['id_matkul'],
                'mata_kuliah_id' => $matkul ? $matkul->id : null,
                'kode_mata_kuliah' => $item['kode_mata_kuliah'] ?? ($matkul ? $matkul->kode_matkul : null),
                'nama_mata_kuliah' => $item['nama_mata_kuliah'] ?? ($matkul ? $matkul->nama_matkul : null),
                'nama_kelas_kuliah' => $item['nama_kelas_kuliah'],
                'sks' => $item['sks'],
                'bahasan' => $item['bahasan'] ?? null,
                'tanggal_mulai_efektif' => isset($item['tanggal_mulai_efektif']) ? date('Y-m-d', strtotime($item['tanggal_mulai_efektif'])) : null,
                'tanggal_akhir_efektif' => isset($item['tanggal_akhir_efektif']) ? date('Y-m-d', strtotime($item['tanggal_akhir_efektif'])) : null,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        return $records;
    }

    /**
     * Map DosenPengajar API data to pivot records and kelas-dosen updates.
     */
    public static function mapDosenPengajar(array $data): array
    {
        $apiKelasIds = collect($data)->pluck('id_kelas_kuliah')->unique()->filter()->toArray();
        $apiDosenIds = collect($data)->pluck('id_dosen')->unique()->filter()->toArray();

        $kelasMap = \App\Models\KelasKuliah::whereIn('id_kelas_kuliah', $apiKelasIds)
            ->pluck('id', 'id_kelas_kuliah');

        $dosenMap = \App\Models\Dosen::whereIn('id_dosen', $apiDosenIds)
            ->pluck('id', 'id_dosen');

        $pivotRecords = [];
        $kelasDosenUpdates = [];

        foreach ($data as $item) {
            $kelasLocalId = $kelasMap[$item['id_kelas_kuliah']] ?? null;
            $dosenLocalId = $dosenMap[$item['id_dosen']] ?? null;

            if (!$kelasLocalId || !$dosenLocalId)
                continue;

            $pivotRecords[] = [
                'kelas_kuliah_id' => $kelasLocalId,
                'id_kelas_kuliah' => $item['id_kelas_kuliah'],
                'dosen_id' => $dosenLocalId,
                'id_dosen' => $item['id_dosen'],
                'id_aktivitas_mengajar' => $item['id_aktivitas_mengajar'] ?? null,
                'id_registrasi_dosen' => $item['id_registrasi_dosen'] ?? null,
                'sks_substansi_total' => $item['sks_substansi_total'] ?? 0,
                'rencana_tatap_muka' => $item['rencana_tatap_muka'] ?? 0,
                'realisasi_tatap_muka' => $item['realisasi_tatap_muka'] ?? 0,
                'id_jenis_evaluasi' => $item['id_jenis_evaluasi'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ];

            $kelasDosenUpdates[$item['id_kelas_kuliah']] = $item['id_dosen'];
        }

        return ['pivotRecords' => $pivotRecords, 'kelasDosenUpdates' => $kelasDosenUpdates];
    }

    /**
     * Map BimbinganMahasiswa API data to local DB records.
     */
    public static function mapBimbinganMahasiswa(array $data): array
    {
        $records = [];
        foreach ($data as $item) {
            $idAktivitas = $item['id_aktivitas_mahasiswa'] ?? null;
            $idDosen = $item['id_dosen'] ?? null;

            if (!$idAktivitas || !$idDosen)
                continue;

            $records[] = [
                'id_aktivitas_mahasiswa' => $idAktivitas,
                'id_dosen' => $idDosen,
                'id_bimbingan_mahasiswa' => $item['id_bimbingan_mahasiswa'] ?? md5($idAktivitas . $idDosen),
                'pembimbing_ke' => $item['pembimbing_ke'] ?? null,
                'id_kategori_kegiatan' => $item['id_kategori_kegiatan'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }
        return $records;
    }

    /**
     * Map UjiMahasiswa API data to local DB records.
     */
    public static function mapUjiMahasiswa(array $data): array
    {
        $records = [];
        foreach ($data as $item) {
            $records[] = [
                'id_uji_mahasiswa' => $item['id_uji_mahasiswa'],
                'id_aktivitas_mahasiswa' => $item['id_aktivitas_mahasiswa'] ?? '',
                'id_dosen' => $item['id_dosen'] ?? '',
                'penguji_ke' => $item['penguji_ke'] ?? null,
                'id_kategori_kegiatan' => $item['id_kategori_kegiatan'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }
        return $records;
    }

    /**
     * Map AktivitasMahasiswa API data to local DB records.
     */
    public static function mapAktivitasMahasiswa(array $data, callable $parseDateFn): array
    {
        $records = [];
        foreach ($data as $item) {
            $idAktivitas = $item['id_aktivitas'] ?? $item['id_aktivitas_mahasiswa'] ?? null;
            if (!$idAktivitas)
                continue;

            $records[] = [
                'id_aktivitas' => $idAktivitas,
                'id_jenis_aktivitas' => $item['id_jenis_aktivitas'] ?? null,
                'nama_jenis_aktivitas' => $item['nama_jenis_aktivitas'] ?? null,
                'id_prodi' => $item['id_prodi'] ?? null,
                'id_semester' => $item['id_semester'] ?? null,
                'judul' => $item['judul_aktivitas_mahasiswa'] ?? $item['judul'] ?? null,
                'keterangan' => $item['keterangan_aktivitas_mahasiswa'] ?? $item['keterangan'] ?? null,
                'lokasi' => $item['lokasi_kegiatan'] ?? $item['lokasi'] ?? null,
                'sk_tugas' => $item['sk_tugas'] ?? null,
                'tanggal_sk_tugas' => $parseDateFn($item['tanggal_sk_tugas'] ?? null),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }
        return $records;
    }

    /**
     * Map AnggotaAktivitasMahasiswa API data to local DB records.
     */
    public static function mapAnggotaAktivitasMahasiswa(array $data): array
    {
        $records = [];
        foreach ($data as $item) {
            $idAktivitas = $item['id_aktivitas'] ?? $item['id_aktivitas_mahasiswa'] ?? null;

            $records[] = [
                'id_anggota' => $item['id_anggota'],
                'id_aktivitas' => $idAktivitas,
                'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                'nim' => $item['nim'],
                'nama_mahasiswa' => $item['nama_mahasiswa'],
                'id_peran_anggota' => $item['id_peran_anggota'],
                'nama_peran_anggota' => $item['nama_peran_anggota'],
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }
        return $records;
    }

    /**
     * Map KonversiKampusMerdeka API data to local DB records.
     */
    public static function mapKonversiKampusMerdeka(array $data): array
    {
        $records = [];
        foreach ($data as $item) {
            $records[] = [
                'id_konversi_aktivitas' => $item['id_konversi_aktivitas'],
                'id_matkul' => $item['id_matkul'],
                'nama_mata_kuliah' => $item['nama_mata_kuliah'],
                'sks_mata_kuliah' => $item['sks_mata_kuliah'],
                'nilai_angka' => $item['nilai_angka'],
                'nilai_huruf' => $item['nilai_huruf'],
                'nilai_indeks' => $item['nilai_indeks'],
                'id_semester' => $item['id_semester'],
                'id_aktivitas_mahasiswa' => $item['id_aktivitas_mahasiswa'] ?? null,
                'judul_aktivitas_mahasiswa' => $item['judul_aktivitas_mahasiswa'] ?? null,
                'id_anggota' => $item['id_anggota'] ?? null,
                'nim' => $item['nim'] ?? null,
                'nama_mahasiswa' => $item['nama_mahasiswa'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }
        return $records;
    }
}
