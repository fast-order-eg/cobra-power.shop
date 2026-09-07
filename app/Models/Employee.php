<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];

    protected $casts = [
        'basic_salary' => 'decimal:3',
        'hire_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = $value ? str_replace(' ', '', $value) : null;
    }

    public function getPhoneAttribute($value)
    {
        return $value ? str_replace(' ', '', $value) : null;
    }

    public function advances()
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public function salarySlips()
    {
        return $this->hasMany(SalarySlip::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function getPendingAdvancesTotalAttribute(): float
    {
        return (float)$this->advances()->where('status', 'pending')->sum('amount');
    }
}
