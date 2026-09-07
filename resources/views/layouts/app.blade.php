<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'الرئيسية') | مؤسسة قوة الكوبرا للأدوات الصحية</title>

    <!-- Google Fonts Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary-color: #1e3a8a;
            --primary-light: #3b82f6;
            --secondary-color: #0f172a;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --bg-color: #f8fafc;
            --sidebar-width: 270px;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-color);
            color: #1e293b;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: #0f172a;
            color: #e2e8f0;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            z-index: 1040;
            overflow-y: auto;
            transition: all 0.3s ease;
            box-shadow: -4px 0 15px rgba(0, 0, 0, 0.05);
        }

        .sidebar-brand {
            padding: 20px 24px;
            background: #090e17;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
        }

        .sidebar-brand h5 {
            font-size: 1.1rem;
            font-weight: 800;
            margin: 0;
            color: #f8fafc;
        }

        .sidebar-brand small {
            color: #94a3b8;
            font-size: 0.75rem;
        }

        .sidebar-menu {
            padding: 16px 12px;
            list-style: none;
            margin: 0;
        }

        .menu-header {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            padding: 12px 14px 6px;
            font-weight: 700;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }

        .sidebar-link i {
            width: 20px;
            font-size: 1.1rem;
            text-align: center;
        }

        .sidebar-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
        }

        .sidebar-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }

        /* Main Content Wrapper */
        .main-wrapper {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        /* Top Navbar */
        .top-navbar {
            background: #ffffff;
            height: 70px;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .btn-pos-quick {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-weight: 700;
            padding: 9px 20px;
            border-radius: 10px;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-pos-quick:hover {
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        /* Cards & Components */
        .card-custom {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease;
        }

        .card-custom:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        }

        .metric-card {
            padding: 22px;
            border-radius: 16px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .metric-card .icon-box {
            position: absolute;
            left: 20px;
            bottom: 16px;
            font-size: 3.5rem;
            opacity: 0.15;
        }

        .badge-tax {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .badge-nontax {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .badge-customs {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 8px;
        }

        /* Mobile Adjustments */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-right: 0;
            }
            .top-navbar {
                padding: 0 16px;
            }
        }

        /* Print Media Styles */
        @media print {
            @page {
                size: landscape;
                margin: 8mm 10mm;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-size: 11pt !important;
            }
            .sidebar, 
            .top-navbar, 
            .no-print, 
            .btn, 
            button, 
            .dropdown,
            .modal,
            #btnToggleSidebar {
                display: none !important;
            }
            .main-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .card-custom {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                background: transparent !important;
            }
            .table-responsive {
                overflow: visible !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 9.5pt !important;
            }
            table th, table td {
                border: 1px solid #64748b !important;
                padding: 6px 8px !important;
                color: #000000 !important;
                background: transparent !important;
            }
            table thead th {
                background-color: #f1f5f9 !important;
                font-weight: bold !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .d-print-block {
                display: block !important;
            }
            .d-print-none {
                display: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar" id="appSidebar">
        <div class="sidebar-brand">
            <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.4rem;">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <h5>قوة الكوبرا</h5>
                <small style="color: #e2e8f0;"><i class="fa-solid fa-shield-halved text-success ms-1"></i> سيستم سحابي - الأردن</small>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-header">الرئيسية ونقاط البيع</li>
            <li>
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>لوحة التحكم</span>
                </a>
            </li>
            <li>
                <a href="{{ route('pos') }}" class="sidebar-link {{ request()->routeIs('pos') || request()->routeIs('invoices.create') ? 'active' : '' }}">
                    <i class="fa-solid fa-cash-register text-success"></i>
                    <span>نقطة البيع</span>
                </a>
            </li>
            <li>
                <a href="{{ route('invoices.index') }}" class="sidebar-link {{ request()->routeIs('invoices.index') || request()->routeIs('invoices.show') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>سجل الفواتير</span>
                </a>
            </li>
            <li>
                <a href="{{ route('customers.index') }}" class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-address-book text-primary"></i>
                    <span>إدارة العملاء</span>
                </a>
            </li>

            <li class="menu-header">المستودع والمشتريات</li>
            <li>
                <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>المخزون</span>
                </a>
            </li>
            <li>
                <a href="{{ route('categories.index') }}" class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-tags"></i>
                    <span>أصناف المنتجات</span>
                </a>
            </li>
            <li>
                <a href="{{ route('customs.index') }}" class="sidebar-link {{ request()->routeIs('customs.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-ship text-warning"></i>
                    <span>كلفة البضائع والجمارك</span>
                </a>
            </li>

            <li class="menu-header">التشغيل والمصروفات</li>
            <li>
                <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-receipt text-danger"></i>
                    <span>المصروفات</span>
                </a>
            </li>
            <li>
                <a href="{{ route('employees.index') }}" class="sidebar-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-gear text-info"></i>
                    <span>الموظفين</span>
                </a>
            </li>

            <li class="menu-header">التقارير والإعدادات</li>
            <li>
                <a href="{{ route('reports.sales') }}" class="sidebar-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>تقارير المبيعات</span>
                </a>
            </li>
            <li>
                <a href="{{ route('reports.profit-loss') }}" class="sidebar-link {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأرباح والخسائر</span>
                </a>
            </li>
            <li>
                <a href="{{ route('reports.inventory') }}" class="sidebar-link {{ request()->routeIs('reports.inventory') ? 'active' : '' }}">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>جرد المخزون</span>
                </a>
            </li>
            <li>
                <a href="{{ route('settings.edit') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-sliders"></i>
                    <span>الإعدادات</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none" id="btnToggleSidebar" type="button">
                    <i class="fa-solid fa-bars fs-5"></i>
                </button>
                <div class="d-none d-md-block">
                    <h6 class="mb-0 fw-bold">مؤسسة قوة الكوبرا للأدوات الصحية والسباكة</h6>
                    <small class="text-muted"><i class="fa-solid fa-location-dot text-danger ms-1"></i> عمان - الأردن | العملة: دينار أردني (د.أ)</small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('pos') }}" class="btn-pos-quick">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>فاتورة بيع جديدة</span>
                </a>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 rounded-3 px-3 py-2" type="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.9rem;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <span class="fw-bold fs-6 d-none d-sm-inline">مدير النظام</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('settings.edit') }}">
                                <i class="fa-solid fa-gear text-secondary"></i>
                                <span>إعدادات المؤسسة</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    <span>تسجيل الخروج</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-grow-1 p-3 p-md-4">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                    <i class="fa-solid fa-circle-check fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                    <i class="fa-solid fa-circle-xmark fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                    <i class="fa-solid fa-circle-info fs-5"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/data/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById('btnToggleSidebar')?.addEventListener('click', function () {
            document.getElementById('appSidebar').classList.toggle('show');
        });
    </script>
    @stack('scripts')
</body>
</html>
