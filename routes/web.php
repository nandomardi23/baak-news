<?php

use App\Http\Controllers\Admin\AkademikController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\NeoFeederSettingsController;
use App\Http\Controllers\Admin\PejabatController;
use App\Http\Controllers\Admin\SuratController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Landing Pages (Public)
Route::get('/', [LandingController::class, 'index'])->name('landing.home');
Route::get('/profil', [LandingController::class, 'profile'])->name('landing.profile');
Route::get('/search', [LandingController::class, 'search'])->name('landing.search');
Route::get('/pengajuan/{mahasiswa}', [LandingController::class, 'form'])->name('landing.form');
Route::post('/pengajuan/{mahasiswa}', [LandingController::class, 'submit'])->name('landing.submit');
Route::get('/status/{mahasiswa}', [LandingController::class, 'status'])->name('landing.status');

// Self-Service Documents (Public)
Route::get('/dokumen/{mahasiswa}', [LandingController::class, 'dokumen'])->name('landing.dokumen');
Route::get('/dokumen/{mahasiswa}/krs/{tahunAkademik}/print', [LandingController::class, 'printKrs'])->name('landing.krs.print');
Route::get('/dokumen/{mahasiswa}/khs/{tahunAkademik}/print', [LandingController::class, 'printKhs'])->name('landing.khs.print');
Route::get('/dokumen/{mahasiswa}/kartu-ujian/{tahunAkademik}/print', [LandingController::class, 'printKartuUjian'])->name('landing.kartu_ujian.print');
Route::get('/dokumen/{mahasiswa}/transkrip/{jenis?}', [LandingController::class, 'printTranskrip'])->name('landing.transkrip.print');

// Kalender Akademik (Public)
Route::get('/kalender-akademik', [LandingController::class, 'kalender'])->name('landing.kalender');

// Auth routes
Route::get('dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Pejabat
    Route::resource('pejabat', PejabatController::class);
    Route::resource('jabatan', \App\Http\Controllers\Admin\JabatanController::class)->except(['create', 'show', 'edit']);

    // Mahasiswa
    Route::get('mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::get('mahasiswa/sync', [MahasiswaController::class, 'sync'])->name('mahasiswa.sync');
    Route::get('mahasiswa/export', [MahasiswaController::class, 'export'])->name('mahasiswa.export');
    Route::get('mahasiswa/kartu-ujian/batch', [MahasiswaController::class, 'batchKartuUjian'])->name('mahasiswa.kartu_ujian.index');
    Route::get('mahasiswa/kartu-ujian/batch/print', [MahasiswaController::class, 'printBatchKartuUjian'])->name('mahasiswa.kartu_ujian.batch');
    Route::get('mahasiswa/{mahasiswa}', [MahasiswaController::class, 'show'])->name('mahasiswa.show');
    Route::post('mahasiswa/{mahasiswa}/sync-krs', [MahasiswaController::class, 'syncKrs'])->name('mahasiswa.sync.krs');
    Route::patch('mahasiswa/{mahasiswa}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');
    Route::get('mahasiswa/{mahasiswa}/krs/{tahunAkademik}/print', [MahasiswaController::class, 'printKrs'])->name('mahasiswa.krs.print');
    Route::get('mahasiswa/{mahasiswa}/khs/{tahunAkademik}/print', [MahasiswaController::class, 'printKhs'])->name('mahasiswa.khs.print');
    Route::get('mahasiswa/{mahasiswa}/kartu-ujian/{tahunAkademik}/print', [MahasiswaController::class, 'printKartuUjian'])->name('mahasiswa.kartu_ujian.print');
    Route::get('mahasiswa/{mahasiswa}/transkrip/print', [MahasiswaController::class, 'printTranskrip'])->name('mahasiswa.transkrip.print');

    // Dosen
    Route::resource('dosen', \App\Http\Controllers\Admin\DosenController::class)->except(['create', 'show', 'edit']);
    Route::resource('kelas-kuliah', \App\Http\Controllers\Admin\KelasKuliahController::class)->only(['index', 'show', 'destroy']);

    // Surat Pengajuan
    Route::get('surat', [SuratController::class, 'index'])->name('surat.index');
    Route::get('surat/{surat}', [SuratController::class, 'show'])->name('surat.show');
    Route::post('surat/{surat}/approve', [SuratController::class, 'approve'])->name('surat.approve');
    Route::post('surat/{surat}/reject', [SuratController::class, 'reject'])->name('surat.reject');
    Route::get('surat/{surat}/print', [SuratController::class, 'print'])->name('surat.print');
    Route::delete('surat/{surat}', [SuratController::class, 'destroy'])->name('surat.destroy');
    Route::post('surat/bulk-approve', [SuratController::class, 'bulkApprove'])->name('surat.bulk-approve');
    Route::post('surat/bulk-reject', [SuratController::class, 'bulkReject'])->name('surat.bulk-reject');

    // User Management (Admin only)
    Route::resource('user', UserController::class)->middleware('role:admin');
    Route::post('role/import-neo', [\App\Http\Controllers\Admin\RoleController::class, 'importNeo'])->name('role.import-neo')->middleware('role:admin|staff_baak');
    Route::resource('role', \App\Http\Controllers\Admin\RoleController::class)->middleware('role:admin|staff_baak');
    Route::get('logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->middleware('role:admin')->name('logs.index');

    // Template Surat (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('templates', [\App\Http\Controllers\Admin\TemplateDesignerController::class, 'index'])->name('templates.index');
        Route::post('templates/pdf-upload', [\App\Http\Controllers\Admin\TemplateDesignerController::class, 'uploadPdf'])->name('templates.upload-pdf');
        Route::delete('templates/{template}', [\App\Http\Controllers\Admin\TemplateDesignerController::class, 'destroy'])->name('templates.destroy');
    });

    // Academic Data (Mata Kuliah, Semester, Prodi)
    Route::prefix('akademik')->name('akademik.')->group(function () {
        Route::resource('matakuliah', \App\Http\Controllers\Admin\MataKuliahController::class)->except(['create', 'edit', 'show']);
        Route::get('semester', [AkademikController::class, 'semester'])->name('semester');
        Route::resource('prodi', \App\Http\Controllers\Admin\ProdiController::class)->except(['create', 'edit']);
    });

    // Kalender Akademik
    Route::resource('kalender', \App\Http\Controllers\Admin\KalenderController::class)->except(['create', 'show', 'edit']);

    // Neo Feeder Settings (Admin only)
    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('neofeeder', [NeoFeederSettingsController::class, 'index'])->name('neofeeder');
        Route::post('neofeeder', [NeoFeederSettingsController::class, 'update'])->name('neofeeder.update');
        Route::post('neofeeder/test', [NeoFeederSettingsController::class, 'testConnection'])->name('neofeeder.test');
        
        // Letter Settings
        Route::get('surat', [\App\Http\Controllers\Admin\LetterSettingsController::class, 'show'])->name('surat');
        Route::post('surat', [\App\Http\Controllers\Admin\LetterSettingsController::class, 'update'])->name('surat.update');

        // General Web Settings
        Route::get('general', [\App\Http\Controllers\Admin\GeneralSettingsController::class, 'index'])->name('general');
        Route::post('general', [\App\Http\Controllers\Admin\GeneralSettingsController::class, 'update'])->name('general.update');
    });

    // Sync routes (AJAX)
    Route::middleware('role:admin|staff_baak')->prefix('sync')->name('sync.')->group(function () {
        Route::post('referensi', [\App\Http\Controllers\Admin\SyncController::class, 'syncReferensi'])->name('referensi');
        Route::post('prodi', [\App\Http\Controllers\Admin\SyncController::class, 'syncProdi'])->name('prodi');
        Route::post('semester', [\App\Http\Controllers\Admin\SyncController::class, 'syncSemester'])->name('semester');
        Route::post('kurikulum', [\App\Http\Controllers\Admin\SyncController::class, 'syncKurikulum'])->name('kurikulum');
        Route::post('matakuliah', [\App\Http\Controllers\Admin\SyncController::class, 'syncMataKuliah'])->name('matakuliah');
        Route::post('mahasiswa', [\App\Http\Controllers\Admin\SyncController::class, 'syncMahasiswa'])->name('mahasiswa');
        Route::post('biodata', [\App\Http\Controllers\Admin\SyncController::class, 'syncBiodata'])->name('biodata');
        Route::post('mahasiswa/detail', [\App\Http\Controllers\Admin\SyncController::class, 'syncMahasiswaDetail'])->name('mahasiswa-detail');
        Route::post('dosen', [\App\Http\Controllers\Admin\SyncController::class, 'syncDosen'])->name('dosen');
        Route::post('nilai', [\App\Http\Controllers\Admin\SyncController::class, 'syncNilai'])->name('nilai');
        Route::post('krs', [\App\Http\Controllers\Admin\SyncController::class, 'syncKrs'])->name('krs');
        Route::post('aktivitas', [\App\Http\Controllers\Admin\SyncController::class, 'syncAktivitasKuliah'])->name('aktivitas');
        Route::post('kelas-kuliah', [\App\Http\Controllers\Admin\SyncController::class, 'syncKelasKuliah'])->name('kelas-kuliah');
        Route::post('dosen-pengajar', [\App\Http\Controllers\Admin\SyncController::class, 'syncDosenPengajar'])->name('dosen-pengajar');
        Route::post('ajar-dosen', [\App\Http\Controllers\Admin\SyncController::class, 'syncAjarDosen'])->name('ajar-dosen');
        Route::post('bimbingan-mahasiswa', [\App\Http\Controllers\Admin\SyncController::class, 'syncBimbinganMahasiswa'])->name('bimbingan-mahasiswa');
        Route::post('uji-mahasiswa', [\App\Http\Controllers\Admin\SyncController::class, 'syncUjiMahasiswa'])->name('uji-mahasiswa');
        Route::post('aktivitas-mahasiswa', [\App\Http\Controllers\Admin\SyncController::class, 'syncAktivitasMahasiswa'])->name('aktivitas-mahasiswa');
        Route::post('anggota-aktivitas-mahasiswa', [\App\Http\Controllers\Admin\SyncController::class, 'syncAnggotaAktivitasMahasiswa'])->name('anggota-aktivitas-mahasiswa');
        Route::post('konversi-kampus-merdeka', [\App\Http\Controllers\Admin\SyncController::class, 'syncKonversiKampusMerdeka'])->name('konversi-kampus-merdeka');
    });
});

require __DIR__.'/settings.php';


