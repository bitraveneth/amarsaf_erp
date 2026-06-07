<?php

namespace App\Support;

use App\Models\MaterialCategory;
use App\Models\Product;

class MaterialCategoryAssigner
{
    /**
     * @return array<int, string|null>
     */
    protected static function categoryRules(): array
    {
        return [
            'Jar-cap sticker' => ['jar-cap sticker', 'jar cap sticker'],
            'Jar-neck label' => ['jar-neck', 'jar neck label', 'neck label'],
            'Jar-label' => ['jar-label', 'jar label'],
            'Jar-cap' => ['jar-cap', 'jar cap'],
            'BOPP Label' => ['bopp', 'rm-label', 'label print'],
            'Shrink Wrap' => ['shrink wrap', 'shrink-wrap', 'rm-shrink'],
            'Hot Melt Glue' => ['hot melt', 'glue'],
            'Potassium' => ['potassium'],
            'Magnesium' => ['magnesium'],
            'Calcium' => ['calcium'],
            'Carton' => ['carton', 'case making', 'case box'],
            'Preform' => ['preform', 'pet bottle', 'rm-pet'],
            'Cap-universal' => ['bottle cap', 'rm-cap', ' cap', 'cap-'],
            'Production Service' => ['labour', 'labor', 'electricity', 'utilities', 'service'],
            'In-house Step' => ['inhouse', 'in-house', 'ih-'],
        ];
    }

    public static function resolveCategoryName(Product $product): ?string
    {
        if ($product->product_type === 'service') {
            return 'Production Service';
        }

        if ($product->product_type === 'inhouse') {
            return 'In-house Step';
        }

        $haystack = strtolower(trim($product->sku . ' ' . $product->name));

        foreach (self::categoryRules() as $categoryName => $keywords) {
            if (in_array($categoryName, ['Production Service', 'In-house Step'], true)) {
                continue;
            }

            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($haystack, strtolower($keyword))) {
                    return $categoryName;
                }
            }
        }

        if (str_contains($haystack, 'water') || str_contains($haystack, 'mineral')) {
            return 'Minerals (Universal)';
        }

        return null;
    }

    public static function resolveCategoryId(Product $product): ?int
    {
        $name = self::resolveCategoryName($product);

        if ($name === null) {
            return null;
        }

        return MaterialCategory::where('name', $name)->value('id');
    }

    /**
     * @return int Number of materials updated
     */
    public static function backfillMissing(): int
    {
        $updated = 0;

        Product::query()
            ->whereIn('product_type', ['raw', 'service', 'inhouse'])
            ->whereNull('material_category_id')
            ->orderBy('id')
            ->each(function (Product $product) use (&$updated) {
                $categoryId = self::resolveCategoryId($product);

                if ($categoryId === null) {
                    return;
                }

                $product->update(['material_category_id' => $categoryId]);
                $updated++;
            });

        return $updated;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function withCategory(array $attributes, ?string $sku = null, ?string $name = null): array
    {
        if (! empty($attributes['material_category_id'])) {
            return $attributes;
        }

        $product = new Product(array_merge($attributes, [
            'sku' => $sku ?? ($attributes['sku'] ?? ''),
            'name' => $name ?? ($attributes['name'] ?? ''),
        ]));

        $categoryId = self::resolveCategoryId($product);

        if ($categoryId !== null) {
            $attributes['material_category_id'] = $categoryId;
        }

        return $attributes;
    }
}
