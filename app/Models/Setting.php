<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'tax_enabled_default' => 'boolean',
    ];

    public static function instance(): self
    {
        return self::firstOrCreate([], [
            'company_name' => 'مؤسسة قوة الكوبرا للأدوات الصحية والسباكة',
            'tax_number' => '123456789',
            'commercial_registry' => 'CR-789456',
            'phone' => '+962 7 9000 0000',
            'email' => 'info@cobra-power.shop',
            'address' => 'عمان - المملكة الأردنية الهاشمية',
            'currency_name' => 'دينار أردني',
            'currency_symbol' => 'د.أ',
            'tax_rate' => 16.00,
            'tax_enabled_default' => true,
            'invoice_footer_text' => 'شكراً لتعاملكم معنا - البضاعة المباعة لا ترد ولا تستبدل بعد 3 أيام إلا بوجود الفاتورة الأصلية',
        ]);
    }

    public static function formatMoney(float|string|null $amount): string
    {
        $amt = (float)($amount ?? 0);
        return number_format($amt, 3, '.', ',') . ' د.أ';
    }
}
