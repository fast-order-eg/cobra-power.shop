@extends('layouts.app')

@section('title', 'سجل الفواتير والمبيعات')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">سجل فواتير المبيعات 📄</h4>
            <p class="text-muted small mb-0">متابعة الفواتير الضريبية وغير الضريبية، فلترة الضرائب، والطباعة</p>
        </div>
        <a href="{{ route('pos') }}" class="btn btn-success rounded-3 px-3 shadow-sm">
            <i class="fa-solid fa-plus ms-1"></i> نقطة البيع (POS)
        </a>
    </div>

    <!-- Summary Stats Bar -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">إجمالي المبيعات (حسب الفلتر الحالي)</small>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalSum, 3) }} د.أ</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">إجمالي ضريبة المبيعات المحصلة (16%)</small>
                <h4 class="fw-bold mb-0 text-primary">{{ number_format($taxSum, 3) }} د.أ</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">صافي الربح التقديري للفواتير</small>
                <h4 class="fw-bold mb-0 text-success">+{{ number_format($profitSum, 3) }} د.أ</h4>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-custom p-3 mb-4">
        <form method="GET" action="{{ route('invoices.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="رقم الفاتورة أو اسم العميل..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Tax / Non-Tax Filter -->
            <div class="col-md-2">
                <select name="invoice_type" class="form-select">
                    <option value="">-- نوع الفاتورة (الكل) --</option>
                    <option value="tax" {{ request('invoice_type') === 'tax' ? 'selected' : '' }}>فواتير ضريبية (16%)</option>
                    <option value="non_tax" {{ request('invoice_type') === 'non_tax' ? 'selected' : '' }}>فواتير غير ضريبية (نقدية)</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="payment_method" class="form-select">
                    <option value="">-- طريقة الدفع (الكل) --</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>كاش</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>فيزا / بطاقة</option>
                    <option value="credit" {{ request('payment_method') === 'credit' ? 'selected' : '' }}>ذمة / آجل</option>
                    <option value="bank" {{ request('payment_method') === 'bank' ? 'selected' : '' }}>تحويل بنكي</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="من تاريخ">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="إلى تاريخ">
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 rounded-3"><i class="fa-solid fa-filter"></i></button>
                <a href="{{ route('invoices.index') }}" class="btn btn-light rounded-3" title="إعادة ضبط"><i class="fa-solid fa-rotate"></i></a>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="card-custom p-4">
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
                        <th>الضريبة</th>
                        <th>الربح التقديري</th>
                        <th class="text-center">إجراءات والطباعة</th>
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
                                    <span class="badge badge-tax"><i class="fa-solid fa-stamp ms-1"></i> ضريبية 16%</span>
                                @else
                                    <span class="badge badge-nontax"><i class="fa-solid fa-file-lines ms-1"></i> غير ضريبية</span>
                                @endif
                            </td>
                            <td>{{ $inv->invoice_date->format('Y/m/d') }}</td>
                            <td>{{ $inv->customer_name ?? 'زبون نقدي' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $inv->payment_method_name }}</span></td>
                            <td class="fw-bold text-dark fs-6">{{ number_format($inv->total_amount, 3) }} د.أ</td>
                            <td>
                                @if($inv->tax_amount > 0)
                                    <span class="text-primary fw-bold">{{ number_format($inv->tax_amount, 3) }} د.أ</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-success fw-bold">+{{ number_format($inv->profit_margin, 3) }} د.أ</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('invoices.show', $inv) }}" class="btn btn-outline-primary" title="عرض الفاتورة">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('invoices.print-thermal', $inv) }}" target="_blank" class="btn btn-outline-dark" title="طباعة كاشير حرارية 80mm">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                    <a href="{{ route('invoices.print-a4', $inv) }}" target="_blank" class="btn btn-outline-secondary" title="طباعة فاتورة رسمية A4">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">لا توجد فواتير مطابقة للبحث أو الفلتر المحدد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $invoices->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
