<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomsShipmentItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_purchase_price' => 'decimal:3',
        'allocated_customs_cost' => 'decimal:3',
        'final_unit_cost' => 'decimal:3',
        'total_item_cost' => 'decimal:3',
    ];

    public function shipment()
    {
        return $this->belongsTo(CustomsShipment::class, 'customs_shipment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
