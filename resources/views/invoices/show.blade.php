@extends('layouts.app')

@section('title', 'فاتورة مبيعات ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid px-0" style="max-width: 900px;">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0">فاتورة رقم: {{ $invoice->invoice_number }}</h4>
                @if($invoice->invoice_type === 'tax')
                    <span class="badge badge-tax"><i class="fa-solid fa-stamp ms-1"></i> فاتورة ضريبية رسمية (16%)</span>
                @else
                    <span class="badge badge-nontax"><i class="fa-solid fa-file-lines ms-1"></i> فاتورة غير ضريبية</span>
                @endif
            </div>
            <small class="text-muted">تاريخ الإصدار: {{ $invoice->invoice_date->format('Y/m/d') }} | الكاشير: {{ $invoice->creator->name ?? 'المدير' }}</small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('invoices.print-thermal', $invoice) }}" target="_blank" class="btn btn-dark rounded-3 px-3">
                <i class="fa-solid fa-print ms-1"></i> طباعة كاشير (80mm)
            </a>
            <a href="{{ route('invoices.print-a4', $invoice) }}" target="_blank" class="btn btn-primary rounded-3 px-3 shadow-sm">
                <i class="fa-solid fa-file-pdf ms-1"></i> طباعة فاتورة A4
            </a>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="fa-solid fa-arrow-right ms-1"></i> رجوع
            </a>
        </div>
    </div>

    <!-- Jordan Tax Sync Card (if Tax Invoice) -->
    @if($invoice->invoice_type === 'tax')
        <div class="card-custom p-3 mb-4 border-primary border-2 bg-light">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary text-white p-3 fs-4">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">نظام الفوترة الوطني الأردني (ISTD)</h6>
                        <small class="text-muted">
                            الحالة: 
                            @if($invoice->jordan_tax_status === 'synced')
                                <span class="badge bg-success ms-1"><i class="fa-solid fa-check ms-1"></i> معتمدة ومطابقة للضريبة (الرقم المرجعي: {{ $invoice->jordan_tax_invoice_id }})</span>
                            @else
                                <span class="badge bg-warning text-dark ms-1">جاهزة للرفع والتوثيق</span>
                            @endif
                        </small>
                    </div>
                </div>

                <form action="{{ route('invoices.sync-tax', $invoice) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm rounded-3 fw-bold">
                        <i class="fa-solid fa-rotate ms-1"></i> إعادة فحص ومزامنة الضريبة
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Invoice Details Sheet -->
    <div class="card-custom p-4 p-md-5 mb-4 bg-white">
        <!-- Header -->
        <div class="row align-items-center border-bottom pb-4 mb-4">
            <div class="col-sm-7">
                <h5 class="fw-bold text-primary mb-1">{{ $setting->company_name }}</h5>
                <p class="text-muted small mb-1">{{ $setting->address }}</p>
                <p class="text-muted small mb-0">هاتف: <strong>{{ $setting->phone }}</strong> | الرقم الضريبي: <strong class="text-dark">{{ $setting->tax_number }}</strong></p>
            </div>
            <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                <div class="p-2 border rounded-3 d-inline-block text-center bg-light">
                    <div id="qrcodeDisplay"></div>
                    <small class="d-block text-muted mt-1" style="font-size: 0.7rem;">رمز التحقق الضريبي QR</small>
                </div>
            </div>
        </div>

        <!-- Customer & Info -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <small class="text-muted fw-bold d-block mb-1">معلومات العميل / المشتري:</small>
                <h6 class="fw-bold mb-0 text-dark">{{ $invoice->customer_name ?? 'زبون نقدي عام' }}</h6>
                @if($invoice->customer)
                    <small class="text-muted">{{ $invoice->customer->phone }} | {{ $invoice->customer->address }}</small>
                @endif
            </div>
            <div class="col-sm-6 text-sm-end">
                <small class="text-muted fw-bold d-block mb-1">تفاصيل الدفع:</small>
                <h6 class="fw-bold mb-0 text-dark">{{ $invoice->payment_method_name }}</h6>
                <small class="text-muted">الرقم التسلسلي: {{ $invoice->invoice_number }}</small>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light text-center small fw-bold">
                    <tr>
                        <th class="text-start" style="width: 45%;">البيان / الصنف</th>
                        <th style="width: 15%;">الكمية</th>
                        <th style="width: 20%;">سعر الإفراد (د.أ)</th>
                        <th style="width: 20%;">المجموع (د.أ)</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="text-start">
                                <strong class="text-dark">{{ $item->product_name }}</strong>
                            </td>
                            <td>{{ (int)$item->quantity }}</td>
                            <td>{{ number_format($item->unit_price, 3) }} د.أ</td>
                            <td class="fw-bold">{{ number_format($item->total_price, 3) }} د.أ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Breakdown -->
        <div class="row justify-content-end">
            <div class="col-md-6 col-lg-5">
                <div class="bg-light p-3 rounded-4 border">
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>المجموع قبل الضريبة:</span>
                        <span class="fw-bold text-dark">{{ number_format($invoice->subtotal, 3) }} د.أ</span>
                    </div>

                    @if($invoice->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2 small text-danger">
                            <span>الخصم الممنوح:</span>
                            <span class="fw-bold">-{{ number_format($invoice->discount_amount, 3) }} د.أ</span>
                        </div>
                    @endif

                    @if($invoice->invoice_type === 'tax')
                        <div class="d-flex justify-content-between mb-2 small text-primary">
                            <span>ضريبة المبيعات العامة (16%):</span>
                            <span class="fw-bold">{{ number_format($invoice->tax_amount, 3) }} د.أ</span>
                        </div>
                    @endif

                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-5">
                        <span class="fw-bold text-dark">المجموع الصافي:</span>
                        <span class="fw-bold text-success">{{ number_format($invoice->total_amount, 3) }} د.أ</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Notes -->
        <div class="border-top pt-3 mt-4 text-center text-muted small">
            <p class="mb-0">{{ $setting->invoice_footer_text }}</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Generate QR Code on page
    const qrPayload = {!! json_encode($invoice->qr_payload ?? $invoice->generateJordanQrPayload()) !!};
    new QRCode(document.getElementById("qrcodeDisplay"), {
        text: qrPayload,
        width: 80,
        height: 80,
        correctLevel: QRCode.CorrectLevel.M
    });
</script>
@endpush
