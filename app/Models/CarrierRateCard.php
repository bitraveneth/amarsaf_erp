<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarrierRateCard extends Model
{
    public const UNIT_TRIP = 'trip';

    public const UNIT_CRATE = 'crate';

    public const UNIT_KM = 'km';

    public const UNIT_MONTHLY = 'monthly';

    protected $fillable = [
        'transport_carrier_id',
        'delivery_route_id',
        'unit',
        'rate',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static function units(): array
    {
        return [
            self::UNIT_TRIP => 'Per trip',
            self::UNIT_CRATE => 'Per crate',
            self::UNIT_KM => 'Per km',
            self::UNIT_MONTHLY => 'Monthly retainer',
        ];
    }

    public function unitLabel(): string
    {
        return self::units()[$this->unit] ?? ucfirst((string) $this->unit);
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

    public function estimateAmount(int $crates = 0, float $km = 0): float
    {
        return match ($this->unit) {
            self::UNIT_CRATE => round((float) $this->rate * max($crates, 1), 2),
            self::UNIT_KM => round((float) $this->rate * max($km, 1), 2),
            self::UNIT_MONTHLY, self::UNIT_TRIP => round((float) $this->rate, 2),
            default => round((float) $this->rate, 2),
        };
    }
}
