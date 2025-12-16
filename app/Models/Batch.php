<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_code',
        'production_date',
        'expiry_date',
        'qc_status',
        'notes',
    ];

    protected $dates = [
        'production_date',
        'expiry_date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productionRuns()
    {
        return $this->hasMany(ProductionRun::class);
    }

    public function stockEntries()
    {
        return $this->hasMany(StockEntry::class);
    }
}
