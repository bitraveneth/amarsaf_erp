<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleController extends Controller
{
    /**
     * Simple central place to review and update user roles.
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $roles = $this->availableRoles();
        $hasUserRolesTable = Schema::hasTable('user_roles');

        $filterRole = $request->input('role');
        $search     = $request->input('q');

        $usersQuery = User::query()
            ->when($hasUserRolesTable, fn ($q) => $q->with('userRoles'))
            ->orderBy('name')
            ->when($filterRole, function ($q) use ($filterRole) {
                $q->where(function ($inner) use ($filterRole) {
                    $inner->where('role', $filterRole)
                        ->when(Schema::hasTable('user_roles'), function ($sub) use ($filterRole) {
                            $sub->orWhereHas('userRoles', function ($roleQuery) use ($filterRole) {
                                $roleQuery->where('role_key', $filterRole);
                            });
                        });
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });

        $users = $usersQuery->paginate(20)->withQueryString();

        $roleCounts = $hasUserRolesTable
            ? DB::table('user_roles')
                ->selectRaw('role_key as role, COUNT(DISTINCT user_id) as total')
                ->groupBy('role_key')
                ->pluck('total', 'role')
            : User::selectRaw('role, COUNT(*) as total')
                ->whereNotNull('role')
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
        $this->syncPrimaryRole($user);

        return redirect()
            ->route('admin.roles.index', $request->only('role', 'q', 'page'))
            ->with('status', 'Role updated for ' . ($user->name ?: $user->email) . '.');
    }

    public function create(Request $request)
    {
        $this->ensureAdmin();

        // Only super admin can create new roles.
        if (! $request->user()?->hasRole('super_admin')) {
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
        if (! auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
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
                'purchase_executive' => 'Purchase executive',
                'warehouse_officer'  => 'Warehouse officer',
                'production_officer' => 'Production officer',
                'sales_officer'      => 'Sales officer',
                'delivery_coordinator' => 'Delivery coordinator',
                'accounts_officer'   => 'Accounts officer',
                'qc_officer'         => 'QC officer',
            ];
        }

        return $roles->pluck('label', 'key')->all();
    }

    protected function syncPrimaryRole(User $user): void
    {
        if (! Schema::hasTable('user_roles') || empty($user->role)) {
            return;
        }

        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $user->id, 'role_key' => $user->role],
            ['updated_at' => now(), 'created_at' => now()]
        );
    }
}
