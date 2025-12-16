<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'license_plate', 'driver', 'capacity_crates'];

    public function routes()
    {
        return $this->hasMany(DeliveryRoute::class);
    }
}
