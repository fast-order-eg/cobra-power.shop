<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class InventoryLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity_change' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'purchase' => 'شراء / توريد',
            'sale' => 'فاتورة بيع',
            'adjustment' => 'تسوية جردية',
            'damage' => 'تالف / كسر',
            'return' => 'مرتجع مبيعات',
            default => $this->type,
        };
    }
}
