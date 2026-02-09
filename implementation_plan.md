# Neo Feeder Sync Implementation Plan - Phase 1

## Goal Description
Implement the **Critical Academic Data** synchronization modules: **Reference Data**, **Kurikulum**, and **Skala Nilai**. These are foundational for validating student records and graduation reporting.

## User Review Required
> [!IMPORTANT]
> This plan involves creating new database tables. Please ensure you run migrations after approval.

## Proposed Changes

### 1. Reference Data (New Component)
Create a dedicated service to handle static dictionary data.
#### [NEW] [ReferenceSyncService.php](file:///c:/Users/Nando/Documents/belajar/baak-news/app/Services/ReferenceSyncService.php)
- Methods for: `syncAgama`, `syncWilayah`, `syncBentukPendidikan`, `syncAlatTransportasi`, etc.
- Will store data in simple dictionary tables or dedicated reference tables if needed.

### 2. Kurikulum (New Component)
Manage curriculum versions and their courses.
#### [NEW] [Kurikulum.php](file:///c:/Users/Nando/Documents/belajar/baak-news/app/Models/Kurikulum.php) & [MatkulKurikulum.php](file:///c:/Users/Nando/Documents/belajar/baak-news/app/Models/MatkulKurikulum.php)
- Models to store curriculum data.
#### [NEW] [Migration Files](file:///c:/Users/Nando/Documents/belajar/baak-news/database/migrations/)
- `create_kurikulum_table`
- `create_matkul_kurikulum_table`
#### [MODIFY] [NeoFeederService.php](file:///c:/Users/Nando/Documents/belajar/baak-news/app/Services/NeoFeederService.php)
- Add: `getKurikulum`, `getMatkulKurikulum`.
#### [MODIFY] [NeoFeederSyncService.php](file:///c:/Users/Nando/Documents/belajar/baak-news/app/Services/NeoFeederSyncService.php)
- Add: `syncKurikulum`, `syncMatkulKurikulum`.

### 3. Skala Nilai (New Component)
Manage grading scales per study program.
#### [NEW] [SkalaNilai.php](file:///c:/Users/Nando/Documents/belajar/baak-news/app/Models/SkalaNilai.php)
- Model for grading scales.
#### [NEW] [Migration File](file:///c:/Users/Nando/Documents/belajar/baak-news/database/migrations/)
- `create_skala_nilai_table`
#### [MODIFY] [NeoFeederService.php](file:///c:/Users/Nando/Documents/belajar/baak-news/app/Services/NeoFeederService.php)
- Add: `getSkalaNilai`.
#### [MODIFY] [NeoFeederSyncService.php](file:///c:/Users/Nando/Documents/belajar/baak-news/app/Services/NeoFeederSyncService.php)
- Add: `syncSkalaNilai`.

### 4. Controller Updates
Expose new sync methods to the Admin API.
#### [MODIFY] [SyncController.php](file:///c:/Users/Nando/Documents/belajar/baak-news/app/Http/Controllers/Admin/SyncController.php)
- Add endpoints: `syncReferensi`, `syncKurikulum`, `syncSkalaNilai`.

## Verification Plan

### Automated Verification
- Create a test script `debug_phase1_sync.php` to run the new sync methods and verify data insertion.

### Manual Verification
- Check database tables (`ref_x`, `kurikulum`, `skala_nilai`) for populated data.
- Verify `GetListKurikulum` correctly maps courses to the curriculum.
