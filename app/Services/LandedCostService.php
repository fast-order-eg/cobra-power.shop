<?php

namespace App\Services;

use App\Models\CustomsShipment;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;

class LandedCostService
{
    /**
     * Allocate all customs, shipping, clearance, and other landing costs
     * to shipment items and optionally update product master costs and inventory.
     */
    public function processLandedCost(CustomsShipment $shipment, array $itemsData, bool $applyToInventory = false, ?int $userId = null): CustomsShipment
    {
        return DB::transaction(function () use ($shipment, $itemsData, $applyToInventory, $userId) {
            // Delete existing items
            $shipment->items()->delete();

            $totalGoodsCost = 0;
            foreach ($itemsData as $item) {
                $qty = (float)($item['quantity'] ?? 0);
                $price = (float)($item['unit_purchase_price'] ?? 0);
                $totalGoodsCost += ($qty * $price);
            }

            $extraCosts = (float)$shipment->customs_fees + (float)$shipment->shipping_fees + (float)$shipment->clearance_fees + (float)$shipment->other_fees;
            $shipment->total_goods_cost = $totalGoodsCost;
            $shipment->total_landed_cost = $totalGoodsCost + $extraCosts;
            $shipment->status = $applyToInventory ? 'applied' : 'draft';
            $shipment->save();

            foreach ($itemsData as $item) {
                $qty = (float)($item['quantity'] ?? 0);
                $unitPrice = (float)($item['unit_purchase_price'] ?? 0);
                $itemBaseTotal = $qty * $unitPrice;

                $allocatedExtra = 0;
                if ($totalGoodsCost > 0) {
                    $ratio = $itemBaseTotal / $totalGoodsCost;
                    $allocatedExtra = $extraCosts * $ratio;
                }

                $allocatedPerUnit = $qty > 0 ? ($allocatedExtra / $qty) : 0;
                $finalUnitCost = $unitPrice + $allocatedPerUnit;

                $shipmentItem = $shipment->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $qty,
                    'unit_purchase_price' => $unitPrice,
                    'allocated_customs_cost' => $allocatedPerUnit,
                    'final_unit_cost' => $finalUnitCost,
                    'total_item_cost' => $finalUnitCost * $qty,
                ]);

                if ($applyToInventory && $shipmentItem->product) {
                    $prod = $shipmentItem->product;
                    $qtyBefore = (float)$prod->stock_quantity;
                    $qtyAfter = $qtyBefore + $qty;

                    // Update product costs and stock
                    $prod->purchase_price = $unitPrice;
                    $prod->customs_cost_per_unit = $allocatedPerUnit;
                    $prod->actual_cost = $finalUnitCost;
                    $prod->stock_quantity = $qtyAfter;
                    $prod->save();

                    // Inventory log
                    InventoryLog::create([
                        'product_id' => $prod->id,
                        'type' => 'purchase',
                        'quantity_change' => $qty,
                        'quantity_before' => $qtyBefore,
                        'quantity_after' => $qtyAfter,
                        'reference_type' => 'شحنة جمارك واستيراد',
                        'reference_id' => $shipment->id,
                        'notes' => "توريد شحنة رقم {$shipment->shipment_number} مع احتساب كلفة الجمارك والشحن",
                        'user_id' => $userId,
                    ]);
                }
            }

            return $shipment->fresh(['items.product']);
        });
    }
}
