<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalarySlip extends Model
{
    protected $guarded = [];

    protected $casts = [
        'basic_salary' => 'decimal:3',
        'advances_deducted' => 'decimal:3',
        'bonuses' => 'decimal:3',
        'deductions' => 'decimal:3',
        'net_salary' => 'decimal:3',
        'payment_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
