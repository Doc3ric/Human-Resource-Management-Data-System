<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Portal — PHRMO Bukidnon</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(135deg,#1e3a5f 0%,#2d6a4f 100%);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .card {
            background:#fff;
            border-radius:16px;
            box-shadow:0 20px 60px rgba(0,0,0,.25);
            padding:44px 40px 36px;
            width:100%;
            max-width:420px;
        }
        .logo-area {
            text-align:center;
            margin-bottom:28px;
        }
        .logo-icon {
            width:64px; height:64px;
            background:linear-gradient(135deg,#1e3a5f,#2d6a4f);
            border-radius:16px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-size:30px;
            margin-bottom:14px;
        }
        .logo-area h1 {
            font-size:18px;
            font-weight:700;
            color:#111827;
            line-height:1.3;
        }
        .logo-area p {
            font-size:12px;
            color:#6b7280;
            margin-top:4px;
        }
        .badge-portal {
            display:inline-block;
            background:#ecfdf5;
            color:#065f46;
            border:1px solid #a7f3d0;
            font-size:11px;
            font-weight:700;
            padding:3px 10px;
            border-radius:99px;
            margin-top:8px;
            letter-spacing:.5px;
        }
        .form-group { margin-bottom:18px; }
        label {
            display:block;
            font-size:12px;
            font-weight:600;
            color:#374151;
            margin-bottom:6px;
        }
        .input-wrap { position:relative; }
        .input-wrap i {
            position:absolute;
            left:12px; top:50%;
            transform:translateY(-50%);
            color:#9ca3af;
            font-size:15px;
        }
        input[type=email], input[type=password] {
            width:100%;
            padding:10px 12px 10px 36px;
            border:1.5px solid #d1d5db;
            border-radius:8px;
            font-size:14px;
            color:#111827;
            transition:border-color .15s;
            background:#f9fafb;
        }
        input:focus {
            outline:none;
            border-color:#1e3a5f;
            background:#fff;
        }
        .toggle-pw {
            position:absolute;
            right:10px; top:50%;
            transform:translateY(-50%);
            background:none;
            border:none;
            cursor:pointer;
            color:#9ca3af;
            font-size:15px;
            padding:2px;
        }
        .toggle-pw:hover { color:#374151; }
        .remember-row {
            display:flex;
            align-items:center;
            gap:8px;
            margin-bottom:20px;
        }
        .remember-row input[type=checkbox] { width:15px; height:15px; cursor:pointer; }
        .remember-row label { margin:0; font-size:13px; font-weight:500; color:#6b7280; cursor:pointer; }
        .btn-login {
            width:100%;
            padding:11px;
            background:linear-gradient(135deg,#1e3a5f,#2d6a4f);
            color:#fff;
            border:none;
            border-radius:8px;
            font-size:14px;
            font-weight:700;
            cursor:pointer;
            transition:opacity .15s;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
        }
        .btn-login:hover { opacity:.9; }
        .error-box {
            background:#fee2e2;
            border:1px solid #fca5a5;
            color:#991b1b;
            border-radius:8px;
            padding:10px 14px;
            font-size:13px;
            margin-bottom:18px;
        }
        .divider {
            text-align:center;
            margin:24px 0 14px;
            font-size:11px;
            color:#9ca3af;
        }
        .back-link {
            display:block;
            text-align:center;
            font-size:12px;
            color:#6b7280;
            text-decoration:none;
        }
        .back-link:hover { color:#1e3a5f; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo-area">
        <div class="logo-icon"><i class="bi bi-person-badge-fill"></i></div>
        <h1>HRMPSB / TWG<br>Panel Portal</h1>
        <p>Provincial Human Resource Management Office</p>
        <span class="badge-portal"><i class="bi bi-shield-lock-fill"></i> Restricted Access</span>
    </div>

    @if($errors->any())
        <div class="error-box">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ $errors->first() }}
        </div>
    @endif

    @if(session('error'))
        <div class="error-box">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('panel.login.submit') }}" autocomplete="on">
        @csrf
        <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-wrap">
                <i class="bi bi-envelope-fill"></i>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="Enter your email" required autofocus>
            </div>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
                <i class="bi bi-lock-fill"></i>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <button type="button" class="toggle-pw" onclick="togglePwd()">
                    <i class="bi bi-eye" id="pw-icon"></i>
                </button>
            </div>
        </div>
        <div class="remember-row">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Keep me signed in</label>
        </div>
        <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right"></i> Sign In to Panel Portal
        </button>
    </form>

    <div class="divider">— This portal is for HRMPSB and TWG members only —</div>
    <a href="{{ route('login') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to main HR system login
    </a>
</div>

<script>
function togglePwd() {
    const inp  = document.getElementById('password');
    const icon = document.getElementById('pw-icon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>
