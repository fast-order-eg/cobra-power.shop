@extends('layouts.app')

@section('title', 'أصناف المنتجات')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">أصناف وتصنيفات المنتجات 🏷️</h4>
            <p class="text-muted small mb-0">تنظيم وتصنيف الأدوات الصحية والسباكة في المستودع ونقطة البيع</p>
        </div>
        <button type="button" class="btn btn-primary rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fa-solid fa-plus ms-1"></i> إضافة صنف جديد
        </button>
    </div>

    <!-- Categories Grid -->
    <div class="row g-3">
        @forelse($categories as $category)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card-custom p-0 h-100 d-flex flex-column justify-content-between overflow-hidden shadow-sm border">
                    <div>
                        <!-- Category Large Image Header -->
                        <div class="position-relative bg-light border-bottom d-flex align-items-center justify-content-center" style="height: 150px; overflow: hidden;">
                            @if($category->image_path)
                                <img src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover">
                            @else
                                <div class="text-center text-secondary py-4">
                                    <i class="fa-solid fa-layer-group fs-1 text-primary opacity-50 mb-2"></i>
                                    <small class="d-block text-muted">بدون صورة</small>
                                </div>
                            @endif
                            <span class="badge bg-dark bg-opacity-75 text-white rounded-pill px-3 py-2 position-absolute top-0 start-0 m-2 shadow-sm">
                                <i class="fa-solid fa-box ms-1"></i> {{ $category->products_count }} منتج
                            </span>
                        </div>

                        <!-- Category Details -->
                        <div class="p-3">
                            <h5 class="fw-bold mb-1 text-dark">{{ $category->name }}</h5>
                            <p class="text-muted small mb-0">{{ $category->description ?? 'لا يوجد وصف إضافي' }}</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top p-3 bg-light-subtle">
                        <a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="small fw-bold text-primary text-decoration-none">
                            استعراض المنتجات ({{ $category->products_count }}) <i class="fa-solid fa-arrow-left me-1"></i>
                        </a>

                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $category->id }}" title="تعديل">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا الصنف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="حذف">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered text-start">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header bg-light">
                            <h6 class="modal-title fw-bold">تعديل الصنف: {{ $category->name }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">اسم الصنف <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">الوصف</label>
                                    <textarea name="description" class="form-control" rows="2">{{ $category->description }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">صورة الصنف الحالية</label>
                                    @if($category->image_path)
                                        <div class="mb-2 rounded-3 border p-1 bg-light text-center" style="max-height: 120px; overflow: hidden;">
                                            <img src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" style="max-height: 110px; max-width: 100%; object-fit: contain;">
                                        </div>
                                    @endif
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <small class="text-muted">اختر صورة جديدة إذا كنت ترغب في استبدال الصورة الحالية.</small>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">حفظ التعديلات</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card-custom p-5 text-center text-muted">
                    <i class="fa-solid fa-tags fs-1 mb-3 text-secondary"></i>
                    <h5>لا توجد أصناف مضافة حالياً</h5>
                    <p class="small">ابدأ بإضافة أصناف جديدة مثل (بطاريات وخلاطات، مغاسل ومجالي، سخانات، كبائن شور)</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <h6 class="modal-title fw-bold">إضافة صنف منتجات جديد</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">اسم الصنف <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: بطاريات وخلاطات" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">الوصف التوضيحي</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="وصف للأصناف والمنتجات التابعة..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">صورة الصنف (اختياري)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">إضافة الصنف</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
