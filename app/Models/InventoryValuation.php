<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryValuation extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity_on_hand',
        'total_value',
        'avg_unit_cost',
    ];

    protected $casts = [
        'quantity_on_hand' => 'decimal:4',
        'total_value' => 'decimal:2',
        'avg_unit_cost' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
