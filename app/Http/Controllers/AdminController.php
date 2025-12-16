<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\PackagingType;
use App\Models\TaxClass;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    /**
     * Display a simple admin dashboard.
     */
    public function index()
    {
        $usersTableReady = Schema::hasTable('users');
        $productTableReady = Schema::hasTable('products');
        $packagingReady = Schema::hasTable('packaging_types');
        $taxReady = Schema::hasTable('tax_classes');
        $batchReady = Schema::hasTable('batches');

        $userCount = $usersTableReady ? User::count() : 0;
        $verifiedCount = $usersTableReady ? User::whereNotNull('email_verified_at')->count() : 0;
        $recentUsers = $usersTableReady
            ? User::orderByDesc('created_at')->take(5)->get()
            : collect();

        $productCount = $productTableReady ? Product::count() : 0;
        $recentProducts = $productTableReady
            ? Product::with(['packagingType', 'taxClass'])->orderByDesc('created_at')->take(6)->get()
            : collect();
        $packagingCount = $packagingReady ? PackagingType::count() : 0;
        $taxClassCount = $taxReady ? TaxClass::count() : 0;
        $batchCount = $batchReady ? Batch::count() : 0;
        $recentBatches = $batchReady
            ? Batch::with('product')->orderByDesc('production_date')->take(5)->get()
            : collect();

        $metrics = [
            [
                'label' => 'Registered users',
                'value' => number_format($userCount),
                'detail' => $usersTableReady ? 'All accounts onboarded' : 'Run migrations to create users',
            ],
            [
                'label' => 'Verified emails',
                'value' => number_format($verifiedCount),
                'detail' => 'Confirmed email addresses',
            ],
        ];

        if ($productTableReady) {
            $metrics[] = [
                'label' => 'Product SKUs',
                'value' => number_format($productCount),
                'detail' => 'Product catalog entries',
            ];
        }

        if ($packagingReady) {
            $metrics[] = [
                'label' => 'Packaging types',
                'value' => number_format($packagingCount),
                'detail' => 'Bottle/crate/carton definitions',
            ];
        }

        $masterSummary = [
            'products' => $productCount,
            'packaging' => $packagingCount,
            'taxClasses' => $taxClassCount,
            'batches' => $batchCount,
        ];

        return view('admin.dashboard', compact(
            'metrics',
            'recentUsers',
            'usersTableReady',
            'recentProducts',
            'masterSummary',
            'recentBatches',
            'productTableReady',
            'packagingReady',
            'taxReady',
            'batchCount'
        ));
    }
}
