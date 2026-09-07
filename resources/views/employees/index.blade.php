@extends('layouts.app')

@section('title', 'الموظفين والرواتب والسلف')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">إدارة الموظفين والرواتب والسلف 👥</h4>
            <p class="text-muted small mb-0">متابعة رواتب العمال والموظفين، صرف السلفيات والخصومات، ومسيرات الرواتب الشهرية</p>
        </div>
        <button type="button" class="btn btn-info text-white rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="fa-solid fa-user-plus ms-1"></i> إضافة موظف جديد
        </button>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">إجمالي الرواتب الأساسية الشهرية</small>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalBasicSalaries, 3) }} د.أ</h4>
                <small class="text-muted">لجميع الموظفين على رأس عملهم ({{ $employees->where('is_active', true)->count() }} موظف)</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-custom p-3 bg-light border-warning border-2">
                <div class="d-flex justify-content-between align-items-start">
                    <small class="text-muted fw-bold">إجمالي السلف المعلقة القائمة</small>
                    <i class="fa-solid fa-hand-holding-dollar text-warning fs-4"></i>
                </div>
                <h4 class="fw-bold mb-0 text-warning-emphasis">{{ number_format($totalPendingAdvances, 3) }} د.أ</h4>
                <small class="text-muted">ستُخصم تلقائياً عند إصدار مسير الراتب</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-custom p-3 bg-light">
                <small class="text-muted fw-bold">العمالة والمياومة</small>
                <h4 class="fw-bold mb-0 text-primary">{{ $employees->count() }} مسجلين</h4>
                <small class="text-muted">سائقين، أمناء مستودع، وعمال تحميل</small>
            </div>
        </div>
    </div>

    <!-- Employees Cards/List -->
    <div class="row g-3">
        @forelse($employees as $employee)
            <div class="col-lg-4 col-md-6">
                <div class="card-custom p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                    {{ mb_substr($employee->name, 0, 1) }}
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">{{ $employee->name }}</h5>
                                    <span class="badge bg-light text-dark border">{{ $employee->job_title }}</span>
                                </div>
                            </div>
                            @if($employee->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">نشط</span>
                            @else
                                <span class="badge bg-secondary">غير نشط</span>
                            @endif
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-3 small">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">الراتب الأساسي:</span>
                                <strong class="text-dark">{{ number_format($employee->basic_salary, 3) }} د.أ</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">السلف المعلقة:</span>
                                @if($employee->pending_advances_total > 0)
                                    <span class="badge bg-warning text-dark fw-bold">{{ number_format($employee->pending_advances_total, 3) }} د.أ</span>
                                @else
                                    <span class="text-muted">لا يوجد</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">رقم الهاتف:</span>
                                <span class="font-monospace">{{ $employee->phone ? str_replace(' ', '', $employee->phone) : '-' }}</span>
                            </div>
                        </div>

                        <!-- Advances List (Small Preview) -->
                        @if($employee->advances->count() > 0)
                            <div class="mb-3">
                                <small class="fw-bold text-muted d-block mb-1">آخر السلفيات المسجلة:</small>
                                <div class="list-group list-group-flush small border rounded-3 overflow-hidden">
                                    @foreach($employee->advances->take(2) as $adv)
                                        <div class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                            <span>{{ $adv->advance_date ? $adv->advance_date->format('m/d') : '-' }} - {{ $adv->notes }}</span>
                                            <span class="fw-bold {{ $adv->status === 'pending' ? 'text-danger' : 'text-muted' }}">
                                                {{ number_format($adv->amount, 3) }} د.أ
                                                @if($adv->status === 'deducted')
                                                    <i class="fa-solid fa-check text-success ms-1" title="تم خصمها من الراتب"></i>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 border-top pt-3 mt-2 flex-wrap">
                        <!-- Add Advance Button -->
                        <button type="button" class="btn btn-warning btn-sm text-dark fw-bold flex-grow-1 rounded-3" data-bs-toggle="modal" data-bs-target="#advanceModal{{ $employee->id }}">
                            <i class="fa-solid fa-hand-holding-dollar ms-1"></i> سلفة
                        </button>
                        <!-- Pay Slip Button -->
                        <button type="button" class="btn btn-success btn-sm fw-bold flex-grow-1 rounded-3" data-bs-toggle="modal" data-bs-target="#slipModal{{ $employee->id }}">
                            <i class="fa-solid fa-money-check-dollar ms-1"></i> مسير راتب
                        </button>
                        <!-- Delete Button -->
                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف الموظف {{ $employee->name }} وجميع سجلاته؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" title="حذف الموظف">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Advance Modal -->
            <div class="modal fade" id="advanceModal{{ $employee->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered text-start">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header bg-warning-subtle">
                            <h6 class="modal-title fw-bold text-dark"><i class="fa-solid fa-hand-holding-dollar ms-1"></i> صرف سلفة للموظف: {{ $employee->name }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('employees.advances.store', $employee) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="alert alert-secondary py-2 small mb-3">
                                    الراتب الأساسي: <strong>{{ number_format($employee->basic_salary, 3) }} د.أ</strong> | إجمالي السلف القائمة حالياً: <strong>{{ number_format($employee->pending_advances_total, 3) }} د.أ</strong>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">مبلغ السلفة (د.أ) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.5" min="1" name="amount" class="form-control fw-bold text-danger fs-5" placeholder="مثال: 50.000" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">تاريخ الصرف <span class="text-danger">*</span></label>
                                    <input type="date" name="advance_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">السبب / ملاحظات</label>
                                    <input type="text" name="notes" class="form-control" placeholder="مثال: سلفة نقدية طارئة على الراتب" required>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-warning text-dark fw-bold rounded-3 px-4">صرف وقيد في المصروفات</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Salary Slip Modal -->
            <div class="modal fade" id="slipModal{{ $employee->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered text-start">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header bg-success-subtle">
                            <h6 class="modal-title fw-bold text-dark"><i class="fa-solid fa-money-check-dollar ms-1"></i> مسير راتب شهري: {{ $employee->name }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('employees.salary-slip.store', $employee) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">عن شهر / سنة <span class="text-danger">*</span></label>
                                    <input type="month" name="salary_month" class="form-control" value="{{ date('Y-m') }}" required>
                                </div>

                                <div class="bg-light p-3 rounded-3 mb-3 small">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>الراتب الأساسي:</span>
                                        <strong class="text-primary">{{ number_format($employee->basic_salary, 3) }} د.أ</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 text-danger">
                                        <span>السلف المعلقة (تُخصم تلقائياً):</span>
                                        <strong>-{{ number_format($employee->pending_advances_total, 3) }} د.أ</strong>
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">مكافآت / بدلات إضافية (د.أ)</label>
                                        <input type="number" step="0.5" min="0" name="bonuses" class="form-control text-success" value="0.000">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">خصومات أخرى / غياب (د.أ)</label>
                                        <input type="number" step="0.5" min="0" name="deductions" class="form-control text-danger" value="0.000">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">تاريخ تسليم الراتب <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">ملاحظات المسير</label>
                                    <input type="text" name="notes" class="form-control" placeholder="أي ملاحظات إضافية...">
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-success fw-bold rounded-3 px-4">اعتماد وصرف الراتب الصافي</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card-custom p-5 text-center text-muted">
                    <i class="fa-solid fa-users-slash fs-1 mb-3 text-secondary"></i>
                    <h5>لا يوجد موظفين مسجلين حالياً</h5>
                    <p class="small">أضف موظفي وعمال المؤسسة (سائقين، مستودع، كاشير) لإدارة رواتبهم وسلفهم بسهولة.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <h6 class="modal-title fw-bold">إضافة موظف / عامل جديد</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employees.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">اسم الموظف / العامل <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="الاسم الثلاثي..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">المسمى الوظيفي <span class="text-danger">*</span></label>
                        <input type="text" name="job_title" class="form-control" placeholder="مثال: أمين مستودع، سائق ديزل، عامل تحميل، كاشير..." required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">الراتب الأساسي (د.أ) <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="0" name="basic_salary" class="form-control fw-bold text-primary" placeholder="350.000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control" placeholder="0790000000">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">الرقم الوطني / الإقامة</label>
                            <input type="text" name="national_id" class="form-control" placeholder="الرقم الوطني">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">تاريخ التعيين</label>
                            <input type="date" name="hire_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-info text-white rounded-3 px-4 fw-bold">حفظ الموظف</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
