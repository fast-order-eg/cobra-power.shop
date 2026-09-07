<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'current_balance' => 'decimal:3',
    ];

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = $value ? str_replace(' ', '', $value) : null;
    }

    public function getPhoneAttribute($value)
    {
        return $value ? str_replace(' ', '', $value) : null;
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
