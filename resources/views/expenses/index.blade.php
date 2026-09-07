@extends('layouts.app')

@section('title', 'المصروفات والتشغيل')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div>
            <h4 class="fw-bold mb-1">إدارة المصروفات والتشغيل 🧾</h4>
            <p class="text-muted small mb-0">متابعة مصروفات الديزل، أجور ويوميات العمال، الطعام، وسلف الرواتب</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('expenses.export-excel', request()->all()) }}" class="btn btn-success rounded-3 px-3 shadow-sm">
                <i class="fa-solid fa-file-excel ms-1"></i> تحميل إكسيل (Excel) 📊
            </a>
            <button onclick="window.print()" class="btn btn-outline-dark rounded-3 px-3 shadow-sm">
                <i class="fa-solid fa-print ms-1"></i> طباعة كشف المصروفات 🖨️
            </button>
            <button type="button" class="btn btn-outline-secondary rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                <i class="fa-solid fa-tags ms-1"></i> إدارة أقسام المصروفات
            </button>
            <button type="button" class="btn btn-danger rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fa-solid fa-plus ms-1"></i> تسجيل سند صرف جديد
            </button>
        </div>
    </div>

    <!-- Category Highlights Cards (Requested Specifically by User) -->
    <div class="row g-3 mb-4 no-print">
        <!-- Total -->
        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light border-danger border-2">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="text-muted small fw-bold">إجمالي المصروفات (الفلتر الحالي)</span>
                    <i class="fa-solid fa-receipt text-danger fs-4"></i>
                </div>
                <h3 class="fw-bold mb-0 text-danger mt-2">{{ number_format($totalExpenses, 3) }} <small class="fs-6">د.أ</small></h3>
            </div>
        </div>

        <!-- Diesel (ديزل) -->
        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="text-muted small fw-bold">محروقات وديزل المركبات</span>
                    <i class="fa-solid fa-gas-pump text-warning fs-4"></i>
                </div>
                <h3 class="fw-bold mb-0 text-dark mt-2">{{ number_format($dieselTotal, 3) }} <small class="fs-6 text-muted">د.أ</small></h3>
                <small class="text-muted">سيارات التوزيع والمولدات</small>
            </div>
        </div>

        <!-- Labor (عمال ومياومة) -->
        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="text-muted small fw-bold">أجور وعمال مياومة وتعتيق</span>
                    <i class="fa-solid fa-people-carry-box text-primary fs-4"></i>
                </div>
                <h3 class="fw-bold mb-0 text-dark mt-2">{{ number_format($laborTotal, 3) }} <small class="fs-6 text-muted">د.أ</small></h3>
                <small class="text-muted">تنزيل الحاويات والعتالة</small>
            </div>
        </div>

        <!-- Food (طعام وإعاشة) -->
        <div class="col-xl-3 col-md-6">
            <div class="card-custom p-3 bg-light">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="text-muted small fw-bold">طعام وإعاشة العمال</span>
                    <i class="fa-solid fa-utensils text-success fs-4"></i>
                </div>
                <h3 class="fw-bold mb-0 text-dark mt-2">{{ number_format($foodTotal, 3) }} <small class="fs-6 text-muted">د.أ</small></h3>
                <small class="text-muted">وجبات المستودع والميدان</small>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-custom p-3 mb-4 no-print">
        <form method="GET" action="{{ route('expenses.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">-- جميع أقسام المصروفات --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="payment_method" class="form-select">
                    <option value="">-- طريقة الدفع (الكل) --</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>نقداً من الصندوق (كاش)</option>
                    <option value="bank" {{ request('payment_method') === 'bank' ? 'selected' : '' }}>حساب بنكي</option>
                    <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>تحويل كليك / فوري</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="من تاريخ">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="إلى تاريخ">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-danger w-100 rounded-3">تطبيق</button>
                <a href="{{ route('expenses.index') }}" class="btn btn-light rounded-3"><i class="fa-solid fa-rotate"></i></a>
            </div>
        </form>
    </div>

    <!-- Print Only Header -->
    <div class="d-none d-print-block text-center border-bottom pb-3 mb-4">
        <h3 class="fw-bold mb-1 text-dark">مؤسسة قوة الكوبرا للأدوات الصحية والسباكة</h3>
        <h5 class="fw-bold mb-2 text-dark">كشف وسجل المصروفات وسندات الصرف التشغيلية</h5>
        <div class="d-flex justify-content-between small text-dark px-2">
            <span>الفترة: <strong>{{ request('from_date', 'البداية') }} إلى {{ request('to_date', date('Y-m-d')) }}</strong></span>
            <span>عدد السندات: <strong>{{ $expenses->total() }} سند</strong></span>
            <span>إجمالي المصروفات: <strong>{{ number_format($totalExpenses, 3) }} د.أ</strong></span>
            <span>تاريخ الطباعة: <strong>{{ date('Y/m/d H:i') }}</strong></span>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>التاريخ</th>
                        <th>بيان المصروف</th>
                        <th>القسم / التصنيف</th>
                        <th>المبلغ (د.أ)</th>
                        <th>الموظف المستلم</th>
                        <th>طريقة الدفع</th>
                        <th>المرفق</th>
                        <th class="text-center no-print">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('Y/m/d') }}</td>
                            <td>
                                <strong class="text-dark">{{ $expense->title }}</strong>
                                @if($expense->notes)
                                    <small class="d-block text-muted">{{ $expense->notes }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($expense->category->code ?? '') {
                                        'diesel' => 'bg-warning text-dark',
                                        'labor' => 'bg-primary text-white',
                                        'food' => 'bg-success text-white',
                                        'employee_advances' => 'bg-purple text-white',
                                        default => 'bg-secondary text-white',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2 py-1">
                                    {{ $expense->category->name ?? 'عام' }}
                                </span>
                            </td>
                            <td class="fw-bold text-danger fs-6">{{ number_format($expense->amount, 3) }} د.أ</td>
                            <td>{{ $expense->employee->name ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $expense->payment_method === 'cash' ? 'كاش' : 'تحويل/بنك' }}</span></td>
                            <td>
                                @if($expense->attachment_path)
                                    <a href="{{ asset('storage/' . $expense->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fa-solid fa-paperclip ms-1"></i> عرض
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center no-print">
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا المصروف؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">لا توجد مصروفات مسجلة مطابقة للفلتر.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($expenses->count() > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-start">الإجمالي العام للمصروفات:</td>
                            <td class="text-danger fs-6">{{ number_format($totalExpenses, 3) }} د.أ</td>
                            <td colspan="3"></td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="mt-4 no-print">
            {{ $expenses->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <h6 class="modal-title fw-bold">تسجيل سند صرف ومصروف جديد</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">قسم المصروف <span class="text-danger">*</span></label>
                        <select name="expense_category_id" class="form-select" required>
                            <option value="">-- اختر القسم --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">بيان المصروف / العنوان <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="مثال: تعبئة ديزل سيارة التوزيع، وجبة غداء عمال..." required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">المبلغ (د.أ) <span class="text-danger">*</span></label>
                            <input type="number" step="0.001" min="0.001" name="amount" class="form-control text-danger fw-bold fs-5" placeholder="0.000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">طريقة الدفع</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">نقداً من الصندوق (كاش)</option>
                                <option value="bank">حساب بنكي / شيك</option>
                                <option value="transfer">تحويل كليك / فوري</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">الموظف المرتبط (اختياري)</label>
                            <select name="employee_id" class="form-select">
                                <option value="">-- بدون موظف محدد --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->job_title }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">مرفق الفاتورة أو الإيصال (صورة/PDF)</label>
                        <input type="file" name="attachment" class="form-control" accept="image/*,application/pdf">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">ملاحظات إضافية</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="أي تفاصيل أخرى عن المصروف..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold">حفظ وسحب من الصندوق</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Expense Categories Modal -->
<div class="modal fade" id="manageCategoriesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg text-start">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-tags text-primary ms-1"></i> إدارة وتخصيص أقسام المصروفات</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Add New Category Form -->
                <div class="bg-light p-3 rounded-4 border mb-4">
                    <h6 class="fw-bold mb-2 small text-dark"><i class="fa-solid fa-plus-circle text-success ms-1"></i> إضافة قسم مصروف جديد</h6>
                    <form action="{{ route('expense-categories.store') }}" method="POST" class="row g-2 align-items-center">
                        @csrf
                        <div class="col-md-9">
                            <input type="text" name="name" class="form-control" placeholder="اسم القسم الجديد (مثال: إيجار المستودع، كهرباء ومياه، دعاية وإعلان...)" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success w-100 rounded-3 fw-bold">
                                <i class="fa-solid fa-plus ms-1"></i> إضافة القسم
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Categories List -->
                <h6 class="fw-bold mb-3 small text-muted"><i class="fa-solid fa-list ms-1"></i> الأقسام والتصنيفات الحالية</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>اسم القسم</th>
                                <th>السندات المسجلة</th>
                                <th class="text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $cat)
                                <tr>
                                    <td>
                                        <strong class="text-dark">{{ $cat->name }}</strong>
                                    </td>
                                    <td>
                                        @if(($cat->expenses_count ?? 0) > 0)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-receipt ms-1"></i> {{ $cat->expenses_count }} سند مسجل</span>
                                        @else
                                            <span class="badge bg-light text-muted border">0 سند</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Edit Trigger -->
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editExpenseCatModal{{ $cat->id }}" title="تعديل الاسم">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>

                                            <!-- Delete Button -->
                                            <form action="{{ route('expense-categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف قسم ({{ $cat->name }})؟ @if(($cat->expenses_count ?? 0) > 0)\nملاحظة هامة: سيتم نقل جميع السندات التابعة له وعددهم ({{ $cat->expenses_count }} سند) تلقائياً إلى قسم (مصروفات أخرى متنوعة) دون حذف أي بيانات أو مبالغ مالية.@endif')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="حذف القسم">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modals for Each Category -->
@foreach($categories as $cat)
    <div class="modal fade" id="editExpenseCatModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered text-start">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold">تعديل اسم قسم المصروف: {{ $cat->name }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('expense-categories.update', $cat) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">اسم القسم <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">حفظ التعديل</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
