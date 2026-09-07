<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Invoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:3',
        'discount_amount' => 'decimal:3',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:3',
        'total_amount' => 'decimal:3',
        'paid_amount' => 'decimal:3',
        'remaining_amount' => 'decimal:3',
        'total_cost' => 'decimal:3',
        'profit_margin' => 'decimal:3',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeTaxInvoices($query)
    {
        return $query->where('invoice_type', 'tax');
    }

    public function scopeNonTaxInvoices($query)
    {
        return $query->where('invoice_type', 'non_tax');
    }

    public function getIsTaxInvoiceAttribute(): bool
    {
        return $this->invoice_type === 'tax';
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'نقداً (كاش)',
            'card' => 'بطاقة مصرفية / فيزا',
            'credit' => 'ذمة / آجل',
            'bank' => 'تحويل بنكي',
            default => 'نقداً',
        };
    }

    /**
     * Generates standard Jordan ISTD QR Code payload.
     * Contains seller name, tax number, timestamp, total with tax, and tax amount.
     */
    public function generateJordanQrPayload(): string
    {
        $setting = Setting::instance();
        $sellerName = $setting->company_name;
        $taxNumber = $setting->tax_number ?? '123456789';
        $timestamp = $this->created_at ? $this->created_at->toIso8601String() : now()->toIso8601String();
        $total = number_format((float)$this->total_amount, 3, '.', '');
        $tax = number_format((float)$this->tax_amount, 3, '.', '');
        $invNo = $this->invoice_number;

        // Structured Jordan format or TLV base64
        $qrText = "مؤسسة: {$sellerName}\nالرقم الضريبي: {$taxNumber}\nرقم الفاتورة: {$invNo}\nالتاريخ: {$timestamp}\nالإجمالي: {$total} د.أ\nالضريبة: {$tax} د.أ";
        
        $this->qr_payload = $qrText;
        return $qrText;
    }
}
