<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillOfMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'is_active',
        'notes',
        'material_unit_cost',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(BomItem::class, 'bill_of_materials_id');
    }

    public function displayName(): string
    {
        return $this->name ?: 'Standard recipe';
    }

    public function computedMaterialUnitCost(): ?float
    {
        if (! is_null($this->material_unit_cost)) {
            return (float) $this->material_unit_cost;
        }

        $this->loadMissing('items');

        $total = $this->items->sum(function (BomItem $item) {
            if (is_null($item->unit_cost)) {
                return 0;
            }

            return (float) $item->unit_cost * (float) $item->quantity;
        });

        return $total > 0 ? round($total, 4) : null;
    }

    public static function activeForProduct(int $productId): ?self
    {
        return static::query()
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();
    }

    public static function generateDefaultName(Product $product, bool $versioned = false): string
    {
        $label = trim($product->sku ?: $product->name ?: 'Product');
        $base = "Standard · {$label}";

        if (! $versioned) {
            return $base;
        }

        $version = static::where('product_id', $product->id)->count() + 1;

        return "{$base} · v{$version}";
    }

    public function recipeCode(): string
    {
        return 'BOM #' . $this->id;
    }

    public static function nextVersionNumber(int $productId): int
    {
        return (int) static::where('product_id', $productId)->count() + 1;
    }

    /**
     * Recipe setup step for the BOM workflow stepper (1–4, or 5 when fully active).
     */
    public function recipeWorkflowStep(): int
    {
        if ($this->is_active) {
            return 5;
        }

        $this->loadMissing('items');

        $filledItems = $this->items->filter(
            fn (BomItem $item) => $item->component_product_id && (float) $item->quantity > 0
        );

        if ($filledItems->isEmpty()) {
            return $this->product_id ? 2 : 1;
        }

        return 3;
    }

    public function recipeWorkflowInProgress(): bool
    {
        return ! $this->is_active && $this->recipeWorkflowStep() === 3;
    }
}
