<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    use HasFactory;

    public const KIND_COMPANY = 'company';
    public const KIND_AGENT = 'agent';
    public const KIND_EMPLOYEE = 'employee';

    protected $fillable = [
        'kind',
        'employee_id',
        'agent_id',
        'period_start',
        'period_end',
        'target_value',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'target_value' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function isCompany(): bool
    {
        return $this->kind === self::KIND_COMPANY;
    }

    public function ownerName(): string
    {
        return match ($this->kind) {
            self::KIND_COMPANY => 'Company',
            self::KIND_AGENT => $this->agent?->name ?? 'Agent',
            self::KIND_EMPLOYEE => $this->employee?->name ?? 'Seller',
            default => $this->agent?->name ?? $this->employee?->name ?? 'Unassigned',
        };
    }

    public function ownerTypeLabel(): string
    {
        return match ($this->kind) {
            self::KIND_COMPANY => 'Company',
            self::KIND_AGENT => 'Agent',
            self::KIND_EMPLOYEE => 'Seller',
            default => $this->agent_id ? 'Agent' : 'Employee',
        };
    }

    protected static function booted(): void
    {
        static::saving(function (SalesTarget $target) {
            if (! \Illuminate\Support\Facades\Schema::hasColumn($target->getTable(), 'kind')) {
                return;
            }

            if (! $target->agent_id && ! $target->employee_id) {
                $target->kind = self::KIND_COMPANY;
            } elseif ($target->agent_id) {
                $target->kind = self::KIND_AGENT;
            } else {
                $target->kind = self::KIND_EMPLOYEE;
            }
        });
    }
}
