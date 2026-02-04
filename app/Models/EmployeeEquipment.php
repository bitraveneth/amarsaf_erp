<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEquipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'effective_date',
        'product_name',
        'device_identifier',
        'status',
        'notes',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
