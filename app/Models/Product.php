<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'purchase_price' => 'decimal:3',
        'customs_cost_per_unit' => 'decimal:3',
        'actual_cost' => 'decimal:3',
        'selling_price' => 'decimal:3',
        'min_selling_price' => 'decimal:3',
        'stock_quantity' => 'decimal:2',
        'min_stock_alert' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function shipmentItems()
    {
        return $this->hasMany(CustomsShipmentItem::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class)->latest();
    }

    public function getProfitPerUnitAttribute(): float
    {
        return max(0, (float)$this->selling_price - (float)$this->actual_cost);
    }

    public function getProfitMarginPercentAttribute(): float
    {
        if ((float)$this->selling_price <= 0) return 0.0;
        return round((($this->profit_per_unit / (float)$this->selling_price) * 100), 1);
    }

    public function getIsLowStockAttribute(): bool
    {
        return (float)$this->stock_quantity <= (float)$this->min_stock_alert;
    }

    public function getFormattedStockAttribute(): string
    {
        $val = (float)$this->stock_quantity;
        return ($val == (int)$val) ? (string)(int)$val : (string)round($val, 2);
    }
}
