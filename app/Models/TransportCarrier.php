<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportCarrier extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_id',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function logisticsBills(): HasMany
    {
        return $this->hasMany(LogisticsBill::class);
    }

    public function carrierRateCards(): HasMany
    {
        return $this->hasMany(CarrierRateCard::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
