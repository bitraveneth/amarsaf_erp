<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FleetRecurringCharge extends Model
{
    protected $fillable = [
        'vehicle_id',
        'expense_type',
        'amount',
        'day_of_month',
        'description',
        'payment_type',
        'payment_account_key',
        'is_active',
        'last_generated_for',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'day_of_month' => 'integer',
        'is_active' => 'boolean',
        'last_generated_for' => 'date',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
