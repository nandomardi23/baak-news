# Neo Feeder Full Synchronization Roadmap

## Phase 1: Reference Data & Fundamentals (The Foundation)
- [ ] **Reference Data Sync** <!-- id: 0 -->
    - [x] Create Migration for Reference Tables (`ref_agama`, `ref_wilayah`, etc.) <!-- id: 29 -->
    - [x] Create `Reference` Model (Generic or specific models) <!-- id: 30 -->
    - [x] Create `ReferenceSyncService` <!-- id: 1 -->
    - [x] Implements `syncRefAgama`, `syncRefWilayah`, etc. <!-- id: 2 -->
- [ ] **Curriculum Management (Kurikulum)** <!-- id: 3 -->
    - [x] Create `Kurikulum` & `MatkulKurikulum` Models & Migrations <!-- id: 4 -->
    - [x] Implement `GetListKurikulum` & `GetMatkulKurikulum` in `NeoFeederService` <!-- id: 5 -->
    - [x] Create sync logic in `NeoFeederSyncService` <!-- id: 6 -->
- [ ] **Grading System (Skala Nilai)** <!-- id: 7 -->
    - [x] Create `SkalaNilai` Model & Migration <!-- id: 8 -->
    - [x] Implement `GetListSkalaNilaiProdi` <!-- id: 9 -->
    - [x] Sync logic for Grading Scales <!-- id: 10 -->

## Phase 2: Graduation & Student History (Critical Reporting)
- [ ] **Graduation & Drop Out (Kelulusan)** <!-- id: 11 -->
    - [x] Create `MahasiswaLulusDO` Model or update `Mahasiswa` table <!-- id: 12 -->
    - [x] Implement `GetListMahasiswaLulusDO` & `Insert/Update/Delete` logic <!-- id: 13 -->
- [ ] **Student History (Riwayat Pendidikan)** <!-- id: 14 -->
    - [x] Enhance `Mahasiswa` sync to capture historical changes (Mutasi/Pindahan) using `GetListRiwayatPendidikanMahasiswa` <!-- id: 15 -->

## Phase 3: Lecturer Workload & Activities (BKD/Sister Support)
- [ ] **Teaching Activity (Ajar Dosen)** <!-- id: 16 -->
    - [x] Create `AjarDosen` Model (for real teaching hours) <!-- id: 17 -->
    - [x] Implement `GetAktivitasMengajarDosen` <!-- id: 18 -->
- [ ] **Guidance & Thesis (Bimbingan)** <!-- id: 19 -->
    - [x] Create `BimbinganMahasiswa` & `UjiMahasiswa` Models <!-- id: 20 -->
    - [x] Implement `GetListBimbingMahasiswa` & `GetListUjiMahasiswa` <!-- id: 21 -->

## Phase 4: Kampus Merdeka (MBKM) & Student Activities
- [ ] **Aktivitas Mahasiswa (Non-Class)** <!-- id: 22 -->
    - [x] Create `AktivitasMahasiswa` & `AnggotaAktivitas` Models <!-- id: 23 -->
    - [x] Implement `GetListAktivitasMahasiswa` & related endpoints <!-- id: 24 -->
- [ ] **MBKM Conversion** <!-- id: 25 -->
    - [x] Implement `GetListKonversiKampusMerdeka` <!-- id: 26 -->

## Phase 5: Admin UI & Automation
- [x] Create Admin Dashboard for Sync Status <!-- id: 27 -->
- [x] Setup Scheduler for Automatic Sync (Nightly) <!-- id: 28 -->
