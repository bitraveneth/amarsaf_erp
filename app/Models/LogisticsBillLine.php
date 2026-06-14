<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticsBillLine extends Model
{
    protected $fillable = [
        'logistics_bill_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(LogisticsBill::class, 'logistics_bill_id');
    }
}
