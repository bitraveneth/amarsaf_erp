<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Product;

class ProductLedgerResolver
{
    public function __construct(
        protected AccountResolver $accounts
    ) {
    }

    public function incomeAccountKey(?Product $product): string
    {
        $account = $this->resolveFromProduct($product, 'income');

        return $account->slug ?? 'product_sales';
    }

    public function expenseAccountKey(?Product $product): string
    {
        $account = $this->resolveFromProduct($product, 'expense');

        return $account->slug ?? 'purchases';
    }

    public function inventoryAccountKey(?Product $product): string
    {
        $account = $this->resolveFromProduct($product, 'inventory');

        return $account->slug ?? $this->typeFallbackInventoryKey($product);
    }

    protected function resolveFromProduct(?Product $product, string $purpose): Account
    {
        if ($product) {
            $product->loadMissing('materialCategory');

            $column = match ($purpose) {
                'income' => 'income_account_id',
                'expense' => 'expense_account_id',
                default => 'inventory_account_id',
            };

            if ($product->{$column}) {
                return Account::query()->findOrFail($product->{$column});
            }

            $category = $product->materialCategory;
            if ($category && $category->{$column}) {
                return Account::query()->findOrFail($category->{$column});
            }
        }

        $fallbackKey = match ($purpose) {
            'income' => 'product_sales',
            'expense' => 'purchases',
            default => $this->typeFallbackInventoryKey($product),
        };

        return $this->accounts->resolve($fallbackKey);
    }

    protected function typeFallbackInventoryKey(?Product $product): string
    {
        $type = $product?->product_type;

        if (in_array($type, ['raw', 'inhouse', 'service'], true)) {
            return 'raw_materials';
        }

        return 'finished_goods';
    }
}
