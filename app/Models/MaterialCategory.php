<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'parent_id',
        'name',
        'group',
        'description',
        'income_account_id',
        'expense_account_id',
        'inventory_account_id',
        'sort_order',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function incomeAccount()
    {
        return $this->belongsTo(Account::class, 'income_account_id');
    }

    public function expenseAccount()
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    public function inventoryAccount()
    {
        return $this->belongsTo(Account::class, 'inventory_account_id');
    }

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

    public function scopeLeaves($query)
    {
        return $query->whereDoesntHave('children');
    }

    public function isLeaf(): bool
    {
        if ($this->relationLoaded('children')) {
            return $this->children->isEmpty();
        }

        return ! $this->children()->exists();
    }

    public function displayLabel(): string
    {
        return $this->code ? $this->code . ' · ' . $this->name : $this->name;
    }
}
