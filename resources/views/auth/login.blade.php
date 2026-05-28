<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — HDMS- Human Resource Data Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
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

        /* Deep gradient base */
        .bg-animated::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 20% 40%, #0d3057 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 70%, #0a2540 0%, transparent 60%),
                radial-gradient(ellipse 100% 80% at 50% 0%, #071f38 0%, #030f1e 100%);
        }

        /* Animated orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            animation: drift linear infinite;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #1a5ba8 0%, #0d3462 60%, transparent 100%);
            top: -100px;
            left: -100px;
            animation-duration: 22s;
            animation-name: drift1;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #0e4a8a 0%, #072040 60%, transparent 100%);
            bottom: -80px;
            right: -80px;
            animation-duration: 28s;
            animation-name: drift2;
            opacity: 0.3;
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, #2176c7 0%, #0d3a70 60%, transparent 100%);
            top: 40%;
            right: 20%;
            animation-duration: 18s;
            animation-name: drift3;
            opacity: 0.2;
        }

        @keyframes drift1 {
            0% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(60px, 40px) scale(1.08);
            }

            66% {
                transform: translate(-30px, 70px) scale(0.95);
            }

            100% {
                transform: translate(0, 0) scale(1);
            }
        }

        @keyframes drift2 {
            0% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(-50px, -60px) scale(1.1);
            }

            100% {
                transform: translate(0, 0) scale(1);
            }
        }

        @keyframes drift3 {
            0% {
                transform: translate(0, 0) scale(1);
            }

            40% {
                transform: translate(40px, -50px) scale(1.05);
            }

            100% {
                transform: translate(0, 0) scale(1);
            }
        }

        /* Grid texture */
        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* ── Card ── */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 900px;
            display: flex;
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.07),
                0 30px 80px rgba(0, 0, 0, 0.55),
                0 8px 20px rgba(0, 0, 0, 0.3);
            min-height: 520px;
            backdrop-filter: blur(10px);
        }

        /* ── Left Panel ── */
        .left-panel {
            flex: 1.15;
            background: linear-gradient(155deg, #0d3057 0%, #082040 40%, #05162c 100%);
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
            top: -80px;
            right: -80px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.04) 0%, transparent 70%);
            pointer-events: none;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(33, 118, 199, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Logo area */
        .brand-logo-box {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .divider-v {
            width: 1px;
            height: 60px;
            background: linear-gradient(to bottom, transparent, rgba(255, 255, 255, 0.3), transparent);
        }

        .brand-title {
            font-size: 1.35rem;
            font-weight: 900;
            color: #fff;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .phrmo-chip {
            margin-top: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            padding: 0.25rem 0.55rem;
        }

        .phrmo-chip span {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: rgba(255, 255, 255, 0.75);
            text-transform: uppercase;
        }

        /* Heading */
        .left-heading {
            font-size: 2.4rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.05;
            letter-spacing: -0.02em;
        }

        .left-heading .accent {
            background: linear-gradient(90deg, #60adf5, #a8d4ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .left-subtext {
            font-size: 11.5px;
            color: rgba(255, 255, 255, 0.55);
            font-weight: 500;
            line-height: 1.65;
            margin-top: 0.75rem;
            max-width: 280px;
        }

        /* Feature list */
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.07);
            transition: background 0.2s, border-color 0.2s;
        }

        .feature-item:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(255, 255, 255, 0.13);
        }

        .feature-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0.06) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #93c5fd;
            font-size: 0.9rem;
        }

        .feature-text-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }

        .feature-text-sub {
            font-size: 10.5px;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.4;
            margin-top: 1px;
        }

        /* Stats bar */
        .stats-bar {
            display: flex;
            gap: 1rem;
            padding: 0.85rem 1rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 10px;
        }

        .stat-item {
            flex: 1;
            text-align: center;
        }

        .stat-value {
            font-size: 16px;
            font-weight: 900;
            color: #60adf5;
            line-height: 1;
        }

        .stat-label {
            font-size: 9px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 3px;
        }

        .stat-divider {
            width: 1px;
            background: rgba(255, 255, 255, 0.08);
            align-self: stretch;
        }

        /* ── Right Panel ── */
        .right-panel {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.5rem 2.75rem;
            position: relative;
            overflow: hidden;
        }

        .right-panel::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(17, 54, 89, 0.04) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Welcome */
        .welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #eef6ff;
            border: 1px solid #c3e0fb;
            border-radius: 100px;
            padding: 0.28rem 0.75rem;
            margin-bottom: 1rem;
        }

        .welcome-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #2176c7;
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(0.75);
            }
        }

        .welcome-badge span {
            font-size: 10.5px;
            font-weight: 700;
            color: #1a5ba8;
            letter-spacing: 0.05em;
        }

        .form-heading {
            font-size: 1.75rem;
            font-weight: 900;
            color: #0a1525;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .form-subtext {
            font-size: 12.5px;
            color: #718096;
            font-weight: 500;
            margin-top: 0.3rem;
            margin-bottom: 1.75rem;
        }

        /* Input groups */
        .input-group {
            margin-bottom: 1rem;
        }

        .input-label {
            display: block;
            font-size: 10.5px;
            font-weight: 800;
            color: #3d4a5c;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.45rem;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 14px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 0.7rem 2.5rem 0.7rem 2.75rem;
            background: #f7f9fc;
            border: 1.5px solid #e8edf4;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            color: #1a202c;
            outline: none;
            transition: all 0.2s;
        }

        .form-input::placeholder {
            color: #b0beca;
            font-weight: 400;
        }

        .form-input:focus {
            background: #fff;
            border-color: #113659;
            box-shadow: 0 0 0 3px rgba(17, 54, 89, 0.08);
        }

        .form-input:focus~.input-icon,
        .input-wrap:focus-within .input-icon {
            color: #113659;
        }

        .pw-toggle {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #a0aec0;
            font-size: 14px;
            padding: 0;
            transition: color 0.2s;
            outline: none;
        }

        .pw-toggle:hover {
            color: #4a5568;
        }

        /* Options row */
        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
        }

        .remember-label input[type="checkbox"] {
            width: 14px;
            height: 14px;
            accent-color: #113659;
            cursor: pointer;
        }

        .remember-label span {
            font-size: 12px;
            font-weight: 600;
            color: #4a5568;
        }

        .forgot-link {
            font-size: 12px;
            font-weight: 700;
            color: #113659;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #1a5ba8;
            text-decoration: underline;
        }

        /* Submit button */
        .btn-signin {
            width: 100%;
            background: linear-gradient(135deg, #113659 0%, #082040 100%);
            color: #fff;
            font-size: 13.5px;
            font-weight: 800;
            letter-spacing: 0.05em;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 14px rgba(8, 32, 64, 0.35);
        }

        .btn-signin::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 60%);
            pointer-events: none;
        }

        .btn-signin:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(8, 32, 64, 0.45);
        }

        .btn-signin:active {
            transform: translateY(0);
            box-shadow: 0 3px 10px rgba(8, 32, 64, 0.3);
        }

        .btn-signin .btn-text {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        /* Error */
        .error-box {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            background: #fff5f5;
            border: 1px solid #feb2b2;
            border-radius: 8px;
            padding: 0.65rem 0.85rem;
            margin-bottom: 1.25rem;
        }

        .error-box i {
            color: #e53e3e;
            font-size: 13px;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .error-box p {
            font-size: 12px;
            font-weight: 600;
            color: #c53030;
            margin: 0;
        }

        /* Footer */
        .form-footer {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid #edf2f7;
            text-align: center;
        }

        .form-footer-text {
            font-size: 11px;
            color: #a0aec0;
            font-weight: 500;
        }

        .form-footer-text a {
            font-weight: 800;
            color: #113659;
            text-decoration: none;
        }

        .form-footer-text a:hover {
            text-decoration: underline;
        }

        .footer-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            margin-top: 0.75rem;
        }

        .footer-link {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 9.5px;
            font-weight: 700;
            color: #b0bec5;
            text-decoration: none;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            transition: color 0.2s;
        }

        .footer-link:hover {
            color: #718096;
        }

        /* Governor image */


        /* Mobile logo shown only on small screens */
        .mobile-logos {
            display: none;
        }

        @media (max-width: 767px) {
            .left-panel {
                display: none;
            }

            .right-panel {
                padding: 2rem 1.75rem;
            }

            .mobile-logos {
                display: flex;
            }

            .login-card {
                max-width: 420px;
            }

            .gov-image {
                height: 200px;
                z-index: 3;
                opacity: 0.6;
            }
        }

        /* Floating particles */
        .particles {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            animation: float-up linear infinite;
        }

        @keyframes float-up {
            0% {
                transform: translateY(100vh) scale(1);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 0.5;
            }

            100% {
                transform: translateY(-10vh) scale(0.8);
                opacity: 0;
            }
        }

        /* Input loading state */
        .form-input:focus {
            animation: focus-in 0.2s ease;
        }

        @keyframes focus-in {
            from {
                box-shadow: 0 0 0 0 rgba(17, 54, 89, 0);
            }

            to {
                box-shadow: 0 0 0 3px rgba(17, 54, 89, 0.08);
            }
        }
    </style>
</head>

<body>

    {{-- Animated Background --}}
    <div class="bg-animated">
        <div class="bg-grid"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    {{-- Floating particles --}}
    <div class="particles" id="particles"></div>



    {{-- ── Login Card ── --}}
    <div class="login-card">

        {{-- LEFT BRANDING PANEL --}}
        <div class="left-panel">

            {{-- Branding --}}
            <div class="brand-logo-box">
                <img src="{{ asset('img/logo.png') }}" alt="CSC Seal"
                    style="width:76px;height:76px;object-fit:contain;filter:drop-shadow(0 4px 12px rgba(0,0,0,0.4));"
                    onerror="this.style.display='none'">
                <div class="divider-v"></div>
                <div>
                    <div class="brand-title">CSC<br>Plantilla</div>
                    <div class="phrmo-chip">
                        <img src="{{ asset('img/phrmologo.png') }}" alt="PHRMO"
                            style="height:16px;object-fit:contain;max-width:44px;" onerror="this.style.display='none'">
                        <span>PHRMO</span>
                    </div>
                </div>
            </div>

            {{-- Headline --}}
            <div>
                <h1 class="left-heading">
                    Personnel<br>
                    <span class="accent">Management</span><br>
                    System
                </h1>
                <p class="left-subtext">
                    Centralized records and workforce analytics designed for the Civil Service Commission. Modernize
                    your HR operations with ease.
                </p>
            </div>

            {{-- Features --}}
            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="feature-text-title">Inventory of Personnel</div>
                        <div class="feature-text-sub">Comprehensive workforce demographics & data.</div>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <div>
                        <div class="feature-text-title">Step Increment Tracking</div>
                        <div class="feature-text-sub">Automated monitoring of career progression steps.</div>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-bar-chart-fill"></i></div>
                    <div>
                        <div class="feature-text-title">Real-time Analytics</div>
                        <div class="feature-text-sub">Instant insights into organizational structure.</div>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-value">100%</div>
                    <div class="stat-label">Secure</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-value">CSC</div>
                    <div class="stat-label">Compliant</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-value">24/7</div>
                    <div class="stat-label">Available</div>
                </div>
            </div>

            {{-- Footer --}}
            <p style="font-size:9.5px;color:rgba(255,255,255,0.3);font-style:italic;margin:0;">
                © {{ date('Y') }} Civil Service Commission. All rights reserved.
            </p>

        </div>

        {{-- RIGHT FORM PANEL --}}
        <div class="right-panel">

            {{-- Mobile logos (small screens only) --}}
            <div class="mobile-logos" style="justify-content:center;align-items:center;gap:1rem;margin-bottom:1.5rem;">
                <img src="{{ asset('img/logo.png') }}" style="height:64px;width:64px;object-fit:contain;">
                <div style="width:1px;height:40px;background:#e2e8f0;"></div>
                <div style="background:#113659;padding:6px 8px;border-radius:8px;">
                    <img src="{{ asset('img/phrmologo.png') }}" style="height:26px;object-fit:contain;">
                </div>
            </div>

            {{-- Welcome badge --}}
            <div class="welcome-badge">
                <div class="welcome-badge-dot"></div>
                <span>Secure Government Portal</span>
            </div>

            <h2 class="form-heading">Welcome back</h2>
            <p class="form-subtext">Sign in to your account to continue</p>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="error-box">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="input-group">
                    <label class="input-label" for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope-fill input-icon" style="left:14px;"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="form-input" placeholder="name@agency.gov.ph" style="padding-left:2.7rem;">
                    </div>
                </div>

                {{-- Password --}}
                <div class="input-group">
                    <label class="input-label" for="password">Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill input-icon" style="left:14px;"></i>
                        <input type="password" id="password" name="password" required class="form-input"
                            placeholder="••••••••"
                            style="padding-left:2.7rem;padding-right:2.7rem;letter-spacing:0.18em;font-size:15px;">
                        <button type="button" class="pw-toggle" onclick="togglePw()" tabindex="-1">
                            <i class="bi bi-eye-fill" id="pw-icon"></i>
                        </button>
                    </div>
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

                {{-- Sign in button --}}
                <button type="submit" class="btn-signin" id="signin-btn">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Sign In to Your Account
                    </span>
                </button>
            </form>

            {{-- Footer --}}
            <div class="form-footer">
                <p class="form-footer-text">
                    Need technical support?
                    <a href="#">Contact IT Helpdesk</a>
                </p>
                <div class="footer-links">
                    <a href="#" class="footer-link">
                        <i class="bi bi-question-circle-fill"></i> Help Center
                    </a>
                    <span
                        style="width:3px;height:3px;border-radius:50%;background:#d1d5db;display:inline-block;"></span>
                    <a href="#" class="footer-link">
                        <i class="bi bi-shield-check-fill"></i> Privacy Policy
                    </a>
                    <span
                        style="width:3px;height:3px;border-radius:50%;background:#d1d5db;display:inline-block;"></span>
                    <a href="#" class="footer-link">
                        <i class="bi bi-file-text-fill"></i> Terms
                    </a>
                </div>
            </div>

        </div>
    </div>

    <script>
        /* ── Password toggle ── */
        function togglePw() {
            var inp = document.getElementById('password');
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

        /* ── Floating particles ── */
        (function () {
            var container = document.getElementById('particles');
            var count = 18;
            for (var i = 0; i < count; i++) {
                var p = document.createElement('div');
                p.className = 'particle';
                var size = Math.random() * 5 + 2;
                var left = Math.random() * 100;
                var delay = Math.random() * 16;
                var duration = Math.random() * 14 + 12;
                p.style.cssText = [
                    'width:' + size + 'px',
                    'height:' + size + 'px',
                    'left:' + left + '%',
                    'bottom:-20px',
                    'animation-delay:' + delay + 's',
                    'animation-duration:' + duration + 's',
                    'opacity:' + (Math.random() * 0.15 + 0.05),
                ].join(';');
                container.appendChild(p);
            }
        })();

        /* ── Sign-in button loading state ── */
        document.querySelector('form').addEventListener('submit', function () {
            var btn = document.getElementById('signin-btn');
            btn.innerHTML = '<span class="btn-text"><i class="bi bi-arrow-repeat" style="animation:spin 0.8s linear infinite;display:inline-block;"></i> Signing In…</span>';
            btn.disabled = true;
            btn.style.opacity = '0.85';
        });
    </script>

    <style>
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
</body>

</html>