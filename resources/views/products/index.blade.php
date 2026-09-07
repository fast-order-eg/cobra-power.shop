@extends('layouts.app')

@section('title', 'المنتجات والمخزون')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">إدارة المنتجات والمستودع 📦</h4>
            <p class="text-muted small mb-0">عرض وحساب كلفة البضائع، الجمارك، أسعار البيع، وهوامش الربح</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customs.index') }}" class="btn btn-outline-warning text-dark rounded-3 px-3">
                <i class="fa-solid fa-ship ms-1"></i> احتساب كلفة الجمارك
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary rounded-3 px-3 shadow-sm">
                <i class="fa-solid fa-plus ms-1"></i> إضافة منتج جديد
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-custom p-3 mb-4">
        <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="بحث بالاسم، الكود، أو الباركود..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">-- جميع الأصناف --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="stock_status" class="form-select">
                    <option value="">-- حالة المخزون (الكل) --</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>أوشكت على النفاد (حد الطلب)</option>
                    <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>نفدت من المخزن (0)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3">فلترة</button>
                <a href="{{ route('products.index') }}" class="btn btn-light rounded-3" title="إعادة ضبط"><i class="fa-solid fa-rotate"></i></a>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المنتج</th>
                        <th>الصنف / الكود</th>
                        <th>كلفة الشراء</th>
                        <th>كلفة الجمارك</th>
                        <th>الكلفة الفعلية</th>
                        <th>سعر البيع</th>
                        <th>هامش الربح</th>
                        <th>رصيد المستودع</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center border" style="width: 48px; height: 48px; min-width: 48px;">
                                        @if($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover rounded-3">
                                        @else
                                            <i class="fa-solid fa-faucet-drip text-secondary fs-5"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">{{ $product->name }}</h6>
                                        <small class="text-muted">{{ $product->unit }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border mb-1">{{ $product->category->name ?? 'عام' }}</span>
                                <div class="small text-muted font-monospace">{{ $product->code }}</div>
                            </td>
                            <td>{{ number_format($product->purchase_price, 3) }} د.أ</td>
                            <td>
                                @if($product->customs_cost_per_unit > 0)
                                    <span class="badge badge-customs">+{{ number_format($product->customs_cost_per_unit, 3) }} د.أ</span>
                                @else
                                    <span class="text-muted small">0.000</span>
                                @endif
                            </td>
                            <td class="fw-bold text-primary">{{ number_format($product->actual_cost, 3) }} د.أ</td>
                            <td class="fw-bold text-success fs-6">{{ number_format($product->selling_price, 3) }} د.أ</td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">
                                    +{{ number_format($product->profit_per_unit, 3) }} د.أ ({{ $product->profit_margin_percent }}%)
                                </span>
                            </td>
                            <td>
                                @if($product->stock_quantity <= 0)
                                    <span class="badge bg-danger rounded-pill px-3 py-2">نفد (0)</span>
                                @elseif($product->is_low_stock)
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2" title="الحد الأدنى: {{ $product->min_stock_alert }}">
                                        {{ $product->formatted_stock }} {{ $product->unit }} (منخفض)
                                    </span>
                                @else
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        {{ $product->formatted_stock }} {{ $product->unit }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <!-- Stock Adjustment Modal Trigger -->
                                    <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#stockModal{{ $product->id }}" title="تعديل المخزون / جرد">
                                        <i class="fa-solid fa-cubes-stacked"></i>
                                    </button>
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary" title="تعديل">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا المنتج؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="حذف">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Stock Adjustment Modal -->
                                <div class="modal fade" id="stockModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <div class="modal-header bg-light">
                                                <h6 class="modal-title fw-bold">تسوية وجرد مخزون: {{ $product->name }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('products.adjust-stock', $product) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="alert alert-secondary py-2 small mb-3">
                                                        الرصيد الحالي المتوفر بالمستودع: <strong>{{ $product->formatted_stock }} {{ $product->unit }}</strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small">نوع الحركة</label>
                                                        <select name="adjustment_type" class="form-select" required>
                                                            <option value="add">إضافة كمية (توريد إضافي / مرتجع)</option>
                                                            <option value="subtract">خصم كمية (تالف / كسر / مفقود)</option>
                                                            <option value="set">تحديد رصيد الجرد الفعلي مباشرة</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small">الكمية</label>
                                                        <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" placeholder="أدخل الكمية..." required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small">ملاحظات وسبب التسوية</label>
                                                        <input type="text" name="notes" class="form-control" placeholder="مثال: جرد يدوي، كسر أثناء النقل..." required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                                                    <button type="submit" class="btn btn-primary rounded-3">حفظ التسوية</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">لم يتم العثور على أي منتجات مطابقة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
