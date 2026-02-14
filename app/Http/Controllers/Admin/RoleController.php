<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Simple central place to review and update user roles.
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $roles = $this->availableRoles();

        $filterRole = $request->input('role');
        $search     = $request->input('q');

        $usersQuery = User::query()
            ->orderBy('name')
            ->when($filterRole, function ($q) use ($filterRole) {
                $q->where('role', $filterRole);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });

        $users = $usersQuery->paginate(20)->withQueryString();

        $roleCounts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('admin.roles.index', compact('users', 'roles', 'roleCounts', 'filterRole', 'search'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdmin();

        $roles = $this->availableRoles();
        $request->validate([
            'role' => 'required|string|in:' . implode(',', array_keys($roles)),
        ]);

        $user->role = $request->input('role');
        $user->save();

        return redirect()
            ->route('admin.roles.index', $request->only('role', 'q', 'page'))
            ->with('status', 'Role updated for ' . ($user->name ?: $user->email) . '.');
    }

    public function create(Request $request)
    {
        $this->ensureAdmin();

        // Only super admin can create new roles.
        if (($request->user()->role ?? null) !== 'super_admin') {
            abort(403, 'Only super admin can create roles.');
        }

        $data = $request->validate([
            'key'   => 'required|string|max:50|alpha_dash|unique:roles,key',
            'label' => 'required|string|max:100',
        ]);

        Role::create([
            'key'       => $data['key'],
            'label'     => $data['label'],
            'is_system' => false,
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role ' . $data['label'] . ' created.');
    }

    protected function ensureAdmin(): void
    {
        $role = auth()->user()->role ?? null;
        if (! in_array($role, ['admin', 'super_admin'], true)) {
            abort(403, 'Only admin or super admin users can manage roles.');
        }
    }

    /**
     * Central list of available roles used by the role manager.
     */
    protected function availableRoles(): array
    {
        $roles = Role::orderBy('label')->get(['key', 'label']);

        // Fallback to legacy list if table is empty.
        if ($roles->isEmpty()) {
            return [
                'super_admin'        => 'Super admin',
                'admin'              => 'Admin',
                'warehouse_manager'  => 'Warehouse manager',
                'production_manager' => 'Production manager',
                'sales_manager'      => 'Sales manager',
                'qc_officer'         => 'QC officer',
                'employee'           => 'Field / office employee',
            ];
        }

        return $roles->pluck('label', 'key')->all();
    }
}
