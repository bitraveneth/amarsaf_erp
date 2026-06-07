<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'number',
        'bill_date',
        'due_date',
        'warehouse_id',
        'net_total',
        'vat_amount',
        'status',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'net_total' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseBillItem::class);
    }

    public function payments()
    {
        return $this->hasMany(BillPayment::class);
    }

    public function getGrossTotalAttribute(): float
    {
        return (float) ($this->net_total + $this->vat_amount);
    }

    public function getPaidTotalAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->sum('amount');
        }

        return (float) $this->payments()->sum('amount');
    }

    public function getOutstandingAttribute(): float
    {
        return max(0.0, round($this->gross_total - $this->paid_total, 2));
    }

    public function recalculateStatus(): void
    {
        $outstanding = $this->outstanding;
        $paid = $this->paid_total;

        if ($outstanding <= 0.00001 && $paid > 0) {
            $this->status = 'paid';
        } elseif ($paid > 0) {
            $this->status = 'part_paid';
        } else {
            $this->status = 'open';
        }

        $this->save();
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
