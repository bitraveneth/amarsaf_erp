<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'number',
        'issued_at',
        'due_at',
        'net_total',
        'vat_amount',
        'withholding',
        'status',
    ];

    protected $casts = [
        'issued_at'   => 'date',
        'due_at'      => 'date',
        'net_total'   => 'decimal:2',
        'vat_amount'  => 'decimal:2',
        'withholding' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function getGrossTotalAttribute(): float
    {
        return (float) ($this->net_total + $this->vat_amount);
    }

    public function getCashTotalAttribute(): float
    {
        return (float) ($this->gross_total - $this->withholding);
    }

    public function getCreditsTotalAttribute(): float
    {
        return (float) $this->creditNotes()->sum('amount');
    }

    public function getReceiptsTotalAttribute(): float
    {
        return (float) $this->receipts()->sum('amount');
    }

    public function getOutstandingAttribute(): float
    {
        return max(0.0, (float) ($this->cash_total - $this->credits_total - $this->receipts_total));
    }

    /**
     * Recalculate the invoice status based on payments / credits applied.
     *
     * Status rules:
     * - paid      : outstanding <= 0 and some movement
     * - adjusted  : some payments or credits applied but still outstanding
     * - issued    : no payments or credits applied yet
     */
    public function recalculateStatus(): void
    {
        $outstanding = $this->outstanding;
        $hasMovement = ($this->credits_total > 0.0) || ($this->receipts_total > 0.0);

        if ($outstanding <= 0.00001 && $hasMovement) {
            $this->status = 'paid';
        } elseif ($hasMovement) {
            $this->status = 'adjusted';
        } else {
            $this->status = 'issued';
        }

        $this->save();
    }
}
