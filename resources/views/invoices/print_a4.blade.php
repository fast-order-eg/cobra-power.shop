<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة ضريبية رسمية - {{ $invoice->invoice_number }}</title>
    <!-- Google Fonts Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #e2e8f0;
            color: #1e293b;
            padding: 30px 15px;
            display: flex;
            justify-content: center;
        }
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            padding: 20mm 18mm;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .header-box {
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .invoice-title-badge {
            background: #1e3a8a;
            color: #ffffff;
            padding: 8px 24px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 800;
            display: inline-block;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #1e3a8a;
            color: #ffffff;
            font-weight: 700;
            padding: 10px;
            font-size: 13px;
            border: 1px solid #1e3a8a;
        }
        td {
            padding: 10px;
            font-size: 13px;
            border: 1px solid #cbd5e1;
        }
        .totals-table {
            width: 320px;
            margin-right: auto;
            margin-left: 0;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
        }
        .sig-box {
            width: 200px;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
        }
        .sig-line {
            margin-top: 40px;
            border-bottom: 1px solid #94a3b8;
        }
        @media print {
            body { background: transparent; padding: 0; }
            .a4-page { width: 100%; min-height: auto; box-shadow: none; padding: 10mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="a4-page">
        <div>
            <!-- Header -->
            <div class="header-box">
                <div>
                    <h2 style="color: #1e3a8a; font-weight: 900; margin-bottom: 4px;">{{ $setting->company_name }}</h2>
                    <p style="font-size: 13px; color: #64748b;">تجارة واستيراد وتوزيع الأدوات الصحية والسباكة واللوازم الإنشائية</p>
                    <p style="font-size: 13px; color: #475569;">{{ $setting->address }} | هاتف: {{ $setting->phone }}</p>
                    <p style="font-size: 14px; font-weight: bold; color: #1e3a8a; margin-top: 4px;">
                        الرقم الضريبي: {{ $setting->tax_number }} | السجل التجاري: {{ $setting->commercial_registry }}
                    </p>
                </div>
                <div style="text-align: left;">
                    <div class="invoice-title-badge mb-2">
                        @if($invoice->invoice_type === 'tax')
                            فاتورة ضريبية رسمية
                        @else
                            فاتورة بيع نقدية
                        @endif
                    </div>
                    <div id="qrcode" style="display: inline-block; padding: 4px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;"></div>
                </div>
            </div>

            <!-- Customer & Invoice Info Box -->
            <div class="info-grid">
                <div>
                    <p style="font-size: 12px; color: #64748b; font-weight: bold;">معلومات العميل / المشتري:</p>
                    <h4 style="font-weight: 800; color: #0f172a; margin-top: 2px;">{{ $invoice->customer_name ?? 'زبون نقدي عام' }}</h4>
                    @if($invoice->customer)
                        <p style="font-size: 13px; color: #334155; margin-top: 2px;">الرقم الضريبي / الوطني: {{ $invoice->customer->tax_number ?? $invoice->customer->national_id ?? '-' }}</p>
                        <p style="font-size: 13px; color: #334155;">العنوان: {{ $invoice->customer->address ?? '-' }} | الهاتف: {{ $invoice->customer->phone ?? '-' }}</p>
                    @endif
                </div>
                <div style="text-align: left;">
                    <p style="font-size: 13px;">رقم الفاتورة: <strong style="font-size: 16px; color: #1e3a8a;">{{ $invoice->invoice_number }}</strong></p>
                    <p style="font-size: 13px;">تاريخ الإصدار: <strong>{{ $invoice->invoice_date->format('Y/m/d') }}</strong></p>
                    <p style="font-size: 13px;">طريقة الدفع: <strong>{{ $invoice->payment_method_name }}</strong></p>
                    <p style="font-size: 13px;">العملة: <strong>دينار أردني (JOD)</strong></p>
                </div>
            </div>

            <!-- Items Table -->
            <table>
                <thead>
                    <tr style="text-align: center;">
                        <th style="width: 40px;">#</th>
                        <th style="text-align: right;">بيان المواد والأصناف</th>
                        <th style="width: 80px;">الكمية</th>
                        <th style="width: 120px;">سعر الوحدة (د.أ)</th>
                        <th style="width: 120px;">المجموع الإجمالي (د.أ)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                            </td>
                            <td style="text-align: center; font-weight: bold;">{{ (int)$item->quantity }}</td>
                            <td style="text-align: center;">{{ number_format($item->unit_price, 3) }}</td>
                            <td style="text-align: left; font-weight: bold;">{{ number_format($item->total_price, 3) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals Table -->
            <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                <table class="totals-table">
                    <tr>
                        <td>المجموع قبل الضريبة:</td>
                        <td style="text-align: left; font-weight: bold;">{{ number_format($invoice->subtotal, 3) }} د.أ</td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                        <tr style="color: #dc2626;">
                            <td>الخصم الممنوح:</td>
                            <td style="text-align: left; font-weight: bold;">-{{ number_format($invoice->discount_amount, 3) }} د.أ</td>
                        </tr>
                    @endif
                    @if($invoice->invoice_type === 'tax')
                        <tr style="color: #1e3a8a; background: #eff6ff;">
                            <td>ضريبة المبيعات العامة (16%):</td>
                            <td style="text-align: left; font-weight: bold;">{{ number_format($invoice->tax_amount, 3) }} د.أ</td>
                        </tr>
                    @endif
                    <tr style="background: #1e3a8a; color: #ffffff; font-size: 15px;">
                        <td style="font-weight: 800;">المجموع الصافي النهائي:</td>
                        <td style="text-align: left; font-weight: 900;">{{ number_format($invoice->total_amount, 3) }} د.أ</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer & Signatures -->
        <div>
            <div class="signatures">
                <div class="sig-box">
                    <span>توقيع وختم المستلم</span>
                    <div class="sig-line"></div>
                </div>
                <div class="sig-box">
                    <span>أمين الصندوق / المبيعات</span>
                    <div class="sig-line"></div>
                </div>
                <div class="sig-box">
                    <span>المحاسب المسؤول</span>
                    <div class="sig-line"></div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 25px; padding-top: 15px; border-top: 1px solid #cbd5e1; font-size: 11px; color: #64748b;">
                <p>{{ $setting->invoice_footer_text }}</p>
                <p style="margin-top: 2px;">صدرت هذه الفاتورة إلكترونياً عبر نظام <strong>قوة الكوبرا</strong> للمحاسبة والمستودعات 🇯🇴</p>
            </div>

            <div class="no-print" style="text-align: center; margin-top: 20px;">
                <button onclick="window.print()" style="padding: 10px 30px; background: #1e3a8a; color: #fff; font-weight: bold; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">
                    طباعة الفاتورة الرسمية (A4) 🖨️
                </button>
            </div>
        </div>
    </div>

    <script>
        const qrPayload = {!! json_encode($invoice->qr_payload ?? $invoice->generateJordanQrPayload()) !!};
        new QRCode(document.getElementById("qrcode"), {
            text: qrPayload,
            width: 85,
            height: 85,
            correctLevel: QRCode.CorrectLevel.M
        });
    </script>
</body>
</html>
