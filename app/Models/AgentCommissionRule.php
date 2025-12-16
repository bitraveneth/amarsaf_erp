<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'sku',
        'type',
        'value',
        'order_type',
        'frequency',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
