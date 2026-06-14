<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'brand',
        'product_type',
        'material_category_id',
        'description',
        'size',
        'uom',
        'volume_ml',
        'weight_g',
        'shelf_life_months',
        'sku_code',
        'packaging_type_id',
        'tax_class_id',
        'income_account_id',
        'expense_account_id',
        'inventory_account_id',
        'mineral_source',
        'ph',
        'tds',
        'certifications',
        'barcode',
        'qr_code',
        'image_path',
        'base_price',
        'mrp',
        'standard_cost',
        'reorder_level',
        'supplier_name',
        'chemical_name',
        'sourcing',
        'is_active',
    ];

    protected $casts = [
        'volume_ml' => 'float',
        'weight_g' => 'integer',
        'shelf_life_months' => 'integer',
        'ph' => 'float',
        'tds' => 'integer',
        'base_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'standard_cost' => 'decimal:2',
        'reorder_level' => 'integer',
        'is_active' => 'boolean',
    ];

    public function packagingType()
    {
        return $this->belongsTo(PackagingType::class);
    }

    public function materialCategory()
    {
        return $this->belongsTo(MaterialCategory::class);
    }

    public function incomeAccount()
    {
        return $this->belongsTo(Account::class, 'income_account_id');
    }

    public function expenseAccount()
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    public function inventoryAccount()
    {
        return $this->belongsTo(Account::class, 'inventory_account_id');
    }

    public function scopeSellable(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $builder) {
                $builder->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            })
            ->where('is_active', true);
    }

    public function scopeMaterials(Builder $query): Builder
    {
        return $query
            ->whereIn('product_type', ['raw', 'service', 'inhouse'])
            ->where('is_active', true);
    }

    public function scopePurchasable(Builder $query): Builder
    {
        return $query
            ->whereIn('product_type', ['raw', 'service'])
            ->where('is_active', true);
    }

    public function isPurchasable(): bool
    {
        return in_array($this->product_type, ['raw', 'service'], true);
    }

    public function scopeStockTracked(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereNull('product_type')
                ->orWhereIn('product_type', ['finished', 'raw', 'inhouse']);
        });
    }

    public function isStockTracked(): bool
    {
        return $this->product_type === null
            || in_array($this->product_type, ['finished', 'raw', 'inhouse'], true);
    }

    public function isSellable(): bool
    {
        return ($this->product_type === null || $this->product_type === 'finished')
            && $this->is_active;
    }

    public function catalogCategoryLabel(): string
    {
        return match ($this->product_type) {
            'raw' => 'Raw Material',
            'service' => 'Service',
            'inhouse' => 'In-house',
            default => 'Finished Goods',
        };
    }

    public function formattedWeight(): ?string
    {
        if ($this->weight_g === null) {
            return null;
        }

        if ($this->weight_g >= 1000) {
            $kg = $this->weight_g / 1000;

            return rtrim(rtrim(number_format($kg, 2), '0'), '.') . ' kg';
        }

        return number_format($this->weight_g) . ' g';
    }

    public function packagingLabel(): ?string
    {
        if (! $this->relationLoaded('packagingType')) {
            $this->load('packagingType');
        }

        if (! $this->packagingType) {
            return null;
        }

        $label = $this->packagingType->name;

        if ($this->packagingType->code) {
            return $label . ' (' . $this->packagingType->code . ')';
        }

        return $label;
    }

    public function hasCompleteCatalogSpecs(): bool
    {
        return filled($this->volume_ml)
            && filled($this->uom);
    }

    public function scopeCatalogFinishedGoods(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereNull('product_type')
                ->orWhere('product_type', 'finished');
        });
    }

    public function hasPricingSet(): bool
    {
        return (float) ($this->base_price ?? 0) > 0;
    }

    /**
     * @return list<array{label: string, tone: string, anchor: ?string, href: ?string}>
     */
    public function catalogReadinessGaps(): array
    {
        if (! in_array($this->product_type, [null, 'finished'], true)) {
            return [];
        }

        $gaps = [];

        if (! filled($this->barcode)) {
            $gaps[] = [
                'label' => 'Missing barcode',
                'tone' => 'warning',
                'anchor' => 'barcode',
                'href' => null,
            ];
        }

        if (! filled($this->volume_ml)) {
            $gaps[] = [
                'label' => 'Volume not set',
                'tone' => 'warning',
                'anchor' => 'volume_ml_select',
                'href' => null,
            ];
        }

        if (! filled($this->uom)) {
            $gaps[] = [
                'label' => 'UOM not set',
                'tone' => 'warning',
                'anchor' => 'uom',
                'href' => null,
            ];
        }

        if (! $this->packaging_type_id) {
            $gaps[] = [
                'label' => 'No packaging',
                'tone' => 'neutral',
                'anchor' => 'packaging_type_id',
                'href' => null,
            ];
        }

        if (! $this->hasPricingSet()) {
            $gaps[] = [
                'label' => 'Trade price not set',
                'tone' => 'warning',
                'anchor' => null,
                'href' => route('admin.products.prices.show', $this),
            ];
        }

        if (! $this->is_active) {
            $gaps[] = [
                'label' => 'Inactive',
                'tone' => 'neutral',
                'anchor' => 'is_active',
                'href' => null,
            ];
        }

        return $gaps;
    }

    public function isCatalogReady(): bool
    {
        return $this->hasCompleteCatalogSpecs()
            && filled($this->barcode)
            && $this->hasPricingSet()
            && $this->is_active;
    }

    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function agentPriceLists()
    {
        return $this->hasMany(AgentPriceList::class);
    }

    public function billsOfMaterial()
    {
        return $this->hasMany(BillOfMaterial::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function productionMaterialIssueItems()
    {
        return $this->hasMany(ProductionMaterialIssueItem::class, 'component_product_id');
    }
}
