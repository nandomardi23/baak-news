<?php

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\SuratPengajuan;
use App\Models\TahunAkademik;

/*
|--------------------------------------------------------------------------
| Landing Page Tests
|--------------------------------------------------------------------------
*/

it('shows the home page', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Landing/Home'));
});

it('shows the profile page', function () {
    $response = $this->get('/profil');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Landing/Profile'));
});

it('requires minimum 3 characters for search', function () {
    $response = $this->get('/search?search=ab');
    $response->assertStatus(302); // Redirect back with validation error
});

it('searches mahasiswa by name or NIM', function () {
    $mahasiswa = Mahasiswa::factory()->create([
        'nama' => 'Budi Santoso',
        'nim' => '2024001001',
        'status_mahasiswa' => 'A',
    ]);

    $response = $this->get('/search?search=Budi');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Landing/SearchResult')
        ->has('mahasiswa', 1)
    );
});

/*
|--------------------------------------------------------------------------
| Self-Service Document Tests
|--------------------------------------------------------------------------
*/

it('shows dokumen page for mahasiswa', function () {
    $mahasiswa = Mahasiswa::factory()->create();

    $response = $this->get("/dokumen/{$mahasiswa->id}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Landing/Dokumen')
        ->has('mahasiswa')
        ->has('semesters')
    );
});

/*
|--------------------------------------------------------------------------
| Surat Pengajuan Flow Tests
|--------------------------------------------------------------------------
*/

it('shows the pengajuan form page', function () {
    $mahasiswa = Mahasiswa::factory()->create();
    TahunAkademik::factory()->count(3)->create();

    $response = $this->get("/pengajuan/{$mahasiswa->id}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Landing/FormPengajuan')
        ->has('mahasiswa')
        ->has('semesters')
    );
});

it('can submit surat aktif kuliah', function () {
    $mahasiswa = Mahasiswa::factory()->create();

    $response = $this->post("/pengajuan/{$mahasiswa->id}", [
        'jenis_surat' => 'aktif_kuliah',
        'keperluan' => 'Untuk mengurus beasiswa',
        'nama' => $mahasiswa->nama,
    ]);

    $response->assertRedirect(route('landing.status', $mahasiswa->id));
    $this->assertDatabaseHas('surat_pengajuan', [
        'mahasiswa_id' => $mahasiswa->id,
        'jenis_surat' => 'aktif_kuliah',
        'status' => 'pending',
    ]);
});

it('validates required fields on submit', function () {
    $mahasiswa = Mahasiswa::factory()->create();

    $response = $this->post("/pengajuan/{$mahasiswa->id}", [
        // Missing required fields
    ]);

    $response->assertSessionHasErrors(['jenis_surat', 'nama']);
});

it('shows the status page with pengajuan history', function () {
    $mahasiswa = Mahasiswa::factory()->create();
    SuratPengajuan::factory()->count(3)->create([
        'mahasiswa_id' => $mahasiswa->id,
    ]);

    $response = $this->get("/status/{$mahasiswa->id}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Landing/Status')
        ->has('pengajuan', 3)
    );
});

/*
|--------------------------------------------------------------------------
| Kalender Akademik Tests
|--------------------------------------------------------------------------
*/

it('shows the academic calendar page', function () {
    $response = $this->get('/kalender-akademik');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Landing/Kalender'));
});

/*
|--------------------------------------------------------------------------
| Model Tests
|--------------------------------------------------------------------------
*/

it('generates correct jenis surat label', function () {
    $surat = SuratPengajuan::factory()->create([
        'jenis_surat' => 'aktif_kuliah',
    ]);

    expect($surat->jenis_surat_label)->toBe('Surat Aktif Kuliah');
});

it('generates correct status label', function () {
    $surat = SuratPengajuan::factory()->create([
        'status' => 'pending',
    ]);

    expect($surat->status_label)->toBe('Menunggu');
});

it('can approve a surat pengajuan', function () {
    $user = \App\Models\User::factory()->create();
    $surat = SuratPengajuan::factory()->pending()->create();

    $surat->approve($user->id);

    expect($surat->fresh()->status)->toBe('approved');
    expect($surat->fresh()->processed_by)->toBe($user->id);
    expect($surat->fresh()->nomor_surat)->not->toBeNull();
});

it('can reject a surat pengajuan with catatan', function () {
    $user = \App\Models\User::factory()->create();
    $surat = SuratPengajuan::factory()->pending()->create();

    $surat->reject($user->id, 'Data tidak lengkap');

    expect($surat->fresh()->status)->toBe('rejected');
    expect($surat->fresh()->catatan)->toBe('Data tidak lengkap');
});

it('generates correct mahasiswa status text', function () {
    $mahasiswa = Mahasiswa::factory()->create(['status_mahasiswa' => 'A']);
    expect($mahasiswa->status_text)->toBe('Aktif');

    $mahasiswa2 = Mahasiswa::factory()->create(['status_mahasiswa' => 'L']);
    expect($mahasiswa2->status_text)->toBe('Lulus');

    $mahasiswa3 = Mahasiswa::factory()->create(['status_mahasiswa' => 'C']);
    expect($mahasiswa3->status_text)->toBe('Cuti');
});

it('generates correct TTL attribute', function () {
    $mahasiswa = Mahasiswa::factory()->create([
        'tempat_lahir' => 'Jakarta',
        'tanggal_lahir' => '2000-05-15',
    ]);

    expect($mahasiswa->ttl)->toContain('Jakarta');
    expect($mahasiswa->ttl)->toContain('Mei');
    expect($mahasiswa->ttl)->toContain('2000');
});
