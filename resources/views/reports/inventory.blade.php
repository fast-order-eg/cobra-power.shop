@extends('layouts.app')

@section('title', 'تقرير جرد وقيمة المخزون')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div>
            <h4 class="fw-bold mb-1">تقرير جرد وتقييم المخزون 🏬</h4>
            <p class="text-muted small mb-0">حصر الكميات الحالية وتقييم بضاعة المستودع بسعر التكلفة الفعلية وسعر البيع المتوقع</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.inventory.export-excel') }}" class="btn btn-success rounded-3 px-3 shadow-sm">
                <i class="fa-solid fa-file-excel ms-1"></i> تحميل ملف إكسيل (Excel) 📊
            </a>
            <button onclick="window.print()" class="btn btn-outline-dark rounded-3 px-3 shadow-sm">
                <i class="fa-solid fa-print ms-1"></i> طباعة كشف الجرد 🖨️
            </button>
        </div>
    </div>

    <!-- Inventory Valuation Summary Cards -->
    <div class="row g-3 mb-4 no-print">
        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">إجمالي عدد الأصناف والقطع</small>
                <h3 class="fw-bold mb-0 text-dark mt-1">{{ number_format($totalStockQty, 0) }} <small class="fs-6 text-muted">قطعة</small></h3>
                <small class="text-muted">موزعة على {{ $totalItems }} صنف مختلف</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light border-primary border-2">
                <small class="text-muted fw-bold">القيمة الإجمالية للمخزون (بسعر التكلفة الفعلية)</small>
                <h3 class="fw-bold mb-0 text-primary mt-1">{{ number_format($totalCostValue, 3) }} <small class="fs-6">د.أ</small></h3>
                <small class="text-muted">شامل سعر الشراء + الجمارك والشحن</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light border-success border-2">
                <small class="text-muted fw-bold">القيمة التقديرية للمخزون (بسعر البيع)</small>
                <h3 class="fw-bold mb-0 text-success mt-1">{{ number_format($totalSellingValue, 3) }} <small class="fs-6">د.أ</small></h3>
                <small class="text-muted">القيمة السوقية للبضاعة عند البيع</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">الربح المتوقع المحتجز بالمخزون</small>
                <h3 class="fw-bold mb-0 text-warning-emphasis mt-1">+{{ number_format($expectedProfit, 3) }} <small class="fs-6 text-muted">د.أ</small></h3>
                <small class="text-muted">فارق سعر البيع عن التكلفة</small>
            </div>
        </div>
    </div>

    <!-- Print Only Header -->
    <div class="d-none d-print-block text-center border-bottom pb-3 mb-4">
        <h3 class="fw-bold mb-1 text-dark">مؤسسة قوة الكوبرا للأدوات الصحية والسباكة</h3>
        <h5 class="fw-bold mb-2 text-dark">تقرير جرد وتقييم بضاعة المستودع والمخزون</h5>
        <div class="d-flex justify-content-between small text-dark px-2">
            <span>إجمالي الأصناف: <strong>{{ $totalItems }} صنف ({{ number_format($totalStockQty, 0) }} قطعة)</strong></span>
            <span>إجمالي قيمة التكلفة: <strong>{{ number_format($totalCostValue, 3) }} د.أ</strong></span>
            <span>إجمالي القيمة البيعية: <strong>{{ number_format($totalSellingValue, 3) }} د.أ</strong></span>
            <span>تاريخ الطباعة: <strong>{{ date('Y/m/d H:i') }}</strong></span>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المنتج / الصنف</th>
                        <th>الكود / الباركود</th>
                        <th>التصنيف</th>
                        <th>الرصيد المتوفر</th>
                        <th>الكلفة الفعلية للقطعة</th>
                        <th>إجمالي التكلفة</th>
                        <th>سعر البيع للقطعة</th>
                        <th>إجمالي قيمة البيع</th>
                        <th>الربح المتوقع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        @php
                            $rowCost = (float)$product->stock_quantity * (float)$product->actual_cost;
                            $rowSelling = (float)$product->stock_quantity * (float)$product->selling_price;
                            $rowProfit = $rowSelling - $rowCost;
                        @endphp
                        <tr>
                            <td>
                                <strong class="text-dark">{{ $product->name }}</strong>
                                <small class="d-block text-muted">{{ $product->unit }}</small>
                            </td>
                            <td class="font-monospace text-muted">{{ $product->code }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $product->category->name ?? 'عام' }}</span></td>
                            <td>
                                @if($product->stock_quantity <= 0)
                                    <span class="badge bg-danger">نفد (0)</span>
                                @elseif($product->is_low_stock)
                                    <span class="badge bg-warning text-dark">{{ (int)$product->stock_quantity }} (منخفض)</span>
                                @else
                                    <span class="badge bg-success">{{ (int)$product->stock_quantity }}</span>
                                @endif
                            </td>
                            <td>{{ number_format($product->actual_cost, 3) }} د.أ</td>
                            <td class="fw-bold text-primary">{{ number_format($rowCost, 3) }} د.أ</td>
                            <td>{{ number_format($product->selling_price, 3) }} د.أ</td>
                            <td class="fw-bold text-dark">{{ number_format($rowSelling, 3) }} د.أ</td>
                            <td class="text-success fw-bold">+{{ number_format($rowProfit, 3) }} د.أ</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-start">الإجمالي العام:</td>
                        <td>{{ number_format($totalStockQty, 0) }} قطعة</td>
                        <td>-</td>
                        <td class="text-primary fs-6">{{ number_format($totalCostValue, 3) }} د.أ</td>
                        <td>-</td>
                        <td class="text-dark fs-6">{{ number_format($totalSellingValue, 3) }} د.أ</td>
                        <td class="text-success fs-6">+{{ number_format($expectedProfit, 3) }} د.أ</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
