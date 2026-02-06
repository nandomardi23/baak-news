<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::withCount('users')->orderBy('name')->get();

        return Inertia::render('Admin/Role/Index', [
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Role/Form', [
            'permissions' => $this->getGroupedPermissions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);
        
        if (!empty($request->permissions)) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.role.index')
            ->with('success', 'Role berhasil dibuat');
    }

    public function edit(Role $role): Response
    {
        $role->load('permissions');

        return Inertia::render('Admin/Role/Form', [
            'role' => $role,
            'currentPermissions' => $role->permissions->pluck('name'),
            'permissions' => $this->getGroupedPermissions(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $request->name]);
        
        if (isset($request->permissions)) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.role.index')
            ->with('success', 'Role berhasil diperbarui');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'admin') {
            return back()->with('error', 'Role admin tidak dapat dihapus');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Role masih digunakan oleh user, tidak dapat dihapus');
        }

        $role->delete();

        return redirect()->route('admin.role.index')
            ->with('success', 'Role berhasil dihapus');
    }

    public function importNeo(): RedirectResponse
    {
        $roles = [
            'Dosen',
            'Mahasiswa',
            'Tenaga Kependidikan',
            'Administrator PT',
            'Kaprodi',
        ];

        $count = 0;
        foreach ($roles as $roleName) {
            // Using firstOrCreate to avoid duplicates
            if (Role::where('name', $roleName)->doesntExist()) {
                Role::create(['name' => $roleName]);
                $count++;
            }
        }

        if ($count > 0) {
            return back()->with('success', "$count Role Neo berhasil diimport.");
        }
        
        return back()->with('info', "Semua Role Neo sudah ada.");
    }

    private function getGroupedPermissions(): array
    {
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            // Check for known feature prefixes
            $parts = explode(' ', $permission->name);
            $feature = count($parts) > 1 ? end($parts) : 'other';
            
            // Map common suffixes to readable group names if needed
            $groupName = ucfirst($feature);
            if ($permission->name === 'view dashboard') $groupName = 'Dashboard';
            if ($permission->name === 'manage users') $groupName = 'User Management';

            $grouped[$groupName][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'label' => ucwords($permission->name),
            ];
        }

        ksort($grouped);
        return $grouped;
    }
}
