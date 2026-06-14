<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FleetExpense extends Model
{
    public const TYPE_FUEL = 'fuel';

    public const TYPE_MAINTENANCE = 'maintenance';

    public const TYPE_RENT = 'rent';

    public const TYPE_TOLL = 'toll';

    public const TYPE_OTHER = 'other';

    public const STATUS_RECORDED = 'recorded';

    public const STATUS_REVIEWED = 'reviewed';

    protected $fillable = [
        'vehicle_id',
        'expense_type',
        'expense_date',
        'amount',
        'description',
        'reference',
        'status',
        'payment_type',
        'payment_account_key',
        'fuel_litres',
        'fuel_rate',
        'odometer_km',
        'delivery_route_id',
        'trip_date',
        'analytic_label',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'trip_date' => 'date',
        'amount' => 'decimal:2',
        'fuel_litres' => 'decimal:2',
        'fuel_rate' => 'decimal:2',
        'odometer_km' => 'integer',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_FUEL => 'Fuel',
            self::TYPE_MAINTENANCE => 'Maintenance',
            self::TYPE_RENT => 'Rent / lease',
            self::TYPE_TOLL => 'Toll / parking',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public function typeLabel(): string
    {
        return self::types()[$this->expense_type] ?? ucfirst((string) $this->expense_type);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function deliveryRoute(): BelongsTo
    {
        return $this->belongsTo(DeliveryRoute::class);
    }
}
