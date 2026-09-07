<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomsShipment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'shipment_date' => 'date',
        'total_goods_cost' => 'decimal:3',
        'customs_fees' => 'decimal:3',
        'shipping_fees' => 'decimal:3',
        'clearance_fees' => 'decimal:3',
        'other_fees' => 'decimal:3',
        'total_landed_cost' => 'decimal:3',
    ];

    public function items()
    {
        return $this->hasMany(CustomsShipmentItem::class);
    }

    /**
     * Recalculates total extra costs (customs, shipping, clearance, others)
     * and allocates them proportionally to each product based on item value or quantity.
     */
    public function recalculateAndAllocateCosts(): void
    {
        $extraCosts = (float)$this->customs_fees + (float)$this->shipping_fees + (float)$this->clearance_fees + (float)$this->other_fees;
        $totalGoodsCost = (float)$this->items->sum(fn($item) => (float)$item->quantity * (float)$item->unit_purchase_price);

        $this->total_goods_cost = $totalGoodsCost;
        $this->total_landed_cost = $totalGoodsCost + $extraCosts;
        $this->save();

        if ($totalGoodsCost > 0) {
            foreach ($this->items as $item) {
                $itemBaseTotal = (float)$item->quantity * (float)$item->unit_purchase_price;
                $costRatio = $itemBaseTotal / $totalGoodsCost;
                $allocatedExtra = $extraCosts * $costRatio;
                $allocatedPerUnit = $item->quantity > 0 ? ($allocatedExtra / (float)$item->quantity) : 0;
                $finalUnitCost = (float)$item->unit_purchase_price + $allocatedPerUnit;

                $item->allocated_customs_cost = $allocatedPerUnit;
                $item->final_unit_cost = $finalUnitCost;
                $item->total_item_cost = $finalUnitCost * (float)$item->quantity;
                $item->save();

                // If shipment is applied, update product's actual_cost and customs_cost_per_unit in inventory
                if ($this->status === 'applied' && $item->product) {
                    $item->product->customs_cost_per_unit = $allocatedPerUnit;
                    $item->product->actual_cost = $finalUnitCost;
                    $item->product->save();
                }
            }
        }
    }
}
