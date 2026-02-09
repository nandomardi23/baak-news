<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'view dashboard',
            
            // Pejabat
            'view pejabat',
            'create pejabat',
            'edit pejabat',
            'delete pejabat',
            
            // Mahasiswa
            'view mahasiswa',
            'sync mahasiswa',
            'print krs',
            'print khs',
            'print transkrip',
            'print kartu ujian',
            
            // Surat
            'view surat',
            'approve surat',
            'reject surat',
            'print surat',
            'delete surat',
            
            // Templates
            'manage templates',

            // User Management
            'manage users',
            'manage roles',

            // Dosen
            'view dosen',
            'sync dosen',

            // Akademik
            'view prodi',
            'sync prodi',
            'view semester',
            'sync semester',
            'view matakuliah',
            'sync matakuliah',
            'view kelas kuliah',
            'sync kelas kuliah',
            'view nilai',
            'sync nilai',
            'view aktivitas kuliah',
            'sync aktivitas kuliah',

            // Kalender Akademik
            'view kalender',
            'create kalender',
            'edit kalender',
            'delete kalender',

            // Settings
            'view settings',
            'manage settings',
            'view neofeeder',
            'manage neofeeder',
            
            // Logs
            'view activity logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $staffRole = Role::firstOrCreate(['name' => 'staff_baak']);
        $staffRole->givePermissionTo([
            'view dashboard',
            'view pejabat',
            'view mahasiswa',
            'print krs',
            'print khs',
            'print transkrip',
            'print kartu ujian',
            
            // Surat & Templates
            'view surat',
            'approve surat',
            'reject surat',
            'print surat',
            'manage templates',
            
            // Dosen
            'view dosen',
            'sync dosen',

            // Akademik
            'view prodi',
            'sync prodi',
            'view semester',
            'sync semester',
            'view matakuliah',
            'sync matakuliah',
            'view kelas kuliah',
            'sync kelas kuliah',
            'view nilai',
            'sync nilai',
            'view aktivitas kuliah',
            'sync aktivitas kuliah',

            // Kalender
            'view kalender',
            
            // Settings - View only
            'view settings',
        ]);

        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('admin');

        // Create staff user
        $staff = User::firstOrCreate(
            ['email' => 'baak@example.com'],
            [
                'name' => 'Staff BAAK',
                'password' => bcrypt('password'),
            ]
        );
        $staff->assignRole('staff_baak');
    }
}
