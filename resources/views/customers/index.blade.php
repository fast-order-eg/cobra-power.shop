@extends('layouts.app')

@section('title', 'إدارة العملاء')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">إدارة وسجل العملاء 👥</h4>
            <p class="text-muted small mb-0">إضافة وتعديل وحذف بيانات العملاء والشركات للفوترة الضريبية ونقطة البيع POS</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="fa-solid fa-user-plus ms-1"></i> إضافة عميل جديد
            </button>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card-custom p-3 mb-4">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" value="{{ request('search') }}" placeholder="ابحث باسم العميل، رقم الهاتف، أو الرقم الضريبي/الوطني...">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3">بحث</button>
                <a href="{{ route('customers.index') }}" class="btn btn-light rounded-3"><i class="fa-solid fa-rotate"></i></a>
            </div>
        </form>
    </div>

    <!-- Customers Table Card -->
    <div class="card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>اسم العميل / الشركة</th>
                        <th>النوع</th>
                        <th>رقم الهاتف</th>
                        <th>الرقم الضريبي / الوطني</th>
                        <th>العنوان</th>
                        <th>عدد الفواتير</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong class="text-dark">{{ $customer->name }}</strong>
                            </td>
                            <td>
                                @if($customer->customer_type === 'company')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fa-solid fa-building ms-1"></i> شركة / مؤسسة</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border"><i class="fa-solid fa-user ms-1"></i> فردي</span>
                                @endif
                            </td>
                            <td class="font-monospace text-primary fw-bold">
                                {{ $customer->phone ? str_replace(' ', '', $customer->phone) : '-' }}
                            </td>
                            <td class="font-monospace text-muted">
                                {{ $customer->tax_number ? str_replace(' ', '', $customer->tax_number) : ($customer->national_id ? str_replace(' ', '', $customer->national_id) : '-') }}
                            </td>
                            <td>{{ $customer->address ?? '-' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $customer->invoices_count ?? 0 }} فاتورة</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCustomerModal{{ $customer->id }}" title="تعديل">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف العميل ({{ $customer->name }})؟ سيتم الاحتفاظ بجميع الفواتير السابقة المسجلة باسمه بشكل آمن.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="حذف">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">لا يوجد عملاء مسجلين حالياً مطابثين للبحث.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $customers->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus text-primary ms-1"></i> إضافة عميل جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('customers.store') }}" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerHTML='<i class=\'fa-solid fa-spinner fa-spin ms-1\'></i> جاري الحفظ...'; return true;">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">اسم العميل / الشركة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="أدخل الاسم الرباعي أو اسم الشركة" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">نوع العميل</label>
                            <select name="customer_type" class="form-select">
                                <option value="individual">فردي (شخص)</option>
                                <option value="company">شركة / مؤسسة تجارية</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">رقم الهاتف (بدون مسافات)</label>
                            <input type="text" name="phone" class="form-control font-monospace" placeholder="0790000000">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">الرقم الضريبي (إن وجد)</label>
                            <input type="text" name="tax_number" class="form-control font-monospace" placeholder="123456789">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">الرقم الوطني / الهوية</label>
                            <input type="text" name="national_id" class="form-control font-monospace" placeholder="9900000000">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">العنوان والتفاصيل</label>
                        <input type="text" name="address" class="form-control" placeholder="عمان - منطقة ...">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">حفظ العميل</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modals -->
@foreach($customers as $customer)
    <div class="modal fade" id="editCustomerModal{{ $customer->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered text-start">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen text-primary ms-1"></i> تعديل بيانات العميل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">اسم العميل / الشركة <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">نوع العميل</label>
                                <select name="customer_type" class="form-select">
                                    <option value="individual" {{ old('customer_type', $customer->customer_type) === 'individual' ? 'selected' : '' }}>فردي (شخص)</option>
                                    <option value="company" {{ old('customer_type', $customer->customer_type) === 'company' ? 'selected' : '' }}>شركة / مؤسسة تجارية</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">رقم الهاتف (بدون مسافات)</label>
                                <input type="text" name="phone" class="form-control font-monospace" value="{{ old('phone', str_replace(' ', '', $customer->phone)) }}">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">الرقم الضريبي (إن وجد)</label>
                                <input type="text" name="tax_number" class="form-control font-monospace" value="{{ old('tax_number', str_replace(' ', '', $customer->tax_number)) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">الرقم الوطني / الهوية</label>
                                <input type="text" name="national_id" class="form-control font-monospace" value="{{ old('national_id', str_replace(' ', '', $customer->national_id)) }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">العنوان والتفاصيل</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $customer->address) }}">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">تحديث البيانات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
