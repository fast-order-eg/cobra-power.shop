<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال حراري - {{ $invoice->invoice_number }}</title>
    <!-- Google Fonts Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #f1f5f9;
            color: #000000;
            font-size: 12px;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .receipt-container {
            width: 80mm;
            background: #ffffff;
            padding: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .text-end { text-align: left; }
        .text-start { text-align: right; }
        .fw-bold { font-weight: bold; }
        .border-bottom-dashed { border-bottom: 1px dashed #000; }
        .border-top-dashed { border-top: 1px dashed #000; }
        .py-1 { padding-top: 4px; padding-bottom: 4px; }
        .my-2 { margin-top: 8px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 6px 0; }
        th, td { padding: 4px 2px; font-size: 11px; }
        .d-flex { display: flex; justify-content: space-between; }
        
        @media print {
            body { background: transparent; padding: 0; }
            .receipt-container { width: 80mm; box-shadow: none; padding: 4px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        <!-- Header -->
        <div class="text-center">
            <h3 class="fw-bold" style="font-size: 16px; margin-bottom: 2px;">{{ $setting->company_name }}</h3>
            <p style="font-size: 10px;">{{ $setting->address }}</p>
            <p style="font-size: 10px;">هاتف: {{ $setting->phone }}</p>
            @if($setting->tax_number)
                <p class="fw-bold" style="font-size: 11px;">الرقم الضريبي: {{ $setting->tax_number }}</p>
            @endif
        </div>

        <div class="border-bottom-dashed my-2"></div>

        <!-- Invoice Type & Details -->
        <div class="text-center py-1">
            @if($invoice->invoice_type === 'tax')
                <h4 class="fw-bold" style="font-size: 13px;">فاتورة ضريبية مبسطة</h4>
            @else
                <h4 class="fw-bold" style="font-size: 13px;">إيصال بيع نقدي (غير ضريبي)</h4>
            @endif
        </div>

        <div class="py-1" style="font-size: 11px;">
            <div class="d-flex">
                <span>رقم الفاتورة:</span>
                <span class="fw-bold">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="d-flex">
                <span>التاريخ والوقت:</span>
                <span>{{ $invoice->created_at->format('Y/m/d H:i') }}</span>
            </div>
            <div class="d-flex">
                <span>العميل:</span>
                <span class="fw-bold">{{ $invoice->customer_name ?? 'زبون نقدي' }}</span>
            </div>
            <div class="d-flex">
                <span>طريقة الدفع:</span>
                <span>{{ $invoice->payment_method_name }}</span>
            </div>
        </div>

        <div class="border-bottom-dashed my-2"></div>

        <!-- Items -->
        <table>
            <thead>
                <tr class="border-bottom-dashed text-center">
                    <th class="text-start">الصنف</th>
                    <th style="width: 25px;">الكمية</th>
                    <th style="width: 45px;">السعر</th>
                    <th class="text-end" style="width: 50px;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td class="text-start">{{ $item->product_name }}</td>
                        <td class="text-center">{{ (int)$item->quantity }}</td>
                        <td class="text-center">{{ number_format($item->unit_price, 3) }}</td>
                        <td class="text-end fw-bold">{{ number_format($item->total_price, 3) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-bottom-dashed my-2"></div>

        <!-- Totals -->
        <div class="py-1" style="font-size: 11px;">
            <div class="d-flex">
                <span>المجموع:</span>
                <span>{{ number_format($invoice->subtotal, 3) }} د.أ</span>
            </div>

            @if($invoice->discount_amount > 0)
                <div class="d-flex">
                    <span>الخصم:</span>
                    <span>-{{ number_format($invoice->discount_amount, 3) }} د.أ</span>
                </div>
            @endif

            @if($invoice->invoice_type === 'tax')
                <div class="d-flex fw-bold">
                    <span>ضريبة المبيعات (16%):</span>
                    <span>{{ number_format($invoice->tax_amount, 3) }} د.أ</span>
                </div>
            @endif

            <div class="border-bottom-dashed my-2"></div>

            <div class="d-flex fw-bold" style="font-size: 14px;">
                <span>المطلوب (الصافي):</span>
                <span>{{ number_format($invoice->total_amount, 3) }} د.أ</span>
            </div>
        </div>

        <!-- Jordan Tax QR Code -->
        <div class="text-center my-2">
            <div id="qrcode" style="display: inline-block; padding: 4px; border: 1px solid #ddd; background: #fff;"></div>
            <p style="font-size: 9px; margin-top: 3px;">التحقق من الفاتورة الضريبية الأردنية</p>
        </div>

        <div class="border-top-dashed my-2"></div>

        <div class="text-center" style="font-size: 10px; color: #555;">
            <p>{{ $setting->invoice_footer_text }}</p>
            <p style="margin-top: 4px;">نظام قوة الكوبرا السحابي</p>
        </div>

        <div class="text-center no-print" style="margin-top: 15px;">
            <button onclick="window.print()" style="padding: 6px 16px; background: #1e3a8a; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
                طباعة الفاتورة 🖨️
            </button>
        </div>
    </div>

    <script>
        const qrPayload = {!! json_encode($invoice->qr_payload ?? $invoice->generateJordanQrPayload()) !!};
        new QRCode(document.getElementById("qrcode"), {
            text: qrPayload,
            width: 90,
            height: 90,
            correctLevel: QRCode.CorrectLevel.M
        });

        // Auto print trigger
        window.addEventListener('load', function () {
            // setTimeout(() => window.print(), 500);
        });
    </script>
</body>
</html>
