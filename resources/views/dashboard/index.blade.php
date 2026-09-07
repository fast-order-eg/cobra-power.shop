@extends('layouts.app')

@section('title', 'لوحة التحكم الرئيسية')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">لوحة الإحصائيات والتحكم 📊</h4>
            <p class="text-muted small mb-0">نظرة عامة على حركة البيع، المصروفات، والضرائب لنظام قوة الكوبرا</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.sales') }}" class="btn btn-outline-primary rounded-3 px-3">
                <i class="fa-solid fa-file-lines ms-1"></i> تقرير المبيعات المفصل
            </a>
            <a href="{{ route('pos') }}" class="btn btn-success rounded-3 px-3 shadow-sm">
                <i class="fa-solid fa-plus ms-1"></i> إصدار فاتورة جديدة
            </a>
        </div>
    </div>

    <!-- Low Stock Alert Banner -->
    @if($lowStockProducts->count() > 0)
        <div class="alert alert-warning border-warning border-2 rounded-4 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.3rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">تنبيه مستودع: يوجد {{ $lowStockProducts->count() }} أصناف وصلت لحد إعادة الطلب أو نفدت!</h6>
                    <small class="text-muted">
                        @foreach($lowStockProducts->take(3) as $lp)
                            <span class="badge bg-white text-dark border ms-1">{{ $lp->name }} (متبقي: {{ (int)$lp->stock_quantity }} {{ $lp->unit }})</span>
                        @endforeach
                    </small>
                </div>
            </div>
            <a href="{{ route('products.index', ['stock_status' => 'low']) }}" class="btn btn-warning btn-sm fw-bold px-3 rounded-3">
                معاينة المخزون <i class="fa-solid fa-arrow-left me-1"></i>
            </a>
        </div>
    @endif

    <!-- 4 Main Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Today Sales -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6);">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-white-50 small fw-bold">مبيعات اليوم</span>
                    <span class="badge bg-white text-primary rounded-pill px-2 py-1 small">اليوم</span>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($todaySales, 3) }} <small class="fs-6">د.أ</small></h3>
                <small class="text-white-50"><i class="fa-solid fa-calendar-day ms-1"></i> إجمالي حركة مبيعات اليوم</small>
                <i class="fa-solid fa-cash-register icon-box"></i>
            </div>
        </div>

        <!-- Month Sales -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm" style="background: linear-gradient(135deg, #059669, #10b981);">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-white-50 small fw-bold">مبيعات الشهر الحالي</span>
                    <span class="badge bg-white text-success rounded-pill px-2 py-1 small">{{ now()->translatedFormat('F') }}</span>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($monthSales, 3) }} <small class="fs-6">د.أ</small></h3>
                <small class="text-white-50"><i class="fa-solid fa-wallet ms-1"></i> إجمالي فواتير الشهر</small>
                <i class="fa-solid fa-chart-line icon-box"></i>
            </div>
        </div>

        <!-- Gross Profit -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm" style="background: linear-gradient(135deg, #7c3aed, #8b5cf6);">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-white-50 small fw-bold">هامش الربح التقريبي (شهري)</span>
                    <span class="badge bg-white text-purple rounded-pill px-2 py-1 small" style="color: #7c3aed;">بعد كلفة البضاعة</span>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($monthGrossProfit, 3) }} <small class="fs-6">د.أ</small></h3>
                <small class="text-white-50"><i class="fa-solid fa-scale-balanced ms-1"></i> كلفة البضائع: {{ number_format($monthCOGS, 3) }} د.أ</small>
                <i class="fa-solid fa-hand-holding-dollar icon-box"></i>
            </div>
        </div>

        <!-- Month Expenses -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm" style="background: linear-gradient(135deg, #dc2626, #ef4444);">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-white-50 small fw-bold">مصروفات الشهر (ديزل/عمال/طعام)</span>
                    <span class="badge bg-white text-danger rounded-pill px-2 py-1 small">المصروفات</span>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($monthExpenses, 3) }} <small class="fs-6">د.أ</small></h3>
                <small class="text-white-50"><i class="fa-solid fa-receipt ms-1"></i> صافي الربح التقديري: {{ number_format($monthNetProfit, 3) }} د.أ</small>
                <i class="fa-solid fa-gas-pump icon-box"></i>
            </div>
        </div>
    </div>

    <!-- Tax vs Non-Tax Invoices Comparison Card -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card-custom p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-3">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <span class="badge badge-tax mb-1"><i class="fa-solid fa-stamp ms-1"></i> فواتير ضريبية رسمية (16%)</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($taxInvoicesTotal, 3) }} <small class="fs-6 text-muted">د.أ</small></h4>
                        <small class="text-muted">العدد الإجمالي: <strong>{{ $taxInvoicesCount }}</strong> فاتورة برمز QR</small>
                    </div>
                </div>
                <a href="{{ route('invoices.index', ['invoice_type' => 'tax']) }}" class="btn btn-light btn-sm rounded-3">
                    عرض الفواتير <i class="fa-solid fa-chevron-left me-1"></i>
                </a>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card-custom p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-secondary-subtle text-secondary p-3 fs-3">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <span class="badge badge-nontax mb-1"><i class="fa-solid fa-file-lines ms-1"></i> فواتير بيع غير ضريبية (نقدية)</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($nonTaxInvoicesTotal, 3) }} <small class="fs-6 text-muted">د.أ</small></h4>
                        <small class="text-muted">العدد الإجمالي: <strong>{{ $nonTaxInvoicesCount }}</strong> فاتورة</small>
                    </div>
                </div>
                <a href="{{ route('invoices.index', ['invoice_type' => 'non_tax']) }}" class="btn btn-light btn-sm rounded-3">
                    عرض الفواتير <i class="fa-solid fa-chevron-left me-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Sales & Expenses Chart -->
        <div class="col-lg-8">
            <div class="card-custom p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-column text-primary ms-2"></i> مقارنة المبيعات والمصروفات (آخر 6 أشهر)</h6>
                    <span class="badge bg-light text-dark border">دينار أردني د.أ</span>
                </div>
                <div style="height: 300px;">
                    <canvas id="salesExpensesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Expenses Distribution Doughnut Chart -->
        <div class="col-lg-4">
            <div class="card-custom p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-pie text-danger ms-2"></i> توزيع مصروفات الشهر</h6>
                    <a href="{{ route('expenses.index') }}" class="small text-decoration-none">إدارة المصروفات</a>
                </div>
                <div style="height: 240px; position: relative;">
                    <canvas id="expensesDoughnutChart"></canvas>
                </div>
                <div class="mt-3 small border-top pt-2 text-center text-muted">
                    <span>تشمل: الديزل، العمال والمياومة، طعام وإعاشة، وسلف الرواتب</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Invoices Table -->
    <div class="card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left text-success ms-2"></i> آخر فواتير المبيعات الصادرة</h6>
                <small class="text-muted">محدث تلقائياً مع خيارات الطباعة السريعة</small>
            </div>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                عرض جميع الفواتير ({{ \App\Models\Invoice::count() }})
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>النوع</th>
                        <th>التاريخ</th>
                        <th>العميل</th>
                        <th>طريقة الدفع</th>
                        <th>المجموع الصافي</th>
                        <th>الربح التقديري</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentInvoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $inv) }}" class="fw-bold text-decoration-none text-primary">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td>
                                @if($inv->invoice_type === 'tax')
                                    <span class="badge badge-tax"><i class="fa-solid fa-stamp ms-1"></i> ضريبية 16%</span>
                                @else
                                    <span class="badge badge-nontax"><i class="fa-solid fa-file-lines ms-1"></i> غير ضريبية</span>
                                @endif
                            </td>
                            <td>{{ $inv->invoice_date->format('Y/m/d') }}</td>
                            <td>{{ $inv->customer_name ?? 'زبون نقدي' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $inv->payment_method_name }}</span></td>
                            <td class="fw-bold text-dark">{{ number_format($inv->total_amount, 3) }} د.أ</td>
                            <td class="text-success fw-bold">+{{ number_format($inv->profit_margin, 3) }} د.أ</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('invoices.show', $inv) }}" class="btn btn-outline-primary" title="عرض التفاصيل">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('invoices.print-thermal', $inv) }}" target="_blank" class="btn btn-outline-dark" title="طباعة حرارية 80mm">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                    <a href="{{ route('invoices.print-a4', $inv) }}" target="_blank" class="btn btn-outline-secondary" title="طباعة A4 رسمية">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">لا توجد فواتير مسجلة حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // 1. Sales & Expenses Bar Chart
    const ctxSales = document.getElementById('salesExpensesChart').getContext('2d');
    new Chart(ctxSales, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartMonths) !!},
            datasets: [
                {
                    label: 'إجمالي المبيعات (د.أ)',
                    data: {!! json_encode($chartSalesData) !!},
                    backgroundColor: 'rgba(37, 99, 235, 0.85)',
                    borderRadius: 8,
                },
                {
                    label: 'المصروفات التشغيلية (د.أ)',
                    data: {!! json_encode($chartExpensesData) !!},
                    backgroundColor: 'rgba(239, 68, 68, 0.85)',
                    borderRadius: 8,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { family: 'Cairo' } } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return value + ' د.أ'; },
                        font: { family: 'Cairo' }
                    }
                },
                x: {
                    ticks: { font: { family: 'Cairo' } }
                }
            }
        }
    });

    // 2. Expenses Doughnut Chart
    const ctxExp = document.getElementById('expensesDoughnutChart').getContext('2d');
    const expLabels = {!! json_encode($expenseCategories->pluck('name')) !!};
    const expData = {!! json_encode($expenseCategories->pluck('expenses_sum_amount')->map(fn($v) => (float)$v)) !!};

    new Chart(ctxExp, {
        type: 'doughnut',
        data: {
            labels: expLabels,
            datasets: [{
                data: expData,
                backgroundColor: [
                    '#f59e0b', // Diesel
                    '#3b82f6', // Labor
                    '#10b981', // Food
                    '#8b5cf6', // Advances
                    '#06b6d4', // Purchases
                    '#ec4899', // Maintenance
                    '#64748b'  // Other
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Cairo', size: 11 } } }
            }
        }
    });
</script>
@endpush
