@extends('layouts.app')

@section('title', 'إعدادات المنشأة والضريبة')

@section('content')
<div class="container-fluid px-0" style="max-width: 900px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">إعدادات المؤسسة والبيانات الضريبية ⚙️</h4>
            <p class="text-muted small mb-0">بيانات الفاتورة، الرقم الضريبي في الأردن، ونسب الضريبة الافتراضية</p>
        </div>
    </div>

    <div class="card-custom p-4">
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-building ms-1"></i> البيانات الرسمية للمنشأة</h6>

            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label fw-bold small">الاسم التجاري للمؤسسة / المعرض <span class="text-danger">*</span></label>
                    <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $setting->company_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">الرقم الضريبي (ISTD الأردن) <span class="text-danger">*</span></label>
                    <input type="text" name="tax_number" class="form-control font-monospace fw-bold text-primary" value="{{ old('tax_number', str_replace(' ', '', $setting->tax_number)) }}" placeholder="123456789">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold small">رقم السجل التجاري</label>
                    <input type="text" name="commercial_registry" class="form-control font-monospace" value="{{ old('commercial_registry', str_replace(' ', '', $setting->commercial_registry)) }}" placeholder="CR-789456">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">رقم الهاتف للتواصل</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', str_replace(' ', '', $setting->phone)) }}" placeholder="+962790000000">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $setting->email) }}" placeholder="info@cobra-power.shop">
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold small">العنوان الرئيسي</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $setting->address) }}" placeholder="عمان - شارع مكة - مجمع الأدوات الصحية">
                </div>
            </div>

            <hr class="my-4">
            <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-coins ms-1"></i> إعدادات العملة والضريبة الأردنية</h6>

            <div class="row g-3 mb-4 bg-light p-3 rounded-4 border">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">اسم العملة <span class="text-danger">*</span></label>
                    <input type="text" name="currency_name" class="form-control" value="{{ old('currency_name', $setting->currency_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">رمز العملة المختصر <span class="text-danger">*</span></label>
                    <input type="text" name="currency_symbol" class="form-control font-monospace fw-bold" value="{{ old('currency_symbol', $setting->currency_symbol) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">نسبة ضريبة المبيعات العامة (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.5" min="0" max="100" name="tax_rate" class="form-control fw-bold text-primary" value="{{ old('tax_rate', $setting->tax_rate) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="tax_enabled_default" id="taxDefSwitch" value="1" {{ old('tax_enabled_default', $setting->tax_enabled_default) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small" for="taxDefSwitch">
                            تفعيل خيار (فاتورة ضريبية) افتراضياً عند فتح شاشة البيع POS
                        </label>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <h6 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-receipt ms-1"></i> تذييل الفاتورة والشعار</h6>

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold small">نص تذييل وشروط الفاتورة</label>
                    <textarea name="invoice_footer_text" rows="2" class="form-control" placeholder="شكراً لتعاملكم معنا...">{{ old('invoice_footer_text', $setting->invoice_footer_text) }}</textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold small">شعار المؤسسة (Logo)</label>
                    @if($setting->logo_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $setting->logo_path) }}" alt="Logo" style="max-height: 70px;">
                        </div>
                    @endif
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary rounded-3 px-5 fw-bold">
                    <i class="fa-solid fa-check ms-1"></i> حفظ وتحديث الإعدادات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
