<?php

use App\Models\Mahasiswa;
use App\Models\SuratPengajuan;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Admin Dashboard & Protected Routes Tests
|--------------------------------------------------------------------------
*/

it('redirects unauthenticated users from admin', function () {
    $response = $this->get('/admin');
    $response->assertRedirect('/login');
});

it('allows authenticated users to access admin dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
});

it('allows authenticated users to view mahasiswa list', function () {
    $user = User::factory()->create();
    Mahasiswa::factory()->count(5)->create();

    $response = $this->actingAs($user)->get('/admin/mahasiswa');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Mahasiswa/Index')
        ->has('mahasiswa')
    );
});

it('allows authenticated users to view mahasiswa detail', function () {
    $user = User::factory()->create();
    $mahasiswa = Mahasiswa::factory()->create();

    $response = $this->actingAs($user)->get("/admin/mahasiswa/{$mahasiswa->id}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Mahasiswa/Show')
        ->has('mahasiswa')
    );
});

it('allows authenticated users to view surat list', function () {
    $user = User::factory()->create();
    SuratPengajuan::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/admin/surat');
    $response->assertStatus(200);
});

/*
|--------------------------------------------------------------------------
| Flash Message Tests
|--------------------------------------------------------------------------
*/

it('shares flash messages to Inertia', function () {
    $user = User::factory()->create();
    $mahasiswa = Mahasiswa::factory()->create();

    // Update dosen wali should flash success and share via Inertia
    $dosen = \App\Models\Dosen::create([
        'id_dosen' => 'test-dosen-001',
        'nama' => 'Dr. Test Dosen',
    ]);

    $response = $this->actingAs($user)
        ->patch("/admin/mahasiswa/{$mahasiswa->id}", [
            'dosen_wali_id' => $dosen->id,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
});
