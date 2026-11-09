<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethodCategory extends Model
{
    public const KIND_BANK = 'bank';

    public const KIND_MOBILE = 'mobile';

    public const KIND_CASH = 'cash';

    protected $fillable = [
        'code',
        'name',
        'kind',
        'parent_account_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    public function instruments(): HasMany
    {
        return $this->hasMany(PaymentInstrument::class)->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function postingPaymentType(): string
    {
        return $this->kind === self::KIND_CASH
            ? ExpenseCategory::PAYMENT_CASH
            : ExpenseCategory::PAYMENT_BANK;
    }
}
