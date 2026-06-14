<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'value',
        'frequency',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
