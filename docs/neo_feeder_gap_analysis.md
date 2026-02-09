# Neo Feeder Sync Gap Analysis & Estimation

## Current Implementation Status
The project currently has a robust synchronization engine for the **Core Academic Data**.

| Module | Status | Notes |
| :--- | :--- | :--- |
| **Mahasiswa** | ✅ Implemented | Includes Biodata, List, Detail |
| **Dosen** | ✅ Implemented | Includes List, Penugasan per Kelas |
| **Prodi** | ✅ Implemented | Basic data |
| **Semester** | ✅ Implemented | Basic data |
| **Mata Kuliah** | ✅ Implemented | List of courses |
| **Kelas Kuliah** | ✅ Implemented | Classes offered per semester |
| **KRS (Study Plan)** | ✅ Implemented | Student course enrollment |
| **Nilai (Grades)** | ✅ Implemented | Student grades per semester |
| **Aktivitas Kuliah** | ⚠️ Partial | Basic status sync exists, but full AKM details (IPS/IPK specific logic) may need refinement |

## Missing Components (Gap Analysis)
To achieve **100% Data Synchronization** with Neo Feeder (PDDIKTI), the following modules are typically required but currently missing:

### 1. Kurikulum (Curriculum Management) - Medium Complexity
*   **Data**: `Kurikulum`, `MatkulKurikulum` (Mapping courses to curriculum versions).
*   **Why needed**: Essential to know *which* curriculum a student is following (e.g., 2019 vs 2021) and validate graduation requirements.
*   **Effort**: ~1 Day.

### 2. Skala Nilai (Grading Scale) - Low Complexity
*   **Data**: `SkalaNilai` (A, B, C range definitions).
*   **Why needed**: To validate if a grade 'A' is actually valid for a specific prodi/period.
*   **Effort**: ~2-3 Hours.

### 3. Kelulusan & Drop Out (Graduation/DO) - Medium Complexity
*   **Data**: `MahasiswaLulusDO`.
*   **Why needed**: Reporting graduation is a critical Feeder function. Requires specific validation rules (SK numbers, dates).
*   **Effort**: ~1 Day.

### 4. Aktivitas Mengajar Dosen (Real Teaching) - Medium Complexity
*   **Data**: `AjarDosen` (Real teaching hours, not just class assignment).
*   **Why needed**: For reporting BKD (Beban Kerja Dosen) and Sister integration.
*   **Effort**: ~4-6 Hours.

### 5. Bimbingan Mahasiswa (Guidance) - Medium Complexity
*   **Data**: `BimbinganMahasiswa` (Thesis/Academic Advisors).
*   **Why needed**: Recording final project supervisors.
*   **Effort**: ~4-6 Hours.

### 6. Prestasi Mahasiswa (Achievements) - Low Complexity
*   **Data**: `PrestasiMahasiswa`.
*   **Why needed**: Student portfolio data.
*   **Effort**: ~2-4 Hours.

### 7. Kampus Merdeka (MBKM) - High Complexity
*   **Data**: `KampusMerdeka`, `AnggotaAktivitas`, `KonversiKampusMerdeka`.
*   **Why needed**: Mandatory for modern accreditation reporting. This is a large module with complex rules.
*   **Effort**: ~2-3 Days.

### 8. Substansi Kuliah & Prasyarat - Low Complexity
*   **Data**: `SubstansiKuliah`, `MatkulPrasyarat`.
*   **Effort**: ~2-4 Hours.

## Time Estimation

### Scenario A: Backend Logic Only (Sync Data into DB, no UI)
*   **Estimated Days**: 3-5 Working Days.
*   **Scope**: Creating Models, Service Methods, Sync Logic, and simple database tables.

### Scenario B: Full Implementation (Backend + Basic Admin UI)
*   **Estimated Days**: 7-10 Working Days (1-2 Weeks).
*   **Scope**: A above + simple CRUD pages to view the data and spot-check sync results.

### Recommendation
Prioritize **Kurikulum** and **Kelulusan** first, as these are critical for ensuring student data validity. MBKM can be a separate phase.
