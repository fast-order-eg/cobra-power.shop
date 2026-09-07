@extends('layouts.app')

@section('title', 'نقطة البيع والكاشير (POS)')

@push('styles')
<style>
    .pos-product-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .pos-product-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.15);
        transform: translateY(-2px);
    }
    .category-pill {
        cursor: pointer;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .category-pill.active {
        background: #1e3a8a;
        color: #ffffff;
        border-color: #1e3a8a;
        box-shadow: 0 3px 8px rgba(30, 58, 138, 0.3);
    }
    .cart-wrapper {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        height: calc(100vh - 120px);
        position: sticky;
        top: 90px;
    }
    .cart-items {
        flex-grow: 1;
        overflow-y: auto;
        padding: 12px;
    }
    .cart-item-row {
        background: #f8fafc;
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 8px;
        border: 1px solid #edf2f7;
    }
    .invoice-type-toggle {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 4px;
        display: flex;
    }
    .invoice-type-toggle .btn-toggle {
        flex: 1;
        padding: 8px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
        border: none;
        background: transparent;
        color: #64748b;
        transition: all 0.2s ease;
    }
    .invoice-type-toggle .btn-toggle.active {
        background: #ffffff;
        color: #1e3a8a;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }
    .invoice-type-toggle .btn-toggle.active.tax-btn {
        color: #1d4ed8;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="row g-3">
        <!-- Products Grid Section (Left 7 Cols) -->
        <div class="col-lg-7 col-xl-8">
            <div class="card-custom p-3 mb-3">
                <!-- Search & Category Filters -->
                <div class="row g-2 mb-3">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" id="posSearchInput" class="form-control border-start-0" placeholder="بحث بالاسم أو الباركود أو الكود...">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-barcode text-muted"></i></span>
                            <input type="text" id="posBarcodeScanner" class="form-control border-start-0" placeholder="قارئ الباركود (مسح فوري)...">
                        </div>
                    </div>
                </div>

                <!-- Categories Horizontal Scroll -->
                <div class="d-flex gap-2 overflow-x-auto pb-2 mb-1" id="categoryPills">
                    <span class="category-pill active" data-cat-id="all">الكل</span>
                    @foreach($categories as $cat)
                        <span class="category-pill" data-cat-id="{{ $cat->id }}">{{ $cat->name }}</span>
                    @endforeach
                </div>
            </div>

            <!-- Products List -->
            <div class="row g-3" id="productsGrid" style="max-height: calc(100vh - 240px); overflow-y: auto;">
                @foreach($products as $p)
                    <div class="col-xl-3 col-lg-4 col-md-4 col-6 product-item" data-id="{{ $p->id }}" data-cat-id="{{ $p->category_id }}" data-name="{{ $p->name }}" data-code="{{ $p->code }}" data-barcode="{{ $p->barcode }}" data-price="{{ $p->selling_price }}" data-stock="{{ (int)$p->stock_quantity }}" data-unit="{{ $p->unit }}">
                        <div class="pos-product-card" onclick="addToCart({{ $p->id }})">
                            <div class="text-center mb-2">
                                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 100%; height: 80px;">
                                    @if($p->image_path)
                                        <img src="{{ asset('storage/' . $p->image_path) }}" alt="{{ $p->name }}" class="w-100 h-100 object-fit-contain rounded-3">
                                    @else
                                        <i class="fa-solid fa-faucet-drip text-secondary fs-3"></i>
                                    @endif
                                </div>
                                <h6 class="fw-bold mb-1 text-dark text-truncate" title="{{ $p->name }}">{{ $p->name }}</h6>
                                <div class="d-flex justify-content-between align-items-center small text-muted">
                                    <span class="badge bg-light text-dark border">{{ $p->unit }}</span>
                                    <span class="text-muted">متاح: <strong>{{ (int)$p->stock_quantity }}</strong></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-auto">
                                <span class="fw-bold text-success fs-6">{{ number_format($p->selling_price, 3) }} د.أ</span>
                                <button type="button" class="btn btn-sm btn-primary rounded-circle" style="width: 28px; height: 28px; padding: 0;">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- POS Cart / Invoice Builder (Right 5 Cols) -->
        <div class="col-lg-5 col-xl-4">
            <div class="cart-wrapper shadow-sm">
                <!-- Cart Header & Invoice Type Toggle -->
                <div class="p-3 border-bottom bg-light rounded-top-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-receipt text-primary ms-1"></i> فاتورة بيع جديدة</h6>
                        <span class="badge bg-white text-dark border font-monospace">{{ $nextInvoiceNumber }}</span>
                    </div>

                    <!-- TAX / NON-TAX TOGGLE (Essential Client Requirement) -->
                    <div class="invoice-type-toggle mb-2">
                        <button type="button" class="btn-toggle tax-btn active" id="btnTaxInvoice" onclick="setInvoiceType('tax')">
                            <i class="fa-solid fa-stamp ms-1"></i> فاتورة ضريبية (16%)
                        </button>
                        <button type="button" class="btn-toggle" id="btnNonTaxInvoice" onclick="setInvoiceType('non_tax')">
                            <i class="fa-solid fa-file-lines ms-1"></i> غير ضريبية (نقدية)
                        </button>
                    </div>

                    <!-- Customer Selector & Management -->
                    <div class="d-flex gap-1 align-items-center">
                        <div class="input-group input-group-sm flex-grow-1">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-user text-muted"></i></span>
                            <select id="posCustomerSelect" class="form-select">
                                <option value="">-- زبون نقدي عام --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? '(' . str_replace(' ', '', $c->phone) . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-2" data-bs-toggle="modal" data-bs-target="#quickAddCustomerModal" title="إضافة زبون جديد سريعا">
                            <i class="fa-solid fa-user-plus"></i>
                        </button>
                        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-2" title="إدارة وحذف العملاء">
                            <i class="fa-solid fa-users-gear"></i>
                        </a>
                    </div>
                </div>

                <!-- Cart Items List -->
                <div class="cart-items" id="cartItemsContainer">
                    <div class="text-center py-5 text-muted" id="emptyCartMessage">
                        <i class="fa-solid fa-cart-arrow-down fs-1 mb-2 text-secondary opacity-50"></i>
                        <p class="small mb-0">السلة فارغة، اضغط على أي صنف لإضافته للفاتورة</p>
                    </div>
                </div>

                <!-- Cart Footer & Calculation -->
                <div class="p-3 border-top bg-light rounded-bottom-4">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>المجموع الفرعي:</span>
                        <span class="fw-bold text-dark" id="displaySubtotal">0.000 د.أ</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="text-muted">الخصم الإجمالي (د.أ):</span>
                        <input type="number" step="0.1" min="0" id="inputDiscount" class="form-control form-control-sm text-end" style="width: 90px;" value="0" oninput="calculateCart()">
                    </div>

                    <div class="d-flex justify-content-between small text-primary mb-1" id="taxRow">
                        <span>ضريبة المبيعات العامة (16%):</span>
                        <span class="fw-bold" id="displayTax">0.000 د.أ</span>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold fs-6 text-dark">المجموع الصافي النهائي:</span>
                        <span class="fw-bold fs-4 text-success" id="displayTotal">0.000 د.أ</span>
                    </div>

                    <!-- Payment Method Buttons -->
                    <div class="btn-group w-100 mb-3" role="group">
                        <input type="radio" class="btn-check" name="paymentMethodRadio" id="payCash" value="cash" checked>
                        <label class="btn btn-outline-secondary btn-sm fw-bold" for="payCash"><i class="fa-solid fa-money-bill-1 ms-1"></i> كاش</label>

                        <input type="radio" class="btn-check" name="paymentMethodRadio" id="payCard" value="card">
                        <label class="btn btn-outline-secondary btn-sm fw-bold" for="payCard"><i class="fa-solid fa-credit-card ms-1"></i> فيزا</label>

                        <input type="radio" class="btn-check" name="paymentMethodRadio" id="payCredit" value="credit">
                        <label class="btn btn-outline-secondary btn-sm fw-bold" for="payCredit"><i class="fa-solid fa-handshake ms-1"></i> ذمة/آجل</label>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success fw-bold py-2 shadow-sm fs-6" id="btnSubmitInvoice" onclick="submitInvoice(false)">
                            <i class="fa-solid fa-circle-check ms-1"></i> اعتماد وحفظ الفاتورة
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const productsData = {!! json_encode($products->keyBy('id')) !!};
    const nextInvNumber = "{{ $nextInvoiceNumber }}";
    let currentInvoiceType = 'tax'; // 'tax' or 'non_tax'
    let cart = {}; // { productId: { product, qty, price } }

    function setInvoiceType(type) {
        currentInvoiceType = type;
        if (type === 'tax') {
            document.getElementById('btnTaxInvoice').classList.add('active');
            document.getElementById('btnNonTaxInvoice').classList.remove('active');
            document.getElementById('taxRow').style.display = 'flex';
        } else {
            document.getElementById('btnNonTaxInvoice').classList.add('active');
            document.getElementById('btnTaxInvoice').classList.remove('active');
            document.getElementById('taxRow').style.display = 'none';
        }
        calculateCart();
    }

    function addToCart(productId) {
        const product = productsData[productId];
        if (!product) return;

        if (cart[productId]) {
            cart[productId].qty += 1;
        } else {
            cart[productId] = {
                id: product.id,
                name: product.name,
                unit: product.unit,
                price: parseFloat(product.selling_price),
                qty: 1
            };
        }
        renderCart();
    }

    function updateQty(productId, delta) {
        if (!cart[productId]) return;
        cart[productId].qty += delta;
        if (cart[productId].qty <= 0) {
            delete cart[productId];
        }
        renderCart();
    }

    function updateItemPrice(productId, newPrice) {
        if (!cart[productId]) return;
        cart[productId].price = parseFloat(newPrice) || 0;
        calculateCart();
    }

    function removeFromCart(productId) {
        delete cart[productId];
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartItemsContainer');
        const keys = Object.keys(cart);

        if (keys.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5 text-muted" id="emptyCartMessage">
                    <i class="fa-solid fa-cart-arrow-down fs-1 mb-2 text-secondary opacity-50"></i>
                    <p class="small mb-0">السلة فارغة، اضغط على أي صنف لإضافته للفاتورة</p>
                </div>
            `;
            calculateCart();
            return;
        }

        let html = '';
        keys.forEach(id => {
            const item = cart[id];
            const itemTotal = item.qty * item.price;
            html += `
                <div class="cart-item-row">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <strong class="text-dark small">${item.name}</strong>
                        <button type="button" class="btn-close btn-sm" onclick="removeFromCart(${item.id})"></button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="updateQty(${item.id}, -1)">-</button>
                            <input type="number" step="1" min="1" class="form-control form-control-sm text-center px-1" style="width: 50px;" value="${item.qty}" onchange="cart[${item.id}].qty = Math.max(1, parseFloat(this.value)||1); calculateCart();">
                            <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="updateQty(${item.id}, 1)">+</button>
                            <span class="small text-muted ms-1">${item.unit}</span>
                        </div>
                        <div class="text-end">
                            <input type="number" step="0.001" min="0" class="form-control form-control-sm text-end fw-bold d-inline-block" style="width: 85px;" value="${item.price.toFixed(3)}" oninput="updateItemPrice(${item.id}, this.value)">
                            <div class="small fw-bold text-primary mt-1">${itemTotal.toFixed(3)} د.أ</div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        calculateCart();
    }

    function calculateCart() {
        let subtotal = 0;
        Object.values(cart).forEach(item => {
            subtotal += (item.qty * item.price);
        });

        const discount = parseFloat(document.getElementById('inputDiscount').value) || 0;
        const taxableAmount = Math.max(0, subtotal - discount);
        const taxRate = (currentInvoiceType === 'tax') ? 0.16 : 0.00;
        const taxAmount = taxableAmount * taxRate;
        const total = taxableAmount + taxAmount;

        document.getElementById('displaySubtotal').innerText = subtotal.toFixed(3) + ' د.أ';
        document.getElementById('displayTax').innerText = taxAmount.toFixed(3) + ' د.أ';
        document.getElementById('displayTotal').innerText = total.toFixed(3) + ' د.أ';
    }

    // Category Filter Pills
    document.querySelectorAll('.category-pill').forEach(pill => {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            const catId = this.getAttribute('data-cat-id');

            document.querySelectorAll('.product-item').forEach(item => {
                if (catId === 'all' || item.getAttribute('data-cat-id') === catId) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Live Search
    document.getElementById('posSearchInput').addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.product-item').forEach(item => {
            const name = item.getAttribute('data-name').toLowerCase();
            const code = (item.getAttribute('data-code') || '').toLowerCase();
            const barcode = (item.getAttribute('data-barcode') || '').toLowerCase();
            if (name.includes(query) || code.includes(query) || barcode.includes(query)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Barcode Scanner Listener
    document.getElementById('posBarcodeScanner').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcode = this.value.trim();
            if (!barcode) return;

            let found = false;
            document.querySelectorAll('.product-item').forEach(item => {
                if (item.getAttribute('data-barcode') === barcode || item.getAttribute('data-code') === barcode) {
                    addToCart(parseInt(item.getAttribute('data-id')));
                    found = true;
                }
            });

            if (!found) {
                alert('لم يتم العثور على صنف بهذا الباركود: ' + barcode);
            }
            this.value = '';
        }
    });

    // Submit Invoice
    function submitInvoice() {
        const keys = Object.keys(cart);
        if (keys.length === 0) {
            Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى إضافة منتجات إلى الفاتورة أولاً' });
            return;
        }

        const items = keys.map(id => {
            return {
                product_id: cart[id].id,
                quantity: cart[id].qty,
                unit_price: cart[id].price
            };
        });

        const paymentMethod = document.querySelector('input[name="paymentMethodRadio"]:checked').value;
        const customerId = document.getElementById('posCustomerSelect').value || null;
        const discount = parseFloat(document.getElementById('inputDiscount').value) || 0;

        // حساب الإجمالي النهائي
        let subtotal = 0;
        Object.values(cart).forEach(item => { subtotal += item.qty * item.price; });
        const taxableAmount = Math.max(0, subtotal - discount);
        const taxRate = (currentInvoiceType === 'tax') ? 0.16 : 0.00;
        const taxAmount = taxableAmount * taxRate;
        const totalAmount = taxableAmount + taxAmount;

        const payload = {
            invoice_number: nextInvNumber,
            invoice_type: currentInvoiceType,
            customer_id: customerId,
            invoice_date: new Date().toISOString().split('T')[0],
            payment_method: paymentMethod,
            discount_amount: discount,
            paid_amount: totalAmount,
            items: items,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        const btn = document.getElementById('btnSubmitInvoice');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin ms-1"></i> جاري إصدار الفاتورة...';

        fetch("{{ route('invoices.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': payload._token
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'تم إصدار الفاتورة بنجاح!',
                    text: 'رقم الفاتورة: ' + data.invoice_number,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-print"></i> طباعة إيصال حراري (80mm)',
                    cancelButtonText: '<i class="fa-solid fa-file-pdf"></i> فاتورة رسمية A4',
                    showDenyButton: true,
                    denyButtonText: 'فاتورة جديدة فوراً'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open('/invoices/' + data.invoice_id + '/print-thermal', '_blank');
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        window.open('/invoices/' + data.invoice_id + '/print-a4', '_blank');
                    }
                    window.location.reload();
                });
            } else {
                Swal.fire({ icon: 'error', title: 'خطأ', text: data.message || 'حدث خطأ أثناء إصدار الفاتورة' });
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-circle-check ms-1"></i> اعتماد وحفظ الفاتورة';
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'خطأ في الاتصال', text: err.message });
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-circle-check ms-1"></i> اعتماد وحفظ الفاتورة';
        });
    }

    function submitQuickCustomer(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveQuickCustomer');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin ms-1"></i> جاري الحفظ...';

        const payload = {
            name: document.getElementById('quickCustomerName').value,
            customer_type: document.getElementById('quickCustomerType').value,
            phone: document.getElementById('quickCustomerPhone').value,
            tax_number: document.getElementById('quickCustomerTaxNumber').value,
            national_id: document.getElementById('quickCustomerNationalId').value,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        fetch("{{ route('customers.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': payload._token
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'حفظ واختيار العميل';
            if (data.success) {
                const select = document.getElementById('posCustomerSelect');
                const opt = document.createElement('option');
                opt.value = data.customer.id;
                opt.text = data.customer.name + (data.customer.phone ? ' (' + data.customer.phone + ')' : '');
                opt.selected = true;
                select.appendChild(opt);

                const modal = bootstrap.Modal.getInstance(document.getElementById('quickAddCustomerModal'));
                if (modal) modal.hide();
                document.getElementById('quickAddCustomerForm').reset();
                Swal.fire({ icon: 'success', title: 'تم الحفظ!', text: data.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'خطأ', text: data.message || 'حدث خطأ أثناء إضافة العميل' });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = 'حفظ واختيار العميل';
            Swal.fire({ icon: 'error', title: 'خطأ في الاتصال', text: err.message });
        });
    }
</script>

<!-- Quick Add Customer Modal inside POS -->
<div class="modal fade" id="quickAddCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus text-primary ms-1"></i> إضافة زبون جديد سريعا</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickAddCustomerForm" onsubmit="submitQuickCustomer(event)">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">اسم العميل / الشركة <span class="text-danger">*</span></label>
                        <input type="text" id="quickCustomerName" class="form-control" placeholder="اسم العميل أو الشركة" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">نوع العميل</label>
                            <select id="quickCustomerType" class="form-select">
                                <option value="individual">فردي (شخص)</option>
                                <option value="company">شركة / مؤسسة</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">رقم الهاتف (بدون مسافات)</label>
                            <input type="text" id="quickCustomerPhone" class="form-control font-monospace" placeholder="0790000000">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">الرقم الضريبي (إن وجد)</label>
                            <input type="text" id="quickCustomerTaxNumber" class="form-control font-monospace" placeholder="123456789">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">الرقم الوطني / الهوية</label>
                            <input type="text" id="quickCustomerNationalId" class="form-control font-monospace" placeholder="9900000000">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" id="btnSaveQuickCustomer" class="btn btn-primary rounded-3 px-4 fw-bold">حفظ واختيار العميل</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
