<?php

namespace App\Support;

use App\Models\CompanyCommissionRule;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompanyCommissionSync
{
    /**
     * @return Collection<int, CompanyCommissionRule>
     */
    public static function rulesByProductId(): Collection
    {
        return CompanyCommissionRule::query()
            ->get()
            ->keyBy('product_id');
    }

    public static function rateForProduct(int $productId): ?string
    {
        $rule = CompanyCommissionRule::query()->where('product_id', $productId)->first();

        if (! $rule || $rule->type !== 'percentage') {
            return null;
        }

        return rtrim(rtrim(number_format((float) $rule->value, 2), '0'), '.');
    }

    /**
     * @param  array<int|string, mixed>  $rates
     */
    public static function syncProducts(array $rates): void
    {
        $allowedProductIds = Product::sellable()->pluck('id')->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use ($rates, $allowedProductIds) {
            foreach ($rates as $productId => $rate) {
                if (! in_array((int) $productId, $allowedProductIds, true)) {
                    continue;
                }

                $numericRate = ($rate === null || $rate === '') ? null : (float) $rate;

                if ($numericRate === null || $numericRate <= 0) {
                    CompanyCommissionRule::query()->where('product_id', (int) $productId)->delete();

                    continue;
                }

                CompanyCommissionRule::query()->updateOrCreate(
                    ['product_id' => (int) $productId],
                    [
                        'type' => 'percentage',
                        'value' => $numericRate,
                        'frequency' => 'monthly',
                    ]
                );
            }
        });
    }
}
