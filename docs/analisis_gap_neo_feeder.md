# Analisis Kesenjangan (Gap Analysis) & Estimasi Sync Neo Feeder

## Status Implementasi Saat Ini
Proyek saat ini sudah memiliki mesin sinkronisasi yang kuat untuk **Data Akademik Inti**.

| Modul | Status | Catatan |
| :--- | :--- | :--- |
| **Mahasiswa** | ✅ Selesai | Termasuk Biodata, List, Detail |
| **Dosen** | ✅ Selesai | Termasuk List, Penugasan per Kelas |
| **Prodi** | ✅ Selesai | Data dasar |
| **Semester** | ✅ Selesai | Data dasar |
| **Mata Kuliah** | ✅ Selesai | Daftar mata kuliah |
| **Kelas Kuliah** | ✅ Selesai | Kelas yang dibuka per semester |
| **KRS (Kartu Rencana Studi)** | ✅ Selesai | Pengambilan mata kuliah mahasiswa |
| **Nilai** | ✅ Selesai | Nilai mahasiswa per semester |
| **Aktivitas Kuliah (AKM)** | ⚠️ Sebagian | Status dasar ada, tapi detail lengkap (logic IPS/IPK khusus) mungkin butuh perbaikan |

## Komponen yang Hilang (Gap Analysis)
Untuk mencapai **Sinkronisasi Data 100%** dengan Neo Feeder (PDDIKTI), modul-modul berikut biasanya diperlukan namun saat ini belum ada:

### 1. Kurikulum (Manajemen Kurikulum) - Kompleksitas: Sedang
*   **Data**: `Kurikulum`, `MatkulKurikulum` (Pemetaan mata kuliah ke versi kurikulum).
*   **Mengapa perlu**: Penting untuk mengetahui kurikulum *mana* yang diikuti mahasiswa (misal: 2019 vs 2021) dan memvalidasi syarat kelulusan.
*   **Estimasi**: ~1 Hari.

### 2. Skala Nilai - Kompleksitas: Rendah
*   **Data**: `SkalaNilai` (Definisi rentang nilai A, B, C).
*   **Mengapa perlu**: Untuk memvalidasi apakah nilai 'A' valid untuk prodi/periode tertentu.
*   **Estimasi**: ~2-3 Jam.

### 3. Kelulusan & Drop Out (Lulus/DO) - Kompleksitas: Sedang
*   **Data**: `MahasiswaLulusDO`.
*   **Mengapa perlu**: Melaporkan mahasiswa lulus adalah fungsi Feeder yang krusial. Memerlukan aturan validasi khusus (Nomor SK, tanggal).
*   **Estimasi**: ~1 Hari.

### 4. Aktivitas Mengajar Dosen (Real Teaching) - Kompleksitas: Sedang
*   **Data**: `AjarDosen` (Jam mengajar riil, bukan hanya penugasan kelas).
*   **Mengapa perlu**: Untuk pelaporan BKD (Beban Kerja Dosen) dan integrasi Sister.
*   **Estimasi**: ~4-6 Jam.

### 5. Bimbingan Mahasiswa - Kompleksitas: Sedang
*   **Data**: `BimbinganMahasiswa` (Pembimbing Skripsi/Akademik).
*   **Mengapa perlu**: Mencatat pembimbing tugas akhir.
*   **Estimasi**: ~4-6 Jam.

### 6. Prestasi Mahasiswa - Kompleksitas: Rendah
*   **Data**: `PrestasiMahasiswa`.
*   **Mengapa perlu**: Data portofolio mahasiswa.
*   **Estimasi**: ~2-4 Jam.

### 7. Kampus Merdeka (MBKM) - Kompleksitas: Tinggi
*   **Data**: `KampusMerdeka`, `AnggotaAktivitas`, `KonversiKampusMerdeka`.
*   **Mengapa perlu**: Wajib untuk pelaporan akreditasi modern. Ini adalah modul besar dengan aturan yang kompleks.
*   **Estimasi**: ~2-3 Hari.

### 8. Substansi Kuliah & Prasyarat - Kompleksitas: Rendah
*   **Data**: `SubstansiKuliah`, `MatkulPrasyarat`.
*   **Estimasi**: ~2-4 Jam.

## Estimasi Waktu

### Skenario A: Logika Backend Saja (Sync Data ke DB, tanpa UI)
*   **Estimasi Waktu**: 3-5 Hari Kerja.
*   **Lingkup**: Membuat Model, Metode Service, Logika Sync, dan tabel database sederhana.

### Skenario B: Implementasi Penuh (Backend + UI Admin Dasar)
*   **Estimasi Waktu**: 7-10 Hari Kerja (1-2 Minggu).
*   **Lingkup**: Sama seperti A + halaman CRUD sederhana untuk melihat data dan memeriksa hasil sync secara manual.

### Rekomendasi
Prioritaskan **Kurikulum** dan **Kelulusan** terlebih dahulu, karena ini sangat penting untuk memastikan validitas data mahasiswa. MBKM bisa dijadikan fase terpisah.
