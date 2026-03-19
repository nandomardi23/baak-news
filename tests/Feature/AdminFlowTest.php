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

it('can update dosen wali for mahasiswa', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('admin');

    $mahasiswa = Mahasiswa::factory()->create();
    $dosen = \App\Models\Dosen::first() ?? \App\Models\Dosen::create([
        'id_dosen' => 'test-uuid-dosen-123',
        'nidn' => '1122334455',
        'nama' => 'Dosen Test',
        'id_status_aktif' => 1,
    ]);

    $response = $this->actingAs($user)
        ->patch("/admin/mahasiswa/{$mahasiswa->id}", [
            'dosen_wali_id' => $dosen->id,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('mahasiswa', [
        'id' => $mahasiswa->id,
        'dosen_wali_id' => $dosen->id,
    ]);
});

it('can access create mahasiswa page', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get('/admin/mahasiswa/create');
    $response->assertStatus(200);
});

it('can store new mahasiswa manually', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $prodi = \App\Models\ProgramStudi::factory()->create();

    $data = [
        'nim' => '2024TEST001',
        'nama' => 'Test Mahasiswa Baru',
        'program_studi_id' => $prodi->id,
        'angkatan' => '2024',
        'status_mahasiswa' => 'A',
    ];

    $response = $this->actingAs($user)->post('/admin/mahasiswa', $data);
    $response->assertRedirect(route('admin.mahasiswa.index'));
    
    $this->assertDatabaseHas('mahasiswa', [
        'nim' => '2024TEST001',
        'nama' => 'Test Mahasiswa Baru',
        'nama_mahasiswa' => 'Test Mahasiswa Baru',
    ]);
});

it('can access edit mahasiswa page', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $mahasiswa = Mahasiswa::factory()->create();

    $response = $this->actingAs($user)->get("/admin/mahasiswa/{$mahasiswa->id}/edit");
    $response->assertStatus(200);
});

it('can delete mahasiswa', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $mahasiswa = Mahasiswa::factory()->create();

    $response = $this->actingAs($user)->delete("/admin/mahasiswa/{$mahasiswa->id}");
    $response->assertRedirect(route('admin.mahasiswa.index'));
    
    $this->assertDatabaseMissing('mahasiswa', [
        'id' => $mahasiswa->id,
    ]);
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
