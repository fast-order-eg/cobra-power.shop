@extends('layouts.app')

@section('title', 'إضافة منتج جديد')

@section('content')
<div class="container-fluid px-0" style="max-width: 900px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">إضافة منتج جديد إلى المستودع 📦</h4>
            <p class="text-muted small mb-0">تحديد أسعار الشراء، كلفة الجمارك، سعر البيع، والحد الأدنى للتنبيه</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary rounded-3">
            <i class="fa-solid fa-arrow-right ms-1"></i> رجوع للمنتجات
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

    <div class="card-custom p-4">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label fw-bold small">اسم المنتج / الصنف <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="مثال: بطارية مغسلة تركي كروم عالية" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">الصنف الرئيسي <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- اختر الصنف --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold small">كود المنتج (Item Code)</label>
                    <input type="text" name="code" class="form-control font-monospace" placeholder="PRD-101" value="{{ old('code') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">الباركود (Barcode)</label>
                    <input type="text" name="barcode" class="form-control font-monospace" placeholder="6281001001" value="{{ old('barcode') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">وحدة القياس <span class="text-danger">*</span></label>
                    <select name="unit" class="form-select" required>
                        <option value="قطعة" {{ old('unit') == 'قطعة' ? 'selected' : '' }}>قطعة</option>
                        <option value="حبة" {{ old('unit') == 'حبة' ? 'selected' : '' }}>حبة</option>
                        <option value="طقم" {{ old('unit') == 'طقم' ? 'selected' : '' }}>طقم</option>
                        <option value="كرتونة" {{ old('unit') == 'كرتونة' ? 'selected' : '' }}>كرتونة</option>
                        <option value="متر" {{ old('unit') == 'متر' ? 'selected' : '' }}>متر</option>
                    </select>
                </div>
            </div>

            <hr class="my-4">
            <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-coins ms-1"></i> احتساب التكاليف وأسعار البيع (بالدينار الأردني د.أ)</h6>

            <div class="row g-3 mb-4 bg-light p-3 rounded-4 border">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">سعر الشراء الأساسي (د.أ) <span class="text-danger">*</span></label>
                    <input type="number" step="0.001" min="0" name="purchase_price" id="inputPurchasePrice" class="form-control text-primary fw-bold" placeholder="0.000" value="{{ old('purchase_price', '0.000') }}" required>
                    <small class="text-muted">سعر الفاتورة من المصنع/المورد</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">كلفة الجمارك والشحن للقطعة (د.أ)</label>
                    <input type="number" step="0.001" min="0" name="customs_cost_per_unit" id="inputCustomsCost" class="form-control text-warning fw-bold" placeholder="0.000" value="{{ old('customs_cost_per_unit', '0.000') }}">
                    <small class="text-muted">نصيب القطعة من الرسوم والتخليص</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">الكلفة الفعلية المحتسبة (د.أ)</label>
                    <input type="text" id="displayActualCost" class="form-control bg-white fw-bold text-dark" readonly value="0.000 د.أ">
                    <small class="text-success fw-bold">الشراء + الجمارك</small>
                </div>

                <div class="col-md-6 mt-3">
                    <label class="form-label fw-bold small text-success">سعر البيع للزبون (د.أ) <span class="text-danger">*</span></label>
                    <input type="number" step="0.001" min="0" name="selling_price" id="inputSellingPrice" class="form-control text-success fw-bold fs-5" placeholder="0.000" value="{{ old('selling_price') }}" required>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label fw-bold small">أقل سعر بيع مسموح (الحد الأدنى د.أ)</label>
                    <input type="number" step="0.001" min="0" name="min_selling_price" class="form-control" placeholder="0.000" value="{{ old('min_selling_price') }}">
                </div>
            </div>

            <hr class="my-4">
            <h6 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-boxes-stacked ms-1"></i> أرصدة المستودع والوسائط</h6>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold small">الكمية الافتتاحية في المستودع <span class="text-danger">*</span></label>
                    <input type="number" step="1" min="0" name="stock_quantity" class="form-control" placeholder="مثال: 50" value="{{ old('stock_quantity', '0') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">حد التنبيه عند نقص المخزون</label>
                    <input type="number" step="1" min="0" name="min_stock_alert" class="form-control" placeholder="5" value="{{ old('min_stock_alert', '5') }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold small">صورة المنتج</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold small">الوصف / المواصفات الفنية</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="تفاصيل المنتج والضمان والمنشأ...">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('products.index') }}" class="btn btn-light rounded-3 px-4">إلغاء</a>
                <button type="submit" class="btn btn-primary rounded-3 px-5 fw-bold">
                    <i class="fa-solid fa-check ms-1"></i> حفظ المنتج
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateActualCost() {
        const purchase = parseFloat(document.getElementById('inputPurchasePrice').value) || 0;
        const customs = parseFloat(document.getElementById('inputCustomsCost').value) || 0;
        const total = purchase + customs;
        document.getElementById('displayActualCost').value = total.toFixed(3) + ' د.أ';
    }

    document.getElementById('inputPurchasePrice').addEventListener('input', updateActualCost);
    document.getElementById('inputCustomsCost').addEventListener('input', updateActualCost);
    updateActualCost();
</script>
@endpush
