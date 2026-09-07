@extends('layouts.app')

@section('title', 'تفاصيل الشحنة والكلفة الجمركية')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0">شحنة استيراد: {{ $shipment->shipment_number }}</h4>
                @if($shipment->status === 'applied')
                    <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-check ms-1"></i> مرحلة للمستودع</span>
                @else
                    <span class="badge bg-warning text-dark px-3 py-2">مسودة احتساب</span>
                @endif
            </div>
            <p class="text-muted small mb-0">المورد: <strong>{{ $shipment->supplier_name }}</strong> | بلد المنشأ: <strong>{{ $shipment->origin_country ?? 'مستورد' }}</strong> | تاريخ الشحنة: <strong>{{ $shipment->shipment_date ? $shipment->shipment_date->format('Y/m/d') : '-' }}</strong></p>
        </div>

        <div class="d-flex gap-2">
            @if($shipment->status === 'draft')
                <form action="{{ route('customs.apply', $shipment) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success rounded-3 px-4 shadow-sm" onclick="return confirm('هل تريد ترحيل هذه الشحنة وتحديث أسعار التكلفة والمخزون في المستودع؟')">
                        <i class="fa-solid fa-arrow-down-to-bracket ms-1"></i> ترحيل وتحديث أسعار المستودع
                    </button>
                </form>
            @endif
            <a href="{{ route('customs.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="fa-solid fa-arrow-right ms-1"></i> رجوع
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">كلفة البضاعة الأصلية بالفاتورة</small>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($shipment->total_goods_cost, 3) }} د.أ</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">الرسوم والضرائب الجمركية</small>
                <h4 class="fw-bold mb-0 text-danger">{{ number_format($shipment->customs_fees, 3) }} د.أ</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">أجور الشحن والنقل والتخليص</small>
                <h4 class="fw-bold mb-0 text-warning-emphasis">{{ number_format($shipment->shipping_fees + $shipment->clearance_fees + $shipment->other_fees, 3) }} د.أ</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-custom p-3 bg-primary text-white">
                <small class="text-white-50 fw-bold">الكلفة الإجمالية للشحنة (Landed Cost)</small>
                <h4 class="fw-bold mb-0 text-white">{{ number_format($shipment->total_landed_cost, 3) }} د.أ</h4>
            </div>
        </div>
    </div>

    <!-- Items Allocation Table -->
    <div class="card-custom p-4">
        <h6 class="fw-bold mb-3"><i class="fa-solid fa-boxes-stacked text-primary ms-1"></i> تفاصيل توزيع المصاريف والكلفة الفعلية لكل صنف</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th class="text-start">المنتج</th>
                        <th>الكمية</th>
                        <th>سعر الشراء الأساسي</th>
                        <th>نصيب القطعة من الجمارك والمصاريف</th>
                        <th>الكلفة النهائية للقطعة (د.أ)</th>
                        <th>سعر البيع الحالي</th>
                        <th>هامش الربح المتوقع للقطعة</th>
                        <th>إجمالي كلفة البند</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach($shipment->items as $item)
                        <tr>
                            <td class="text-start">
                                <h6 class="fw-bold mb-0 text-dark">{{ $item->product->name ?? 'منتج محذوف' }}</h6>
                                <small class="text-muted">{{ $item->product->code ?? '' }}</small>
                            </td>
                            <td class="fw-bold">{{ (int)$item->quantity }} {{ $item->product->unit ?? '' }}</td>
                            <td>{{ number_format($item->unit_purchase_price, 3) }} د.أ</td>
                            <td>
                                <span class="badge badge-customs">+{{ number_format($item->allocated_customs_cost, 3) }} د.أ</span>
                            </td>
                            <td class="fw-bold text-primary fs-6">{{ number_format($item->final_unit_cost, 3) }} د.أ</td>
                            <td class="fw-bold text-success">{{ number_format($item->product->selling_price ?? 0, 3) }} د.أ</td>
                            <td>
                                @php
                                    $profitPerUnit = ($item->product->selling_price ?? 0) - $item->final_unit_cost;
                                @endphp
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    +{{ number_format($profitPerUnit, 3) }} د.أ
                                </span>
                            </td>
                            <td class="fw-bold text-dark">{{ number_format($item->total_item_cost, 3) }} د.أ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
