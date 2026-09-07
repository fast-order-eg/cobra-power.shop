<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:3',
        'unit_price' => 'decimal:3',
        'discount' => 'decimal:3',
        'tax_amount' => 'decimal:3',
        'total_price' => 'decimal:3',
        'total_cost' => 'decimal:3',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
