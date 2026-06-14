<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_id',
        'is_one_time',
    ];

    protected $casts = [
        'is_one_time' => 'boolean',
    ];

    public function productCategories()
    {
        return $this->belongsToMany(SupplierProductCategory::class, 'supplier_supplier_product_category')
            ->withTimestamps()
            ->orderBy('supplier_product_categories.sort_order')
            ->orderBy('supplier_product_categories.name');
    }

    public function bills()
    {
        return $this->hasMany(PurchaseBill::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
