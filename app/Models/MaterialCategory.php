<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'group',
        'description',
        'sort_order',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeOrdered($query)
    {
        return $query
            ->orderByRaw('COALESCE(`group`, "") ASC')
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
