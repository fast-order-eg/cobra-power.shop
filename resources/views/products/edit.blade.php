@extends('layouts.app')

@section('title', 'تعديل المنتج')

@section('content')
<div class="container-fluid px-0" style="max-width: 900px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">تعديل بيانات المنتج: {{ $product->name }} ✏️</h4>
            <p class="text-muted small mb-0">تحديث الأسعار، كلفة الجمارك، والحد الأدنى للتنبيه</p>
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
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label fw-bold small">اسم المنتج / الصنف <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">الصنف الرئيسي <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold small">كود المنتج (Item Code)</label>
                    <input type="text" name="code" class="form-control font-monospace" value="{{ old('code', $product->code) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">الباركود (Barcode)</label>
                    <input type="text" name="barcode" class="form-control font-monospace" value="{{ old('barcode', $product->barcode) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">وحدة القياس <span class="text-danger">*</span></label>
                    <select name="unit" class="form-select" required>
                        <option value="قطعة" {{ old('unit', $product->unit) == 'قطعة' ? 'selected' : '' }}>قطعة</option>
                        <option value="حبة" {{ old('unit', $product->unit) == 'حبة' ? 'selected' : '' }}>حبة</option>
                        <option value="طقم" {{ old('unit', $product->unit) == 'طقم' ? 'selected' : '' }}>طقم</option>
                        <option value="كرتونة" {{ old('unit', $product->unit) == 'كرتونة' ? 'selected' : '' }}>كرتونة</option>
                        <option value="متر" {{ old('unit', $product->unit) == 'متر' ? 'selected' : '' }}>متر</option>
                    </select>
                </div>
            </div>

            <hr class="my-4">
            <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-coins ms-1"></i> احتساب التكاليف وأسعار البيع (بالدينار الأردني د.أ)</h6>

            <div class="row g-3 mb-4 bg-light p-3 rounded-4 border">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">سعر الشراء الأساسي (د.أ) <span class="text-danger">*</span></label>
                    <input type="number" step="0.001" min="0" name="purchase_price" id="inputPurchasePrice" class="form-control text-primary fw-bold" value="{{ old('purchase_price', (float)$product->purchase_price) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">كلفة الجمارك والشحن للقطعة (د.أ)</label>
                    <input type="number" step="0.001" min="0" name="customs_cost_per_unit" id="inputCustomsCost" class="form-control text-warning fw-bold" value="{{ old('customs_cost_per_unit', (float)$product->customs_cost_per_unit) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">الكلفة الفعلية المحتسبة (د.أ)</label>
                    <input type="text" id="displayActualCost" class="form-control bg-white fw-bold text-dark" readonly value="{{ number_format($product->actual_cost, 3) }} د.أ">
                </div>

                <div class="col-md-6 mt-3">
                    <label class="form-label fw-bold small text-success">سعر البيع للزبون (د.أ) <span class="text-danger">*</span></label>
                    <input type="number" step="0.001" min="0" name="selling_price" id="inputSellingPrice" class="form-control text-success fw-bold fs-5" value="{{ old('selling_price', (float)$product->selling_price) }}" required>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label fw-bold small">أقل سعر بيع مسموح (الحد الأدنى د.أ)</label>
                    <input type="number" step="0.001" min="0" name="min_selling_price" class="form-control" value="{{ old('min_selling_price', (float)$product->min_selling_price) }}">
                </div>
            </div>

            <hr class="my-4">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold small">حد التنبيه عند نقص المخزون</label>
                    <input type="number" step="1" min="0" name="min_stock_alert" class="form-control" value="{{ old('min_stock_alert', $product->min_stock_alert) }}">
                </div>
                <div class="col-md-6 d-flex align-items-center pt-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small" for="isActiveSwitch">المنتج متاح ونشط للبيع</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold small">صورة المنتج</label>
                    @if($product->image_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" style="max-height: 80px; border-radius: 8px;">
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold small">الوصف والمواصفات</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('products.index') }}" class="btn btn-light rounded-3 px-4">إلغاء</a>
                <button type="submit" class="btn btn-primary rounded-3 px-5 fw-bold">
                    <i class="fa-solid fa-check ms-1"></i> حفظ التعديلات
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
</script>
@endpush
