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
            'Jar-cap sticker' => ['jar-cap sticker', 'jar cap sticker', 'lbl-jar-cap'],
            'Jar-neck label' => ['jar-neck', 'jar neck label', 'neck label', 'lbl-jar-neck'],
            'Jar-label' => ['jar-label', 'jar label', 'lbl-20l'],
            'BOPP Label' => ['bopp', 'rm-bopp', 'rm-lbl', 'label 500', 'label-500', 'rm-label'],
            'Shrink Wrap' => ['shrink wrap', 'shrink-wrap', 'rm-shrink'],
            'Hot Melt Glue' => ['hot melt', 'glue'],
            'Potassium' => ['potassium', 'min-k'],
            'Magnesium' => ['magnesium', 'min-mg'],
            'Calcium' => ['calcium', 'min-ca'],
            'Minerals (Universal)' => ['minerals universal', 'min-univ', 'mineral'],
            'Carton' => ['carton', 'ctn-', 'case making', 'case box'],
            'Preform' => ['preform', 'pref-', 'rm-pet', 'pet bottle'],
            'Cap-universal' => ['bottle cap', 'rm-cap-std', ' cap', 'cap-std', 'cap-universal'],
            'Jar-cap' => ['jar-cap', 'jar cap', 'cap-jar'],
            'Treated Water' => ['ro water', 'rm-water', 'treated water'],
            'Production Service' => ['labour', 'labor', 'sv-svc', 'sv-lab', 'electricity', 'utilities', 'sv-util'],
            'In-house Step' => ['inhouse', 'in-house', 'ih-step', 'ih-shrink', 'label print'],
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
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($haystack, strtolower($keyword))) {
                    return $categoryName;
                }
            }
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
