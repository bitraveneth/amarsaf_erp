<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    public const PAYMENT_BANK = 'bank';

    public const PAYMENT_CASH = 'cash';

    public const PAYMENT_PAYABLE = 'payable';

    protected $fillable = [
        'code',
        'name',
        'account_id',
        'payable_account_id',
        'default_payment_type',
        'default_payment_account_key',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function payableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payable_account_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
