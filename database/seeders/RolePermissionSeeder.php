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
            
            // Surat
            'view surat',
            'approve surat',
            'reject surat',
            'print surat',
            'delete surat',

            // User Management
            'manage users',
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
            'view surat',
            'approve surat',
            'reject surat',
            'print surat',
        ]);

        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@stikesht-tpi.ac.id'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('admin');

        // Create staff user
        $staff = User::firstOrCreate(
            ['email' => 'baak@stikesht-tpi.ac.id'],
            [
                'name' => 'Staff BAAK',
                'password' => bcrypt('password'),
            ]
        );
        $staff->assignRole('staff_baak');
    }
}
