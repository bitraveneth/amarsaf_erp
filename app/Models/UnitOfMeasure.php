<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOfMeasure extends Model
{
    use HasFactory;

    protected $table = 'units_of_measure';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function linkedProducts()
    {
        return $this->hasMany(Product::class, 'uom', 'code');
    }
}
