<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'product_type',
        'description',
        'size',
        'uom',
        'volume_ml',
        'sku_code',
        'packaging_type_id',
        'tax_class_id',
        'mineral_source',
        'ph',
        'tds',
        'certifications',
        'barcode',
        'qr_code',
        'image_path',
        'base_price',
        'standard_cost',
        'supplier_name',
        'is_active',
    ];

    protected $casts = [
        'volume_ml' => 'float',
        'ph' => 'float',
        'tds' => 'integer',
        'base_price' => 'decimal:2',
        'standard_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function packagingType()
    {
        return $this->belongsTo(PackagingType::class);
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
}
