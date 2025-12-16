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
        'issued_at' => 'date',
        'due_at' => 'date',
        'net_total' => 'decimal:2',
        'vat_amount' => 'decimal:2',
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
}
