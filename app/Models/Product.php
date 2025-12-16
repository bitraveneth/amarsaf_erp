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
        'description',
        'size',
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
    ];

    protected $casts = [
        'volume_ml' => 'float',
        'ph' => 'float',
        'tds' => 'integer',
        'base_price' => 'decimal:2',
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
}
