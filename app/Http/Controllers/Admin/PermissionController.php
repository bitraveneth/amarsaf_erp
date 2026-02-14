<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\Role;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureSuperAdmin();

        $permissions = Permission::orderBy('group')->orderBy('name')->get();

        $groups = Permission::selectRaw("`group` as group_key, COALESCE(`group`, 'Other') as label, COUNT(*) as total")
            ->groupBy('group_key', 'label')
            ->orderBy('label')
            ->get();

        // Permissions grouped by module label for easier per-role display
        $groupedPermissions = $permissions->groupBy(function ($perm) {
            return $perm->group ?? 'Other';
        });

        $roles = Role::orderBy('label')->pluck('label', 'key')->all();

        // Fallback to legacy roles if roles table is empty
        if (empty($roles)) {
            $roles = [
                'super_admin'        => 'Super admin',
                'admin'              => 'Admin',
                'warehouse_manager'  => 'Warehouse manager',
                'production_manager' => 'Production manager',
                'sales_manager'      => 'Sales manager',
                'qc_officer'         => 'QC officer',
                'employee'           => 'Field / office employee',
            ];
        }

        $rolePermissions = RolePermission::all()
            ->groupBy('role')
            ->map(function ($rows) {
                return $rows->pluck('permission_name')->all();
            });
        
        $selectedRoleKey   = $request->input('role');
        $selectedRoleLabel = $selectedRoleKey && isset($roles[$selectedRoleKey]) ? $roles[$selectedRoleKey] : null;
        $selectedPermissionNames = $selectedRoleKey && isset($rolePermissions[$selectedRoleKey])
            ? $rolePermissions[$selectedRoleKey]
            : [];

        return view('admin.permissions.index', compact(
            'permissions',
            'roles',
            'rolePermissions',
            'groups',
            'groupedPermissions',
            'selectedRoleKey',
            'selectedRoleLabel',
            'selectedPermissionNames'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'name'  => 'required|string|max:100|alpha_dash|unique:permissions,name',
            'label' => 'required|string|max:255',
            'group' => 'nullable|string|max:100',
        ]);

        Permission::create($data);

        return redirect()->route('admin.permissions.index')
            ->with('status', 'Permission created.');
    }

    public function updateRoles(Request $request)
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'role_permissions' => 'array',
        ]);

        $rolePermissions = $data['role_permissions'] ?? [];

        // Replace mappings per role
        foreach ($rolePermissions as $role => $permissionNames) {
            RolePermission::where('role', $role)->delete();

            foreach ($permissionNames as $permissionName) {
                RolePermission::create([
                    'role'            => $role,
                    'permission_name' => $permissionName,
                ]);
            }
        }

        return redirect()->route('admin.permissions.index')
            ->with('status', 'Role permissions updated.');
    }

    /**
     * Update permissions for a single role from the grouped UI.
     */
    public function updateRole(Request $request, string $role)
    {
        $this->ensureSuperAdmin();

        // Super admin is treated as having all permissions; nothing to update.
        if ($role === 'super_admin') {
            return redirect()
                ->route('admin.permissions.index', ['role' => $role])
                ->with('status', 'Super admin already has access to everything. No changes needed.');
        }

        $permissions = $request->input('permissions', []);

        RolePermission::where('role', $role)->delete();

        foreach ($permissions as $permissionName) {
            RolePermission::create([
                'role'            => $role,
                'permission_name' => $permissionName,
            ]);
        }

        return redirect()
            ->route('admin.permissions.index', ['role' => $role])
            ->with('status', 'Permissions updated for role: ' . $role . '.');
    }

    public function destroy(Permission $permission)
    {
        $this->ensureSuperAdmin();

        RolePermission::where('permission_name', $permission->name)->delete();
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('status', 'Permission deleted.');
    }

    public function renameGroup(Request $request)
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'current_group' => 'nullable|string|max:100',
            'new_group'     => 'required|string|max:100',
        ]);

        $current = $data['current_group'] === '' ? null : $data['current_group'];

        Permission::where('group', $current)->update(['group' => $data['new_group']]);

        return redirect()->route('admin.permissions.index')
            ->with('status', 'Group renamed to ' . $data['new_group'] . '.');
    }

    public function deleteGroup(Request $request)
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'group' => 'nullable|string|max:100',
        ]);

        $group = $data['group'] === '' ? null : $data['group'];

        $permissionNames = Permission::where('group', $group)->pluck('name');

        if ($permissionNames->isEmpty()) {
            return redirect()->route('admin.permissions.index')
                ->with('status', 'Group already empty.');
        }

        RolePermission::whereIn('permission_name', $permissionNames)->delete();
        Permission::whereIn('name', $permissionNames)->delete();

        return redirect()->route('admin.permissions.index')
            ->with('status', 'Group and its permissions deleted.');
    }

    protected function ensureSuperAdmin(): void
    {
        $role = auth()->user()->role ?? null;
        if ($role !== 'super_admin') {
            abort(403, 'Only super admin can manage permissions.');
        }
    }
}
