<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'order_item_id',
        'product_id',
        'batch_id',
        'qty_dispatched',
        'qty_delivered',
        'qty_short',
        'qty_damaged',
        'notes',
    ];

    protected $casts = [
        'qty_dispatched' => 'decimal:2',
        'qty_delivered' => 'decimal:2',
        'qty_short' => 'decimal:2',
        'qty_damaged' => 'decimal:2',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
