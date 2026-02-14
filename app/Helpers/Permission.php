<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\RolePermission;

class Permission
{
    /**
     * Check if a given user has a named permission based on their role.
     *
     * This is intentionally simple for now – a static role → permission map.
     */
    public static function can(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        $role = $user->role ?? null;
        if (in_array($role, ['admin', 'super_admin'], true)) {
            // Admin and Super Admin can do everything.
            return true;
        }

        static $cache = [];

        if (! array_key_exists($permission, $cache)) {
            $cache[$permission] = RolePermission::where('permission_name', $permission)
                ->pluck('role')
                ->all();
        }

        return in_array($role, $cache[$permission], true);
    }
}
