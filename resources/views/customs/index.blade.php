@extends('layouts.app')

@section('title', 'كلفة البضائع والجمارك')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">حساب كلفة البضائع والجمارك 🚢</h4>
            <p class="text-muted small mb-0">نظام احتساب تكاليف الشحنات والاستيراد وتوزيع مصاريف الجمارك والشحن على المنتجات (Landed Cost)</p>
        </div>
        <a href="{{ route('customs.create') }}" class="btn btn-warning text-dark fw-bold rounded-3 px-3 shadow-sm">
            <i class="fa-solid fa-calculator ms-1"></i> شحنة جديدة واحتساب كلفة
        </a>
    </div>

    <!-- Shipments Table -->
    <div class="card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الشحنة</th>
                        <th>المورد / المصنع</th>
                        <th>تاريخ الشحنة</th>
                        <th>بلد المنشأ</th>
                        <th>كلفة البضاعة</th>
                        <th>المصاريف والجمارك</th>
                        <th>إجمالي الكلفة الكلية</th>
                        <th>الحالة</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $shipment)
                        <tr>
                            <td>
                                <a href="{{ route('customs.show', $shipment) }}" class="fw-bold text-decoration-none text-primary">
                                    {{ $shipment->shipment_number }}
                                </a>
                            </td>
                            <td class="fw-bold">{{ $shipment->supplier_name }}</td>
                            <td>{{ $shipment->shipment_date->format('Y/m/d') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $shipment->origin_country ?? 'مستورد' }}</span></td>
                            <td>{{ number_format($shipment->total_goods_cost, 3) }} د.أ</td>
                            <td>
                                @php
                                    $extra = (float)$shipment->customs_fees + (float)$shipment->shipping_fees + (float)$shipment->clearance_fees + (float)$shipment->other_fees;
                                @endphp
                                <span class="badge badge-customs">+{{ number_format($extra, 3) }} د.أ</span>
                            </td>
                            <td class="fw-bold text-dark fs-6">{{ number_format($shipment->total_landed_cost, 3) }} د.أ</td>
                            <td>
                                @if($shipment->status === 'applied')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fa-solid fa-check-double ms-1"></i> مرحلة للمخزون
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1">مسودة احتساب</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('customs.show', $shipment) }}" class="btn btn-outline-primary btn-sm rounded-3">
                                    <i class="fa-solid fa-eye ms-1"></i> تفاصيل التوزيع
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-ship fs-1 mb-3 text-secondary"></i>
                                <h6>لا توجد شحنات مسجلة حالياً</h6>
                                <p class="small">سجّل شحنة استيراد جديدة لتوزيع تكاليف الجمارك والشحن على أسعار القطع تلقائياً.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $shipments->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
