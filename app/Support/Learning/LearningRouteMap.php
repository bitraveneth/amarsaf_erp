<?php

namespace App\Support\Learning;

class LearningRouteMap
{
    /**
     * Map admin URL prefixes to Learning Hub module slugs (longest match wins).
     *
     * @var array<string, string>
     */
    protected static array $prefixMap = [
        '/admin/products' => 'products',
        '/admin/materials' => 'products',
        '/admin/material-categories' => 'products',
        '/admin/packaging' => 'products',
        '/admin/tax-classes' => 'products',
        '/admin/products-price-list' => 'agents',
        '/admin/agents' => 'agents',
        '/admin/commission-rules' => 'agents',
        '/admin/kyc-document-types' => 'agents',
        '/admin/suppliers' => 'procurement',
        '/admin/purchase-orders' => 'procurement',
        '/admin/goods-receipts' => 'procurement',
        '/admin/bills' => 'accounting',
        '/admin/warehouses' => 'warehouses',
        '/admin/warehouse-locations' => 'warehouses',
        '/admin/vehicles' => 'warehouses',
        '/admin/delivery-routes' => 'warehouses',
        '/admin/boms' => 'manufacturing',
        '/admin/production' => 'manufacturing',
        '/admin/batches' => 'manufacturing',
        '/admin/manufacturing-dashboard' => 'manufacturing',
        '/admin/inventory' => 'inventory',
        '/admin/mrp' => 'inventory',
        '/admin/stock' => 'inventory',
        '/admin/orders-picking' => 'delivery',
        '/admin/deliveries' => 'delivery',
        '/admin/vehicle-load' => 'delivery',
        '/admin/orders' => 'sales',
        '/admin/sales-targets' => 'sales',
        '/admin/returns' => 'sales',
        '/admin/gifts' => 'sales',
        '/admin/campaigns' => 'sales',
        '/admin/commissions' => 'sales',
        '/admin/settlements' => 'sales',
        '/admin/sales-dashboard' => 'sales',
        '/admin/finance' => 'accounting',
        '/admin/expenses' => 'accounting',
        '/admin/salary-distributions' => 'accounting',
        '/admin/accounts' => 'accounting',
        '/admin/journals' => 'accounting',
        '/admin/agent-advances' => 'accounting',
        '/admin/accounting-dashboard' => 'accounting',
        '/admin/reports/pl' => 'profit-loss',
        '/admin/reports' => 'reports',
        '/admin/export-center' => 'reports',
    ];

    public static function slugForPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = '/' . trim($path, '/');
        if ($path === '/admin/learning-hub') {
            return null;
        }

        $matches = [];
        foreach (self::$prefixMap as $prefix => $slug) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                $matches[strlen($prefix)] = $slug;
            }
        }

        if ($matches === []) {
            return null;
        }

        ksort($matches);

        return end($matches);
    }

    public static function slugForRequest(?\Illuminate\Http\Request $request = null): ?string
    {
        $request ??= request();

        return self::slugForPath('/' . ltrim($request->path(), '/'));
    }
}
