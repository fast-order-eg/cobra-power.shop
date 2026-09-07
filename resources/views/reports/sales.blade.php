@extends('layouts.app')

@section('title', 'تقارير المبيعات والضريبة')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div>
            <h4 class="fw-bold mb-1">تقارير وإحصائيات المبيعات 📈</h4>
            <p class="text-muted small mb-0">تقارير دورية وفلترة دقيقة للفواتير الضريبية وغير الضريبية وحساب الأرباح</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.sales.export-excel', request()->all()) }}" class="btn btn-success rounded-3 px-3 shadow-sm">
                <i class="fa-solid fa-file-excel ms-1"></i> تحميل ملف إكسيل (Excel) 📊
            </a>
            <button onclick="window.print()" class="btn btn-outline-dark rounded-3 px-3 shadow-sm">
                <i class="fa-solid fa-print ms-1"></i> طباعة التقرير 🖨️
            </button>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card-custom p-3 mb-4 no-print">
        <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 align-items-center">
            <!-- Period Selector -->
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">الفترة الزمنية</label>
                <select name="period" class="form-select" id="periodSelect" onchange="toggleCustomDates(this.value)">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>تقرير مبيعات اليوم</option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>تقرير الأسبوع الحالي</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>تقرير الشهر الحالي ({{ now()->translatedFormat('F') }})</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>تقرير السنة الحالية ({{ date('Y') }})</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>مخصص بفترة محددة (تاريخ من / إلى)</option>
                </select>
            </div>

            <!-- Tax Filter (Essential Feature) -->
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">تصنيف الفواتير</label>
                <select name="invoice_type" class="form-select">
                    <option value="all" {{ $invoiceType === 'all' ? 'selected' : '' }}>-- جميع الفواتير (الإجمالي) --</option>
                    <option value="tax" {{ $invoiceType === 'tax' ? 'selected' : '' }}>فواتير ضريبية فقط (16%)</option>
                    <option value="non_tax" {{ $invoiceType === 'non_tax' ? 'selected' : '' }}>فواتير غير ضريبية فقط (نقدية)</option>
                </select>
            </div>

            <div class="col-md-2" id="fromDateCol" style="{{ $period === 'custom' ? '' : 'display:none;' }}">
                <label class="form-label small fw-bold text-muted mb-1">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
            </div>

            <div class="col-md-2" id="toDateCol" style="{{ $period === 'custom' ? '' : 'display:none;' }}">
                <label class="form-label small fw-bold text-muted mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
            </div>

            <div class="col-md-2 d-flex align-items-end mt-auto">
                <button type="submit" class="btn btn-primary w-100 rounded-3">
                    <i class="fa-solid fa-filter ms-1"></i> استخراج التقرير
                </button>
            </div>
        </form>
    </div>

    <!-- Aggregate Metric Cards -->
    <div class="row g-3 mb-4 no-print">
        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light border-primary border-2">
                <small class="text-muted fw-bold">إجمالي المبيعات المحققة</small>
                <h3 class="fw-bold mb-0 text-primary mt-1">{{ number_format($totalSales, 3) }} <small class="fs-6">د.أ</small></h3>
                <small class="text-muted">عدد الفواتير: <strong>{{ $totalInvoicesCount }}</strong> فاتورة</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">ضريبة المبيعات المحصلة (16%)</small>
                <h3 class="fw-bold mb-0 text-dark mt-1">{{ number_format($totalTax, 3) }} <small class="fs-6 text-muted">د.أ</small></h3>
                <small class="text-muted">من الفواتير الضريبية المعتمدة</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">كلفة البضاعة المباعة (COGS)</small>
                <h3 class="fw-bold mb-0 text-danger mt-1">{{ number_format($totalCost, 3) }} <small class="fs-6 text-muted">د.أ</small></h3>
                <small class="text-muted">تشمل الشراء والجمارك</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light border-success border-2">
                <small class="text-muted fw-bold">هامش الربح الإجمالي التقديري</small>
                <h3 class="fw-bold mb-0 text-success mt-1">+{{ number_format($totalProfit, 3) }} <small class="fs-6">د.أ</small></h3>
                <small class="text-muted">قبل خصم المصروفات التشغيلية</small>
            </div>
        </div>
    </div>

    <!-- Tax vs Non-Tax Quick Comparison Box -->
    <div class="row g-3 mb-4 no-print">
        <div class="col-md-6">
            <div class="card-custom p-3 bg-white d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-tax mb-1"><i class="fa-solid fa-stamp ms-1"></i> فواتير ضريبية بالفترة</span>
                    <h5 class="fw-bold mb-0 text-dark">{{ number_format($taxInvoicesTotal, 3) }} د.أ</h5>
                </div>
                <span class="badge bg-light text-dark border fs-6">{{ $taxInvoicesCount }} فاتورة</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-custom p-3 bg-white d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-nontax mb-1"><i class="fa-solid fa-file-lines ms-1"></i> فواتير غير ضريبية بالفترة</span>
                    <h5 class="fw-bold mb-0 text-dark">{{ number_format($nonTaxInvoicesTotal, 3) }} د.أ</h5>
                </div>
                <span class="badge bg-light text-dark border fs-6">{{ $nonTaxInvoicesCount }} فاتورة</span>
            </div>
        </div>
    </div>

    <!-- Print Only Header -->
    <div class="d-none d-print-block text-center border-bottom pb-3 mb-4">
        <h3 class="fw-bold mb-1 text-dark">مؤسسة قوة الكوبرا للأدوات الصحية والسباكة</h3>
        <h5 class="fw-bold mb-2 text-dark">
            تقرير مبيعات الفواتير 
            @if($invoiceType === 'tax') (الضريبية 16%) 
            @elseif($invoiceType === 'non_tax') (غير الضريبية - النقدية) 
            @else (الشامل - ضريبية وغير ضريبية) 
            @endif
        </h5>
        <div class="d-flex justify-content-between small text-dark px-2">
            <span>الفترة الزمنية: <strong>من {{ $fromDate }} إلى {{ $toDate }}</strong></span>
            <span>عدد الفواتير: <strong>{{ $totalInvoicesCount }} فاتورة</strong></span>
            <span>إجمالي المبيعات: <strong>{{ number_format($totalSales, 3) }} د.أ</strong></span>
            <span>تاريخ الطباعة: <strong>{{ date('Y/m/d H:i') }}</strong></span>
        </div>
    </div>

    <!-- Invoices List Details Table -->
    <div class="card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-list-check text-primary ms-1"></i> تفاصيل فواتير الفترة من ({{ $fromDate }}) إلى ({{ $toDate }})</h6>
            <span class="badge bg-light text-dark border">إجمالي {{ $invoices->count() }} فاتورة</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>النوع</th>
                        <th>التاريخ</th>
                        <th>العميل</th>
                        <th>المجموع قبل الضريبة</th>
                        <th>الخصم</th>
                        <th>الضريبة 16%</th>
                        <th>الصافي النهائي</th>
                        <th>الكلفة</th>
                        <th>الربح</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $inv) }}" class="fw-bold text-decoration-none text-primary font-monospace">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td>
                                @if($inv->invoice_type === 'tax')
                                    <span class="badge badge-tax">ضريبية</span>
                                @else
                                    <span class="badge badge-nontax">غير ضريبية</span>
                                @endif
                            </td>
                            <td>{{ $inv->invoice_date->format('Y/m/d') }}</td>
                            <td>{{ $inv->customer_name ?? 'زبون نقدي' }}</td>
                            <td>{{ number_format($inv->subtotal, 3) }}</td>
                            <td class="text-danger">{{ $inv->discount_amount > 0 ? '-' . number_format($inv->discount_amount, 3) : '-' }}</td>
                            <td class="text-primary">{{ $inv->tax_amount > 0 ? number_format($inv->tax_amount, 3) : '-' }}</td>
                            <td class="fw-bold text-dark">{{ number_format($inv->total_amount, 3) }} د.أ</td>
                            <td class="text-muted">{{ number_format($inv->total_cost, 3) }}</td>
                            <td class="text-success fw-bold">+{{ number_format($inv->profit_margin, 3) }} د.أ</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">لا توجد مبيعات مسجلة في هذه الفترة الزمنية.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($invoices->count() > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-start">الإجمالي الكلي:</td>
                            <td>{{ number_format($totalSubtotal, 3) }} د.أ</td>
                            <td class="text-danger">-{{ number_format($totalDiscount, 3) }} د.أ</td>
                            <td class="text-primary">{{ number_format($totalTax, 3) }} د.أ</td>
                            <td class="text-dark fs-6">{{ number_format($totalSales, 3) }} د.أ</td>
                            <td class="text-muted">{{ number_format($totalCost, 3) }} د.أ</td>
                            <td class="text-success fs-6">+{{ number_format($totalProfit, 3) }} د.أ</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleCustomDates(val) {
        const fromCol = document.getElementById('fromDateCol');
        const toCol = document.getElementById('toDateCol');
        if (val === 'custom') {
            fromCol.style.display = 'block';
            toCol.style.display = 'block';
        } else {
            fromCol.style.display = 'none';
            toCol.style.display = 'none';
        }
    }
</script>
@endpush
