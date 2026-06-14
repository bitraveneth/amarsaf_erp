<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogisticsBill extends Model
{
    public const SERVICE_EXTERNAL_FREIGHT = 'external_freight';

    public const SERVICE_COURIER = 'courier';

    public const SERVICE_OTHER = 'other';

    protected $fillable = [
        'transport_carrier_id',
        'number',
        'bill_date',
        'due_date',
        'service_type',
        'delivery_route_id',
        'trip_date',
        'notes',
        'net_total',
        'vat_amount',
        'status',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'trip_date' => 'date',
        'net_total' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    public static function serviceTypes(): array
    {
        return [
            self::SERVICE_EXTERNAL_FREIGHT => 'External freight (hired truck)',
            self::SERVICE_COURIER => 'Courier / express',
            self::SERVICE_OTHER => 'Other transport',
        ];
    }

    public function serviceTypeLabel(): string
    {
        return self::serviceTypes()[$this->service_type] ?? ucfirst((string) $this->service_type);
    }

    public function transportCarrier(): BelongsTo
    {
        return $this->belongsTo(TransportCarrier::class);
    }

    /** @deprecated Use transportCarrier() — legacy alias */
    public function supplier(): BelongsTo
    {
        return $this->transportCarrier();
    }

    public function deliveryRoute(): BelongsTo
    {
        return $this->belongsTo(DeliveryRoute::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(LogisticsBillLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LogisticsBillPayment::class);
    }

    public function getPaidTotalAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->sum('amount');
        }

        return (float) $this->payments()->sum('amount');
    }

    public function getOutstandingAttribute(): float
    {
        return max(0.0, round($this->gross_total - $this->paid_total, 2));
    }

    public function recalculateStatus(): void
    {
        $outstanding = $this->outstanding;
        $paid = $this->paid_total;

        if ($outstanding <= 0.00001 && $paid > 0) {
            $this->status = 'paid';
        } elseif ($paid > 0) {
            $this->status = 'part_paid';
        } elseif ($this->status !== 'draft') {
            $this->status = 'open';
        }

        $this->saveQuietly();
    }

    public function getGrossTotalAttribute(): float
    {
        return (float) ($this->net_total + $this->vat_amount);
    }

    public function getDocumentNumberAttribute(): string
    {
        return $this->number ?: ('LB-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT));
    }
}
