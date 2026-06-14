<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    public const STATUS_RECORDED = 'recorded';

    public const STATUS_REVIEWED = 'reviewed';

    protected $fillable = [
        'date',
        'category',
        'expense_category_id',
        'account_id',
        'description',
        'amount',
        'reference',
        'status',
        'payment_type',
        'payment_account_key',
        'analytic_label',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function accountOverride(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public static function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            self::STATUS_REVIEWED,
            'paid',
            'overdue' => self::STATUS_REVIEWED,
            default => self::STATUS_RECORDED,
        };
    }

    public function getStatusAttribute($value): string
    {
        return self::normalizeStatus($value);
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }
}
