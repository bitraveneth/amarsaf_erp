<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'purchase_bill_id',
        'supplier_id',
        'warehouse_id',
        'created_by',
        'grn_number',
        'received_at',
        'status',
        'submitted_at',
        'warehouse_approved_at',
        'warehouse_approved_by',
        'procurement_approved_at',
        'procurement_approved_by',
        'notes',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'submitted_at' => 'datetime',
        'warehouse_approved_at' => 'datetime',
        'procurement_approved_at' => 'datetime',
    ];

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isFullyApproved(): bool
    {
        return $this->warehouse_approved_at !== null && $this->procurement_approved_at !== null;
    }

    public function warehouseApprover()
    {
        return $this->belongsTo(User::class, 'warehouse_approved_by');
    }

    public function procurementApprover()
    {
        return $this->belongsTo(User::class, 'procurement_approved_by');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseBill()
    {
        return $this->belongsTo(PurchaseBill::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function stockEntries()
    {
        return $this->hasMany(StockEntry::class);
    }
}
