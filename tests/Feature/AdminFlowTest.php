<?php

use App\Models\Mahasiswa;
use App\Models\SuratPengajuan;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create admin role for test users
    Role::findOrCreate('admin', 'web');
});

/*
|--------------------------------------------------------------------------
| Admin Dashboard & Protected Routes Tests
|--------------------------------------------------------------------------
*/

it('redirects unauthenticated users from admin', function () {
    $response = $this->get('/admin');
    $response->assertRedirect('/login');
});

it('blocks users without admin role', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');
    $response->assertStatus(403);
});

it('allows admin users to access admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get('/admin');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
});

it('allows admin users to view mahasiswa list', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    Mahasiswa::factory()->count(5)->create();

    $response = $this->actingAs($user)->get('/admin/mahasiswa');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Mahasiswa/Index')
        ->has('mahasiswa')
    );
});

it('allows admin users to view mahasiswa detail', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $mahasiswa = Mahasiswa::factory()->create();

    $response = $this->actingAs($user)->get("/admin/mahasiswa/{$mahasiswa->id}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Mahasiswa/Show')
        ->has('mahasiswa')
    );
});

it('allows admin users to view surat list', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
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
    $user->assignRole('admin');
    $mahasiswa = Mahasiswa::factory()->create();

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
