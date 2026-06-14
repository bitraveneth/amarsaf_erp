<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'unit',
        'description',
        'units_per_pack',
        'size_key',
        'is_system',
    ];

    protected $casts = [
        'units_per_pack' => 'integer',
        'is_system' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function conversionsFrom()
    {
        return $this->hasMany(PackagingConversion::class, 'from_packaging_type_id');
    }

    public function conversionsTo()
    {
        return $this->hasMany(PackagingConversion::class, 'to_packaging_type_id');
    }

    public function scopeOrderedForSelect($query)
    {
        return $query->orderByDesc('is_system')->orderBy('code');
    }

    public function selectOptionLabel(): string
    {
        $label = trim(($this->code ? $this->code . ' · ' : '') . $this->name);

        if ($this->units_per_pack) {
            $label .= ' (' . number_format($this->units_per_pack) . ' pcs)';
        }

        return $label;
    }
}
