<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticsBillPayment extends Model
{
    protected $fillable = [
        'logistics_bill_id',
        'amount',
        'paid_at',
        'method',
        'batch_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(LogisticsBill::class, 'logistics_bill_id');
    }
}
