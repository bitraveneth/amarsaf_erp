<?php

namespace App\Support;

class ProductUnits
{
    /** Units for finished sellable products (beverage / packaging). */
    public static function forProducts(): array
    {
        return [
            'piece' => 'Piece',
            'carton' => 'Carton',
            'bottle' => 'Bottle',
            'jar' => 'Jar',
            'liter' => 'Litre',
            'ml' => 'Millilitre',
            'kg' => 'Kilogram',
        ];
    }

    /** Units for raw materials, services, and in-house steps. */
    public static function forMaterials(): array
    {
        return [
            'piece' => 'Piece',
            'liter' => 'Litre',
            'ml' => 'Millilitre',
            'kg' => 'Kilogram',
            'carton' => 'Carton',
            'day' => 'Day',
            'shift' => 'Shift',
        ];
    }

    public static function options(bool $isMaterials): array
    {
        return $isMaterials ? self::forMaterials() : self::forProducts();
    }

    public static function label(?string $uom): ?string
    {
        if (! $uom) {
            return null;
        }

        $all = self::forProducts() + self::forMaterials();

        return $all[$uom] ?? ucfirst($uom);
    }
}
