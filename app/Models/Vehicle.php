<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'ownership', 'fuel_type', 'odometer_km', 'license_plate', 'driver', 'capacity_crates', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'odometer_km' => 'integer',
    ];

    public static function ownershipOptions(): array
    {
        return [
            'own' => 'Own fleet',
            'leased' => 'Leased',
            'hired_daily' => 'Hired daily',
        ];
    }

    public static function fuelTypeOptions(): array
    {
        return [
            'diesel' => 'Diesel',
            'octane' => 'Octane',
            'cng' => 'CNG',
            'electric' => 'Electric',
        ];
    }

    public function routes()
    {
        return $this->hasMany(DeliveryRoute::class);
    }

    public function fleetExpenses()
    {
        return $this->hasMany(FleetExpense::class);
    }

    public function recurringCharges()
    {
        return $this->hasMany(FleetRecurringCharge::class);
    }
}
