@extends('layouts.app')

@section('title', 'شحنة جديدة واحتساب كلفة الجمارك')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">شحنة جديدة وحاسبة كلفة الجمارك (Landed Cost) 🧮</h4>
            <p class="text-muted small mb-0">إدخال تكاليف الشحن والتخليص وتوزيعها بنسبة وتناسب على أسعار القطع الفعلية</p>
        </div>
        <a href="{{ route('customs.index') }}" class="btn btn-outline-secondary rounded-3">
            <i class="fa-solid fa-arrow-right ms-1"></i> رجوع للشحنات
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-3 shadow-sm mb-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customs.store') }}" method="POST" id="landedCostForm">
        @csrf

        <!-- Shipment Info & Extra Landing Costs -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card-custom p-4 h-100">
                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-file-contract ms-1"></i> بيانات الشحنة والمورد</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">رقم الشحنة / البوليصة <span class="text-danger">*</span></label>
                            <input type="text" name="shipment_number" class="form-control font-monospace" placeholder="SHP-2026-001" value="{{ old('shipment_number', 'SHP-' . date('Y') . '-' . rand(100, 999)) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">اسم المورد / المصنع <span class="text-danger">*</span></label>
                            <input type="text" name="supplier_name" class="form-control" placeholder="مثال: مصنع سيروزا للسخانات" value="{{ old('supplier_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">تاريخ وصول الشحنة <span class="text-danger">*</span></label>
                            <input type="date" name="shipment_date" class="form-control" value="{{ old('shipment_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">بلد المنشأ / التوريد</label>
                            <input type="text" name="origin_country" class="form-control" placeholder="مثال: السعودية، تركيا، الصين" value="{{ old('origin_country') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">ملاحظات الشحنة</label>
                            <input type="text" name="notes" class="form-control" placeholder="رقم الحاوية أو تفاصيل البضاعة...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extra Landing Costs Section -->
            <div class="col-lg-6">
                <div class="card-custom p-4 h-100 border-warning border-2">
                    <h6 class="fw-bold text-warning-emphasis mb-3"><i class="fa-solid fa-money-bill-transfer ms-1"></i> مصاريف الجمارك والاستيراد المضافة (د.أ)</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">الرسوم والضرائب الجمركية (د.أ) <span class="text-danger">*</span></label>
                            <input type="number" step="0.001" min="0" name="customs_fees" id="customsFees" class="form-control cost-input fw-bold text-danger" value="0.000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">أجور الشحن والنقل (بري/بحري) <span class="text-danger">*</span></label>
                            <input type="number" step="0.001" min="0" name="shipping_fees" id="shippingFees" class="form-control cost-input fw-bold text-warning-emphasis" value="0.000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">أتعاب التخليص الجمركي (د.أ) <span class="text-danger">*</span></label>
                            <input type="number" step="0.001" min="0" name="clearance_fees" id="clearanceFees" class="form-control cost-input fw-bold" value="0.000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">مصاريف عمال ومستندات أخرى (د.أ)</label>
                            <input type="number" step="0.001" min="0" name="other_fees" id="otherFees" class="form-control cost-input" value="0.000">
                        </div>
                        <div class="col-12 mt-3">
                            <div class="p-3 bg-warning-subtle rounded-3 d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">مجموع المصاريف الجمركية المضافة:</span>
                                <span class="fw-bold fs-5 text-dark" id="totalExtraCostsDisplay">0.000 د.أ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products in Shipment Table -->
        <div class="card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-boxes-packing text-primary ms-1"></i> بنود البضاعة في الشحنة وتوزيع الكلفة</h6>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-3" id="btnAddRow">
                    <i class="fa-solid fa-plus ms-1"></i> إضافة بند إضافي
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="itemsTable">
                    <thead class="table-light">
                        <tr class="text-center small fw-bold">
                            <th style="min-width: 250px;">المنتج</th>
                            <th style="width: 120px;">الكمية</th>
                            <th style="width: 150px;">سعر الشراء بالفاتورة (د.أ)</th>
                            <th style="width: 140px;">إجمالي الشراء (د.أ)</th>
                            <th style="width: 150px;">نصيب القطعة من الجمارك</th>
                            <th style="width: 150px;">الكلفة النهائية للقطعة</th>
                            <th style="width: 50px;">حذف</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="item-row">
                            <td>
                                <select name="items[0][product_id]" class="form-select product-select" required>
                                    <option value="">-- اختر المنتج --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->purchase_price }}">{{ $p->name }} ({{ $p->code }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="1" min="1" name="items[0][quantity]" class="form-control text-center item-qty" value="10" required>
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0" name="items[0][unit_purchase_price]" class="form-control text-center item-price" value="20.000" required>
                            </td>
                            <td class="text-center fw-bold row-base-total">200.000 د.أ</td>
                            <td class="text-center text-warning fw-bold row-allocated-customs">0.000 د.أ</td>
                            <td class="text-center text-success fw-bold fs-6 row-final-cost">20.000 د.أ</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-3 btn-remove-row"><i class="fa-solid fa-xmark"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Grand Totals Summary -->
            <div class="row g-3 mt-3 justify-content-end">
                <div class="col-lg-5 col-md-7">
                    <div class="p-3 bg-light rounded-4 border">
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">إجمالي كلفة البضاعة في الفاتورة:</span>
                            <span class="fw-bold" id="grandGoodsCost">0.000 د.أ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">إجمالي الجمارك والشحن المضاف:</span>
                            <span class="fw-bold text-warning" id="grandExtraCost">0.000 د.أ</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-5">
                            <span class="fw-bold text-dark">الكلفة الإجمالية للشحنة (Landed Cost):</span>
                            <span class="fw-bold text-primary" id="grandTotalLanded">0.000 د.أ</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 pt-3 border-top gap-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="apply_inventory" id="applyInventoryCheck" value="1" checked>
                    <label class="form-check-label fw-bold text-dark" for="applyInventoryCheck">
                        ترحيل الشحنة للمستودع وتحديث أرصدة وتكاليف المنتجات الفعلية فور الحفظ
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('customs.index') }}" class="btn btn-light rounded-3 px-4">إلغاء</a>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-3 px-5 shadow-sm">
                        <i class="fa-solid fa-check-double ms-1"></i> حفظ وتوزيع الكلفة
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let rowIndex = 1;
    const productsList = {!! json_encode($products) !!};

    function recalculateAll() {
        const customs = parseFloat(document.getElementById('customsFees').value) || 0;
        const shipping = parseFloat(document.getElementById('shippingFees').value) || 0;
        const clearance = parseFloat(document.getElementById('clearanceFees').value) || 0;
        const other = parseFloat(document.getElementById('otherFees').value) || 0;
        const totalExtra = customs + shipping + clearance + other;

        document.getElementById('totalExtraCostsDisplay').innerText = totalExtra.toFixed(3) + ' د.أ';
        document.getElementById('grandExtraCost').innerText = totalExtra.toFixed(3) + ' د.أ';

        // 1. Calculate total goods cost
        let totalGoodsCost = 0;
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const baseTotal = qty * price;
            totalGoodsCost += baseTotal;
            row.querySelector('.row-base-total').innerText = baseTotal.toFixed(3) + ' د.أ';
        });

        document.getElementById('grandGoodsCost').innerText = totalGoodsCost.toFixed(3) + ' د.أ';
        document.getElementById('grandTotalLanded').innerText = (totalGoodsCost + totalExtra).toFixed(3) + ' د.أ';

        // 2. Allocate extra cost proportionally to each row
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const baseTotal = qty * price;

            let allocatedExtraPerUnit = 0;
            if (totalGoodsCost > 0 && qty > 0) {
                const ratio = baseTotal / totalGoodsCost;
                const rowExtra = totalExtra * ratio;
                allocatedExtraPerUnit = rowExtra / qty;
            }

            const finalUnitCost = price + allocatedExtraPerUnit;
            row.querySelector('.row-allocated-customs').innerText = '+' + allocatedExtraPerUnit.toFixed(3) + ' د.أ';
            row.querySelector('.row-final-cost').innerText = finalUnitCost.toFixed(3) + ' د.أ';
        });
    }

    document.querySelectorAll('.cost-input').forEach(input => {
        input.addEventListener('input', recalculateAll);
    });

    document.getElementById('itemsBody').addEventListener('input', function(e) {
        if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price')) {
            recalculateAll();
        }
    });

    document.getElementById('itemsBody').addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            const selectedOpt = e.target.options[e.target.selectedIndex];
            const price = selectedOpt.getAttribute('data-price');
            if (price) {
                const row = e.target.closest('tr');
                row.querySelector('.item-price').value = parseFloat(price).toFixed(3);
                recalculateAll();
            }
        }
    });

    document.getElementById('btnAddRow').addEventListener('click', function() {
        let optionsHtml = '<option value="">-- اختر المنتج --</option>';
        productsList.forEach(p => {
            optionsHtml += `<option value="${p.id}" data-price="${p.purchase_price}">${p.name} (${p.code})</option>`;
        });

        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required>
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="number" step="1" min="1" name="items[${rowIndex}][quantity]" class="form-control text-center item-qty" value="1" required>
            </td>
            <td>
                <input type="number" step="0.001" min="0" name="items[${rowIndex}][unit_purchase_price]" class="form-control text-center item-price" value="0.000" required>
            </td>
            <td class="text-center fw-bold row-base-total">0.000 د.أ</td>
            <td class="text-center text-warning fw-bold row-allocated-customs">+0.000 د.أ</td>
            <td class="text-center text-success fw-bold fs-6 row-final-cost">0.000 د.أ</td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-3 btn-remove-row"><i class="fa-solid fa-xmark"></i></button>
            </td>
        `;
        document.getElementById('itemsBody').appendChild(newRow);
        rowIndex++;
        recalculateAll();
    });

    document.getElementById('itemsBody').addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-row')) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
                recalculateAll();
            } else {
                alert('يجب أن تحتوي الشحنة على بند واحد على الأقل.');
            }
        }
    });

    recalculateAll();
</script>
@endpush
