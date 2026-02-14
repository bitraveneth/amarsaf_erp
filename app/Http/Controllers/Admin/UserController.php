<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
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

        return view('admin.users.index', compact('users', 'roles', 'roleCounts', 'filterRole', 'search'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $roles = $this->availableRoles();

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'role'     => 'required|string|in:' . implode(',', array_keys($roles)),
            'password' => 'nullable|string|min:8|max:191',
        ]);

        $password = $data['password'] ?: str()->random(12);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'password' => $password,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created. Remember to share credentials securely.');
    }

    protected function ensureAdmin(): void
    {
        $role = auth()->user()->role ?? null;
        if (! in_array($role, ['admin', 'super_admin'], true)) {
            abort(403, 'Only admin or super admin users can manage users.');
        }
    }

    protected function availableRoles(): array
    {
        $roles = Role::orderBy('label')->get(['key', 'label']);

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
