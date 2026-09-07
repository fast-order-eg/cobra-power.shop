<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | قوة الكوبرا للأدوات الصحية</title>
    <!-- Google Fonts Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 440px;
            padding: 36px 32px;
        }
        .brand-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 16px;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.35);
        }
        .btn-login {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            font-weight: 700;
            padding: 12px;
            border-radius: 12px;
            border: none;
            width: 100%;
            transition: all 0.2s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
            color: white;
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            border: 1.5px solid #e2e8f0;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
    </style>
</head>
<body>

    <div class="login-card text-center">
        <div class="brand-icon">
            <i class="fa-solid fa-bolt"></i>
        </div>
        <h4 class="fw-bold mb-1 text-dark">سيستم قوة الكوبرا</h4>
        <p class="text-muted small mb-4">إدارة الحسابات والمستودعات والفوترة الضريبية 🇯🇴</p>

        @if($errors->any())
            <div class="alert alert-danger py-2 text-start rounded-3 small">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="text-start">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold small text-secondary">البريد الإلكتروني</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 rounded-end-3" value="{{ old('email', 'admin@cobra-power.shop') }}" required autofocus placeholder="admin@cobra-power.shop">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small text-secondary">كلمة المرور</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 rounded-end-3" value="12345678" required placeholder="••••••••">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                    <label class="form-check-label small text-muted" for="remember">تذكرني</label>
                </div>
                <span class="badge bg-light text-primary border">دخول المدير الافتراضي</span>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fa-solid fa-arrow-left-to-bracket ms-2"></i> تسجيل الدخول
            </button>
        </form>

        <div class="mt-4 pt-3 border-top text-muted small">
            <span>مؤسسة قوة الكوبرا للأدوات الصحية والسباكة - عمان</span>
        </div>
    </div>

</body>
</html>
