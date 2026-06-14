<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOvertime extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PAID = 'paid';

    protected $table = 'employee_overtime';

    protected $fillable = [
        'employee_id',
        'work_date',
        'hours',
        'rate_multiplier',
        'hourly_rate',
        'amount',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'salary_distribution_id',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours' => 'decimal:2',
        'rate_multiplier' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PAID => 'Paid',
        ];
    }

    public static function multipliers(): array
    {
        return [
            '1.00' => 'Regular (1×)',
            '1.50' => 'Standard OT (1.5×)',
            '2.00' => 'Double (2×)',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryDistribution(): BelongsTo
    {
        return $this->belongsTo(SalaryDistribution::class);
    }

    public static function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'submitted' => self::STATUS_PENDING,
            self::STATUS_APPROVED => self::STATUS_APPROVED,
            self::STATUS_REJECTED => self::STATUS_REJECTED,
            self::STATUS_PAID => self::STATUS_PAID,
            default => self::STATUS_PENDING,
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
