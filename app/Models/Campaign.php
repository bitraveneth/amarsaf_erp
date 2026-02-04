<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'platform',
        'start_date',
        'end_date',
        'reach',
        'impressions',
        'cost',
        'status',
        'campaign_code',
        'attachment_path',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reach' => 'integer',
        'impressions' => 'integer',
        'cost' => 'decimal:2',
    ];
}
