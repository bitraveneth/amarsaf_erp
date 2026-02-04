<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reference',
        'start_date',
        'end_date',
        'working_schedule',
        'salary_amount',
        'travel_allowance',
        'dearness_allowance',
        'bonus',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'salary_amount' => 'decimal:2',
        'travel_allowance' => 'decimal:2',
        'dearness_allowance' => 'decimal:2',
        'bonus' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
