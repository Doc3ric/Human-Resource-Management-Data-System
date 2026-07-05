<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — HRMDS | Provincial Government of Bukidnon</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

        /* ── CSS Variable Bridge (login page has no dashboard layout) ── */
        :root {
            --color-primary:       #113659;
            --color-primary-dark:  #0d2842;
            --color-primary-light: #1a4a7a;
            --color-accent:        #2563eb;
            --color-focus-ring:    rgba(17, 54, 89, 0.10);
        }

        /* ── Animated Background ── */
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow: hidden;
            background: #071f38;
            position: relative;
        }

        .bg-animated {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .bg-animated::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 40%, #0d3057 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 70%, #0a2540 0%, transparent 60%),
                radial-gradient(ellipse 100% 80% at 50% 0%, #071f38 0%, #030f1e 100%);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            animation: drift linear infinite;
        }
        .orb-1 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #1a5ba8 0%, #0d3462 60%, transparent 100%);
            top: -100px; left: -100px;
            animation-duration: 22s; animation-name: drift1;
        }
        .orb-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #0e4a8a 0%, #072040 60%, transparent 100%);
            bottom: -80px; right: -80px;
            animation-duration: 28s; animation-name: drift2; opacity: 0.3;
        }
        .orb-3 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, #2176c7 0%, #0d3a70 60%, transparent 100%);
            top: 40%; right: 20%;
            animation-duration: 18s; animation-name: drift3; opacity: 0.2;
        }

        @keyframes drift1 {
            0%   { transform: translate(0,0) scale(1); }
            33%  { transform: translate(60px,40px) scale(1.08); }
            66%  { transform: translate(-30px,70px) scale(0.95); }
            100% { transform: translate(0,0) scale(1); }
        }
        @keyframes drift2 {
            0%   { transform: translate(0,0) scale(1); }
            50%  { transform: translate(-50px,-60px) scale(1.1); }
            100% { transform: translate(0,0) scale(1); }
        }
        @keyframes drift3 {
            0%   { transform: translate(0,0) scale(1); }
            40%  { transform: translate(40px,-50px) scale(1.05); }
            100% { transform: translate(0,0) scale(1); }
        }

        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* ── Card ── */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 940px;
            display: flex;
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.07),
                0 30px 80px rgba(0,0,0,0.55),
                0 8px 20px rgba(0,0,0,0.3);
            min-height: 560px;
        }

        /* ── Left Brand Panel (45%) ── */
        .left-panel {
            flex: 0 0 45%;
            background: linear-gradient(155deg, var(--color-primary) 0%, var(--color-primary-dark) 55%, #030f1e 100%);
            padding: 2.5rem 2.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
            pointer-events: none;
        }
        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 260px; height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(33,118,199,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Seal + logos area */
        .brand-top {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            text-align: center;
        }

        .bukidnon-seal {
            width: 160px;
            height: 160px;
            object-fit: contain;
            filter: drop-shadow(0 6px 18px rgba(0,0,0,0.5));
        }

        .brand-title-main {
            font-size: 1.1rem;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 10px;
            font-weight: 600;
            color: rgba(255,255,255,0.55);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .phrmo-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 8px 16px;
            margin-top: 4px;
        }

        .phrmo-logo {
            height: 80px;
            object-fit: contain;
        }

        /* Feature list */
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.8rem;
            border-radius: 10px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            transition: background 0.2s;
        }
        .feature-item:hover {
            background: rgba(255,255,255,0.07);
        }

        .feature-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0.06) 100%);
            border: 1px solid rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; color: #93c5fd; font-size: 0.85rem;
        }

        .feature-text-title { font-size: 12px; font-weight: 700; color: #fff; line-height: 1.2; }
        .feature-text-sub   { font-size: 10px; color: rgba(255,255,255,0.5); line-height: 1.4; margin-top: 1px; }

        /* Stats */
        .stats-bar {
            display: flex; gap: 1rem; padding: 0.8rem 1rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 10px;
        }
        .stat-item  { flex: 1; text-align: center; }
        .stat-value { font-size: 15px; font-weight: 900; color: #60adf5; line-height: 1; }
        .stat-label { font-size: 9px; font-weight: 600; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 3px; }
        .stat-div   { width: 1px; background: rgba(255,255,255,0.08); align-self: stretch; }

        /* ── Right Login Panel (55%) ── */
        .right-panel {
            flex: 0 0 55%;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.5rem 2.75rem;
            position: relative;
            overflow-y: auto;
        }

        .right-panel::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(17,54,89,0.04) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Welcome badge */
        .welcome-badge {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: #eef6ff; border: 1px solid #c3e0fb;
            border-radius: 100px; padding: 0.28rem 0.75rem;
            margin-bottom: 1rem; width: fit-content;
        }
        .welcome-badge-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: #2176c7;
            animation: pulse-dot 2s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%,100% { opacity:1; transform:scale(1); }
            50%      { opacity:0.5; transform:scale(0.75); }
        }
        .welcome-badge span { font-size: 10.5px; font-weight: 700; color: #1a5ba8; letter-spacing: 0.05em; }

        .form-heading  { font-size: 1.7rem; font-weight: 900; color: #0a1525; line-height: 1.1; letter-spacing: -0.02em; }
        .form-subtext  { font-size: 12.5px; color: #718096; font-weight: 500; margin-top: 0.3rem; margin-bottom: 1.5rem; }

        /* Inputs */
        .input-group  { margin-bottom: 0.9rem; }
        .input-label  {
            display: block; font-size: 10.5px; font-weight: 800;
            color: #3d4a5c; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.4rem;
        }
        .input-wrap   { position: relative; }
        .input-icon   {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            color: #a0aec0; font-size: 14px; pointer-events: none; transition: color 0.2s;
        }
        .form-input {
            width: 100%; padding: 0.7rem 2.5rem 0.7rem 2.75rem;
            background: #f7f9fc; border: 1.5px solid #e8edf4;
            border-radius: 10px; font-size: 13px; font-weight: 500; color: #1a202c;
            outline: none; transition: all 0.2s;
        }
        .form-input::placeholder { color: #b0beca; font-weight: 400; }
        .form-input:focus {
            background: #fff; border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-focus-ring);
        }
        .input-wrap:focus-within .input-icon { color: var(--color-primary); }

        .pw-toggle {
            position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #a0aec0; font-size: 14px; padding: 0; transition: color 0.2s; outline: none;
        }
        .pw-toggle:hover { color: #4a5568; }

        /* Field-level error */
        .field-error { font-size: 11.5px; color: #c53030; font-weight: 600; margin-top: 4px; display: flex; align-items: center; gap: 4px; }

        /* Options row */
        .options-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.1rem;
        }
        .remember-label { display: flex; align-items: center; gap: 0.4rem; cursor: pointer; }
        .remember-label input[type="checkbox"] { width: 14px; height: 14px; accent-color: var(--color-primary); cursor: pointer; }
        .remember-label span { font-size: 12px; font-weight: 600; color: #4a5568; }
        .forgot-link { font-size: 12px; font-weight: 700; color: var(--color-primary); text-decoration: none; transition: color 0.2s; }
        .forgot-link:hover { color: var(--color-accent); text-decoration: underline; }

        /* Sign In button */
        .btn-signin {
            width: 100%;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: #fff; font-size: 13.5px; font-weight: 800; letter-spacing: 0.05em;
            padding: 0.82rem 1rem; border: none; border-radius: 10px; cursor: pointer;
            position: relative; overflow: hidden;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 14px rgba(8,32,64,0.35);
            margin-bottom: 0;
        }
        .btn-signin::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 60%);
            pointer-events: none;
        }
        .btn-signin:hover  { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(8,32,64,0.45); }
        .btn-signin:active { transform: translateY(0);     box-shadow: 0 3px 10px rgba(8,32,64,0.3); }
        .btn-signin:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .btn-text { display: flex; align-items: center; justify-content: center; gap: 0.5rem; }

        /* ── RA 10173 Privacy Notice ── */
        .privacy-notice {
            margin-top: 10px;
            background: #f0f7ff;
            border: 1px solid #c3daee;
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            gap: 9px;
            align-items: flex-start;
        }
        .privacy-notice i {
            color: #2176c7;
            font-size: 14px;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .privacy-notice-text {
            font-size: 10.5px;
            color: #3d4e61;
            line-height: 1.6;
            font-weight: 500;
        }
        .privacy-notice-text a {
            color: var(--color-primary);
            font-weight: 700;
            text-decoration: none;
        }
        .privacy-notice-text a:hover { text-decoration: underline; }

        /* Error box */
        .error-box {
            display: flex; align-items: flex-start; gap: 0.5rem;
            background: #fff5f5; border: 1.5px solid #feb2b2;
            border-radius: 8px; padding: 0.65rem 0.85rem; margin-bottom: 1.1rem;
        }
        .error-box i { color: #e53e3e; font-size: 13px; margin-top: 1px; flex-shrink: 0; }
        .error-box p { font-size: 12px; font-weight: 600; color: #c53030; margin: 0; }

        /* Footer */
        .form-footer {
            margin-top: 1.5rem;
            padding-top: 1.1rem;
            border-top: 1px solid #edf2f7;
        }
        .form-footer-text { font-size: 11px; color: #a0aec0; font-weight: 500; text-align: center; }
        .form-footer-text a { font-weight: 800; color: var(--color-primary); text-decoration: none; }
        .form-footer-text a:hover { text-decoration: underline; }

        /* Register CTA */
        .register-cta-wrap { text-align: center; margin-bottom: 1rem; }
        .register-cta-label { font-size: 11px; color: #94a3b8; font-weight: 600; margin-bottom: 0.5rem; }
        .register-cta {
            display: inline-flex; align-items: center; gap: 0.5rem;
            font-size: 13.5px; font-weight: 800; letter-spacing: 0.01em; color: #fff;
            background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-primary) 100%);
            background-size: 200% 200%;
            padding: 0.6rem 1.3rem; border-radius: 100px; text-decoration: none;
            box-shadow: 0 4px 16px rgba(37,99,235,0.35);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-position 0.6s ease;
            animation: cta-glow 3s ease-in-out infinite;
        }
        .register-cta i { font-size: 14px; transition: transform 0.2s ease; }
        .register-cta:hover {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 10px 26px rgba(37,99,235,0.45);
            background-position: 100% 50%;
        }
        .register-cta:hover i { transform: translateX(2px) rotate(-8deg); }
        .register-cta:active { transform: translateY(0) scale(0.98); }
        @keyframes cta-glow {
            0%, 100% { box-shadow: 0 4px 16px rgba(37,99,235,0.35); }
            50%      { box-shadow: 0 4px 20px rgba(37,99,235,0.55); }
        }
        .footer-links {
            display: flex; align-items: center; justify-content: center;
            gap: 1rem; margin-top: 0.65rem; flex-wrap: wrap;
        }
        .footer-link {
            display: flex; align-items: center; gap: 0.3rem;
            font-size: 9.5px; font-weight: 700; color: #b0bec5;
            text-decoration: none; letter-spacing: 0.08em; text-transform: uppercase; transition: color 0.2s;
        }
        .footer-link:hover { color: #718096; }
        .footer-dot { width: 3px; height: 3px; border-radius: 50%; background: #d1d5db; display: inline-block; }
        .footer-version {
            text-align: center;
            font-size: 9px;
            color: #c0ccd8;
            font-weight: 500;
            margin-top: 8px;
            letter-spacing: 0.06em;
        }

        /* Mobile logos */
        .mobile-logos { display: none; }

        /* Particles */
        .particles { position: fixed; inset: 0; z-index: 1; pointer-events: none; }
        .particle  { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.15); animation: float-up linear infinite; }
        @keyframes float-up {
            0%   { transform: translateY(100vh) scale(1); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 0.5; }
            100% { transform: translateY(-10vh) scale(0.8); opacity: 0; }
        }

        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        @media (max-width: 767px) {
            .left-panel   { display: none; }
            .right-panel  { flex: 1; padding: 2rem 1.75rem; }
            .mobile-logos { display: flex; }
            .login-card   { max-width: 420px; }
        }
    </style>
</head>

<body>

    <div class="bg-animated">
        <div class="bg-grid"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="particles" id="particles"></div>

    <div class="login-card">

        {{-- ── LEFT BRAND PANEL ── --}}
        <div class="left-panel">

            <div class="brand-top">
                {{-- Bukidnon Provincial Seal --}}
                <img src="{{ asset('img/logo.png') }}"
                     alt="Province of Bukidnon Seal"
                     class="bukidnon-seal"
                     onerror="this.style.display='none'">

                <div>
                    <div class="brand-title-main">Province of Bukidnon</div>
                    <div class="brand-subtitle">Human Resource Management Data System</div>
                </div>

                {{-- PHRMO Logo --}}
                <div class="phrmo-row">
                    <img src="{{ asset('img/phrmologo.png') }}"
                         alt="PHRMO Logo"
                         class="phrmo-logo"
                         onerror="this.style.display='none'">
                </div>
            </div>

            {{-- Features --}}
            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="feature-text-title">Inventory of Personnel</div>
                        <div class="feature-text-sub">Comprehensive workforce demographics &amp; records.</div>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <div>
                        <div class="feature-text-title">Step Increment Tracking</div>
                        <div class="feature-text-sub">Automated monitoring of career progression.</div>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-bar-chart-fill"></i></div>
                    <div>
                        <div class="feature-text-title">GAD Analytics</div>
                        <div class="feature-text-sub">Sex-disaggregated workforce reporting (RA 9710).</div>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-value">100%</div>
                    <div class="stat-label">Secure</div>
                </div>
                <div class="stat-div"></div>
                <div class="stat-item">
                    <div class="stat-value">CSC</div>
                    <div class="stat-label">Compliant</div>
                </div>
                <div class="stat-div"></div>
                <div class="stat-item">
                    <div class="stat-value">RA 9710</div>
                    <div class="stat-label">GAD-Ready</div>
                </div>
            </div>

            <p style="font-size:9px;color:rgba(255,255,255,0.25);font-style:italic;margin:0;text-align:center;">
                © {{ date('Y') }} Provincial Government of Bukidnon — PHRMO. All rights reserved.
            </p>

        </div>

        {{-- ── RIGHT FORM PANEL ── --}}
        <div class="right-panel">

            {{-- Mobile logos (small screens only) --}}
            <div class="mobile-logos" style="justify-content:center;align-items:center;gap:1rem;margin-bottom:1.5rem;">
                <img src="{{ asset('img/logo.png') }}" style="height:64px;width:64px;object-fit:contain;">
                <div style="width:1px;height:40px;background:#e2e8f0;"></div>
                <div style="background:var(--color-primary);padding:6px 8px;border-radius:8px;">
                    <img src="{{ asset('img/phrmologo.png') }}" style="height:26px;object-fit:contain;">
                </div>
            </div>

            <div class="welcome-badge">
                <div class="welcome-badge-dot"></div>
                <span>Secure Government Portal — PHRMO</span>
            </div>

            <h2 class="form-heading">Welcome back</h2>
            <p class="form-subtext">Sign in to your authorized HRMDS account to continue</p>

            {{-- ── Inline Error Box (no JS alerts) ── --}}
            @if ($errors->any())
                <div class="error-box" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── Login Form ── --}}
            <form method="POST" action="{{ route('login') }}" id="login-form" novalidate>
                @csrf

                {{-- Email --}}
                <div class="input-group">
                    <label class="input-label" for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope-fill input-icon"></i>
                        <input type="email" id="email" name="email"
                               value="{{ old('email') }}" required autofocus autocomplete="username"
                               class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               placeholder="name@bukidnon.gov.ph">
                    </div>
                    @if($errors->has('email'))
                        <div class="field-error"><i class="bi bi-x-circle-fill"></i> {{ $errors->first('email') }}</div>
                    @endif
                </div>

                {{-- Password --}}
                <div class="input-group">
                    <label class="input-label" for="password">Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" id="password" name="password"
                               required autocomplete="current-password"
                               class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="••••••••"
                               style="padding-right:2.7rem;letter-spacing:0.18em;font-size:15px;">
                        <button type="button" class="pw-toggle" onclick="togglePw()" tabindex="-1"
                                aria-label="Toggle password visibility">
                            <i class="bi bi-eye-fill" id="pw-icon"></i>
                        </button>
                    </div>
                    @if($errors->has('password'))
                        <div class="field-error"><i class="bi bi-x-circle-fill"></i> {{ $errors->first('password') }}</div>
                    @endif
                </div>

                {{-- Options row --}}
                <div class="options-row">
                    <label class="remember-label" for="remember">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember', true) ? 'checked' : '' }}>
                        <span>Keep me signed in</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    @endif
                </div>

                {{-- Sign In button --}}
                <button type="submit" class="btn-signin" id="signin-btn">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Sign In to Your Account
                    </span>
                </button>

                {{-- ── RA 10173 Data Privacy Notice ── --}}
                <div class="privacy-notice" role="note" aria-label="Data Privacy Notice">
                    <i class="bi bi-shield-lock-fill"></i>
                    <p class="privacy-notice-text">
                        By signing in, you acknowledge that your personal data will be processed in accordance with
                        <strong>Republic Act No. 10173</strong> (Data Privacy Act of 2012) and the
                        <a href="{{ route('privacy-policy') }}">Privacy Notice</a> of the Provincial Government of Bukidnon.
                        Unauthorized access is prohibited and subject to criminal penalties under Philippine law.
                    </p>
                </div>

            </form>

            {{-- Footer --}}
            <div class="form-footer">
                <div class="register-cta-wrap">
                    <div class="register-cta-label">New employee? You'll need an account to sign in.</div>
                    <a href="{{ route('register') }}" class="register-cta">
                        <i class="bi bi-person-plus-fill"></i>
                        Request an Account
                    </a>
                </div>
                <p class="form-footer-text">
                    Need technical support? <a href="#">Contact ICTS / PHRMO IT Unit</a>
                </p>
                <div class="footer-links">
                    <a href="#" class="footer-link"><i class="bi bi-question-circle-fill"></i> Help Center</a>
                    <span class="footer-dot"></span>
                    <a href="{{ route('privacy-policy') }}" class="footer-link"><i class="bi bi-shield-check-fill"></i> Privacy Policy</a>
                    <span class="footer-dot"></span>
                    <a href="#" class="footer-link"><i class="bi bi-file-text-fill"></i> Terms of Use</a>
                </div>
                <div class="footer-version">
                    HRMDS v2.0 &nbsp;·&nbsp; RA 10173 Compliant &nbsp;·&nbsp; CSC-Certified &nbsp;·&nbsp; {{ date('Y') }}
                </div>
            </div>

        </div>
    </div>

    <script>
        function togglePw() {
            var inp  = document.getElementById('password');
            var icon = document.getElementById('pw-icon');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.className = 'bi bi-eye-slash-fill';
                inp.style.letterSpacing = 'normal';
                inp.style.fontSize = '13px';
            } else {
                inp.type = 'password';
                icon.className = 'bi bi-eye-fill';
                inp.style.letterSpacing = '0.18em';
                inp.style.fontSize = '15px';
            }
        }

        /* Floating particles */
        (function () {
            var container = document.getElementById('particles');
            for (var i = 0; i < 18; i++) {
                var p = document.createElement('div');
                p.className = 'particle';
                var size = Math.random() * 5 + 2;
                p.style.cssText = [
                    'width:'  + size + 'px',
                    'height:' + size + 'px',
                    'left:'   + (Math.random() * 100) + '%',
                    'bottom:-20px',
                    'animation-delay:'    + (Math.random() * 16) + 's',
                    'animation-duration:' + (Math.random() * 14 + 12) + 's',
                    'opacity:' + (Math.random() * 0.15 + 0.05),
                ].join(';');
                container.appendChild(p);
            }
        })();

        /* Loading state on submit */
        document.getElementById('login-form').addEventListener('submit', function () {
            var btn = document.getElementById('signin-btn');
            btn.innerHTML = '<span class="btn-text"><i class="bi bi-arrow-repeat" style="animation:spin 0.8s linear infinite;display:inline-block;"></i> Signing In…</span>';
            btn.disabled = true;
        });
    </script>

</body>
</html>
