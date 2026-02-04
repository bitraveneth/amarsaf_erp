<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerGift extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'employee_id',
        'date',
        'occasion',
        'gift_type',
        'description',
        'amount',
        'campaign_code',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
