<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'order_type',
        'delivery_date',
        'status',
        'total',
        'commission_total',
        'notes',
        'is_credit_used',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'total' => 'decimal:2',
        'commission_total' => 'decimal:2',
        'is_credit_used' => 'boolean',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }
}
