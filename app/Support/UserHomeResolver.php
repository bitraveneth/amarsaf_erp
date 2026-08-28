<?php

namespace App\Support;

use App\Helpers\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class UserHomeResolver
{
    /**
     * Route name for the user's primary landing page after login.
     */
    public static function routeName(?User $user): string
    {
        if (! $user) {
            return 'admin.dashboard';
        }

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return 'admin.dashboard';
        }

        $routeName = match ($user->role) {
            'warehouse_officer' => 'admin.warehouses.dashboard',
            'purchase_executive' => 'admin.purchase-orders.index',
            'production_officer', 'qc_officer' => 'admin.manufacturing.dashboard',
            'sales_officer' => 'admin.sales.dashboard',
            'delivery_coordinator' => 'admin.logistics.dashboard',
            'accounts_officer' => 'admin.accounting.dashboard',
            default => null,
        };

        if ($routeName && Route::has($routeName)) {
            return $routeName;
        }

        return self::fallbackRouteName($user);
    }

    public static function url(?User $user): string
    {
        return route(self::routeName($user));
    }

    protected static function fallbackRouteName(User $user): string
    {
        $candidates = [
            ['perm' => 'inventory.manage', 'route' => 'admin.warehouses.dashboard'],
            ['perm' => 'control.suppliers', 'route' => 'admin.purchase-orders.index'],
            ['perm' => 'manufacturing.manage', 'route' => 'admin.manufacturing.dashboard'],
            ['perm' => 'sales.manage', 'route' => 'admin.sales.dashboard'],
            ['perm' => 'control.warehouses', 'route' => 'admin.logistics.dashboard'],
            ['perm' => 'accounting.manage', 'route' => 'admin.accounting.dashboard'],
            ['perm' => 'reports.view', 'route' => 'admin.reports.dashboard'],
        ];

        foreach ($candidates as $candidate) {
            if (Permission::can($user, $candidate['perm']) && Route::has($candidate['route'])) {
                return $candidate['route'];
            }
        }

        return 'admin.dashboard';
    }
}
