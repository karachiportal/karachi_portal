<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Commissioner Karachi Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0D3D22 0%, #1A5C38 60%, #2E7D52 100%);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 36px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }
        .logo-circle {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #1A5C38, #2E7D52);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
        }
        .form-control:focus {
            border-color: #1A5C38;
            box-shadow: 0 0 0 3px rgba(26,92,56,.15);
        }
        .btn-login {
            background: linear-gradient(135deg, #1A5C38, #2E7D52);
            color: #fff; border: none; height: 48px;
            font-size: 15px; font-weight: 600;
            border-radius: 10px; width: 100%;
            transition: opacity .2s;
        }
        .btn-login:hover { opacity: .9; color: #fff; }
        .input-group-text { background: #f8f9fa; border-right: none; }
        .form-control { border-left: none; }
        .form-control.with-icon { border-left: none; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <div class="logo-circle" style="width:100px;height:100px;background:#0D3D22;background-image:url('{{ asset("logo.png") }}');background-size:200%;background-position:center;background-repeat:no-repeat;background-blend-mode:luminosity;"></div>
        <h5 class="fw-bold mb-1" style="color:#1A5C38;">COMMISSIONER KARACHI PORTAL</h5>
        <p class="text-muted mb-0" style="font-size:13px;">Admin Panel — کمشنر کراچی پورٹل</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 mb-3">
            <i class="bi bi-x-circle me-1"></i>{{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.post') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Username / یوزرنیم</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                <input type="text" name="username" class="form-control"
                       placeholder="Enter admin username"
                       value="{{ old('username') }}" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:13px;">Password / پاس ورڈ</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" id="pwdInput"
                       class="form-control" placeholder="Enter password" required>
                <button type="button" class="btn btn-outline-secondary border-start-0"
                        onclick="togglePwd()">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-login btn mb-3">
            <i class="bi bi-shield-lock me-2"></i>Login to Admin Panel
        </button>
    </form>

    <p class="text-center text-muted mb-0" style="font-size:12px;">
        Commissioner Karachi Portal &copy; {{ date('Y') }}
    </p>
</div>

<script>
function togglePwd() {
    const i = document.getElementById('pwdInput');
    const e = document.getElementById('eyeIcon');
    if (i.type === 'password') {
        i.type = 'text';
        e.className = 'bi bi-eye-slash';
    } else {
        i.type = 'password';
        e.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>
