@extends('layouts.app')

@section('title', 'تقرير الأرباح والخسائر')

@section('content')
<div class="container-fluid px-0" style="max-width: 950px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div>
            <h4 class="fw-bold mb-1">قائمة الأرباح والخسائر الشاملة ⚖️</h4>
            <p class="text-muted small mb-0">تحليل الإيرادات، تكلفة البضاعة المباعة (COGS)، المصروفات التشغيلية، وصافي الربح</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-dark rounded-3 px-3 shadow-sm">
            <i class="fa-solid fa-print ms-1"></i> طباعة القائمة 🖨️
        </button>
    </div>

    <!-- Date Filter -->
    <div class="card-custom p-3 mb-4 no-print">
        <form method="GET" action="{{ route('reports.profit-loss') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted mb-1">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
            </div>
            <div class="col-md-2 d-flex align-items-end mt-auto">
                <button type="submit" class="btn btn-primary w-100 rounded-3">تحليل الأرباح</button>
            </div>
        </form>
    </div>

    <!-- Income Statement Sheet -->
    <div class="card-custom p-4 p-md-5 bg-white">
        <div class="text-center border-bottom pb-3 mb-4">
            <h5 class="fw-bold text-primary mb-1">مؤسسة قوة الكوبرا للأدوات الصحية والسباكة</h5>
            <h6 class="fw-bold text-dark mb-1">قائمة الدخل والأرباح التشغيلية</h6>
            <small class="text-muted">عن الفترة من: <strong>{{ $fromDate }}</strong> إلى: <strong>{{ $toDate }}</strong> (المبالغ بالدينار الأردني د.أ)</small>
        </div>

        <!-- 1. Revenue Section -->
        <div class="mb-4">
            <div class="p-2 bg-light rounded-3 fw-bold text-primary d-flex justify-content-between mb-2">
                <span>1. إيرادات المبيعات (Revenue)</span>
                <span>المبلغ</span>
            </div>
            <div class="ps-3 pe-2">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">إجمالي فواتير المبيعات (شاملة الضريبة):</span>
                    <span>{{ number_format($totalSales, 3) }} د.أ</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom text-muted">
                    <span>يُخصم: ضريبة المبيعات العامة المحصلة (أمانات ضريبية):</span>
                    <span>-{{ number_format($taxAmount, 3) }} د.أ</span>
                </div>
                <div class="d-flex justify-content-between py-2 fw-bold text-dark">
                    <span>صافي إيراد المبيعات الفعلي:</span>
                    <span>{{ number_format($netSales, 3) }} د.أ</span>
                </div>
            </div>
        </div>

        <!-- 2. Cost of Goods Sold (COGS) -->
        <div class="mb-4">
            <div class="p-2 bg-light rounded-3 fw-bold text-danger d-flex justify-content-between mb-2">
                <span>2. تكلفة البضاعة المباعة (كلفة الشراء + الجمارك والشحن)</span>
                <span>المبلغ</span>
            </div>
            <div class="ps-3 pe-2">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">إجمالي كلفة البضاعة المباعة الفعلية (COGS):</span>
                    <span class="text-danger">-{{ number_format($cogs, 3) }} د.أ</span>
                </div>
            </div>
        </div>

        <!-- Gross Profit Banner -->
        <div class="p-3 rounded-3 bg-primary-subtle text-primary border border-primary-subtle d-flex justify-content-between align-items-center mb-4">
            <div>
                <h6 class="fw-bold mb-0">إجمالي الربح التجاري (مجمل الربح Gross Profit):</h6>
                <small class="text-muted">نسبة هامش مجمل الربح: <strong>{{ $grossMarginPercent }}%</strong></small>
            </div>
            <h4 class="fw-bold mb-0 text-primary">{{ number_format($grossProfit, 3) }} د.أ</h4>
        </div>

        <!-- 3. Operating Expenses -->
        <div class="mb-4">
            <div class="p-2 bg-light rounded-3 fw-bold text-warning-emphasis d-flex justify-content-between mb-2">
                <span>3. المصروفات التشغيلية والرواتب (Operating Expenses)</span>
                <span>المبلغ</span>
            </div>
            <div class="ps-3 pe-2">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted"><i class="fa-solid fa-gas-pump ms-1 text-warning"></i> ديزل ومحروقات المركبات:</span>
                    <span class="text-danger">-{{ number_format($dieselExpenses, 3) }} د.أ</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted"><i class="fa-solid fa-people-carry-box ms-1 text-primary"></i> أجور عمال ومياومة ورواتب:</span>
                    <span class="text-danger">-{{ number_format($laborExpenses, 3) }} د.أ</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted"><i class="fa-solid fa-utensils ms-1 text-success"></i> طعام وإعاشة عمال المستودع:</span>
                    <span class="text-danger">-{{ number_format($foodExpenses, 3) }} د.أ</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted"><i class="fa-solid fa-hand-holding-dollar ms-1 text-purple"></i> سلف موظفين منصرفة بالفترة:</span>
                    <span class="text-danger">-{{ number_format($advancesExpenses, 3) }} د.أ</span>
                </div>
                @if($otherExpenses > 0)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="fa-solid fa-ellipsis ms-1"></i> مصروفات أخرى متنوعة:</span>
                        <span class="text-danger">-{{ number_format($otherExpenses, 3) }} د.أ</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between py-2 fw-bold text-dark">
                    <span>مجموع المصروفات التشغيلية:</span>
                    <span class="text-danger">-{{ number_format($totalExpenses, 3) }} د.أ</span>
                </div>
            </div>
        </div>

        <!-- Net Profit Final Box -->
        <div class="p-4 rounded-4 {{ $netProfit >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }} shadow-sm d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">
                    {{ $netProfit >= 0 ? 'صافي الربح النهائي (Net Profit)' : 'صافي الخسارة (Net Loss)' }}
                </h5>
                <small class="text-white-50">نسبة صافي هامش الربح من المبيعات: <strong>{{ $netMarginPercent }}%</strong></small>
            </div>
            <h2 class="fw-bold mb-0 text-white">{{ number_format($netProfit, 3) }} د.أ</h2>
        </div>
    </div>
</div>
@endsection
