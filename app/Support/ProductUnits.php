<?php

namespace App\Support;

use App\Models\UnitOfMeasure;

class ProductUnits
{
    /** @var array<string, string>|null */
    protected static ?array $cachedAll = null;

    /** Sellable finished-goods units (excludes labour / material-only codes). */
    protected static array $finishedProductCodes = [
        'piece', 'bottle', 'jar', 'carton', 'crate', 'pack', 'liter', 'ml', 'kg',
    ];

    /** Units for finished sellable products (beverage / packaging). */
    public static function forProducts(): array
    {
        return self::forFinishedProducts();
    }

    /**
     * @param  iterable<int, UnitOfMeasure>|null  $dbUnits
     * @return array<string, string>
     */
    public static function forFinishedProducts(?iterable $dbUnits = null): array
    {
        $options = [];

        foreach (self::defaultFinishedUnits() as $code => $name) {
            $options[$code] = $name;
        }

        if ($dbUnits !== null) {
            foreach ($dbUnits as $unit) {
                if (! $unit->is_system || in_array($unit->code, self::$finishedProductCodes, true)) {
                    $options[$unit->code] = $unit->name;
                }
            }
        } else {
            $fromDb = self::loadFromDatabase();
            if ($fromDb) {
                foreach ($fromDb as $code => $name) {
                    if (in_array($code, self::$finishedProductCodes, true)) {
                        $options[$code] = $name;
                    }
                }
            }
        }

        asort($options);

        return $options;
    }

    /**
     * @return array<string, string>
     */
    protected static function defaultFinishedUnits(): array
    {
        return [
            'bottle' => 'Bottle',
            'carton' => 'Carton',
            'piece' => 'Piece',
            'jar' => 'Jar',
            'crate' => 'Crate',
            'pack' => 'Pack',
            'liter' => 'Litre',
            'ml' => 'Millilitre',
            'kg' => 'Kilogram',
        ];
    }

    /** Units for raw materials, services, and in-house steps. */
    public static function forMaterials(): array
    {
        $all = self::loadFromDatabase() ?: [
            'piece' => 'Piece',
            'liter' => 'Litre',
            'ml' => 'Millilitre',
            'kg' => 'Kilogram',
            'carton' => 'Carton',
            'day' => 'Day',
            'shift' => 'Shift',
        ];

        return $all;
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

    /**
     * @return array<string, string>|null
     */
    protected static function loadFromDatabase(): ?array
    {
        if (self::$cachedAll !== null) {
            return self::$cachedAll;
        }

        try {
            if (! class_exists(UnitOfMeasure::class) || ! \Illuminate\Support\Facades\Schema::hasTable('units_of_measure')) {
                return null;
            }

            $rows = UnitOfMeasure::ordered()->get(['code', 'name']);

            if ($rows->isEmpty()) {
                return null;
            }

            self::$cachedAll = $rows->pluck('name', 'code')->all();

            return self::$cachedAll;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function flushCache(): void
    {
        self::$cachedAll = null;
    }
}
