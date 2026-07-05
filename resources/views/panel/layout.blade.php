<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Portal') — PHRMO</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background:#f1f5f9; font-family:'Segoe UI',sans-serif; }
        .panel-topbar {
            background:linear-gradient(135deg,#1e3a5f,#2d6a4f);
            color:#fff;
            padding:0 24px;
            height:64px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            box-shadow:0 2px 8px rgba(0,0,0,.15);
            position:sticky;
            top:0;
            z-index:100;
        }
        .panel-topbar .brand {
            display:flex;
            align-items:center;
            gap:12px;
        }
        .panel-topbar .brand-icon {
            width:36px; height:36px;
            background:rgba(255,255,255,.2);
            border-radius:8px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:18px;
        }
        .panel-topbar .brand-text { font-size:14px; font-weight:700; line-height:1.2; }
        .panel-topbar .brand-sub  { font-size:10px; opacity:.7; }
        .badge-type {
            font-size:11px;
            font-weight:700;
            padding:4px 12px;
            border-radius:99px;
        }
        .badge-hrmpsb { background:rgba(255,255,255,.2); color:#fff; }
        .badge-twg    { background:rgba(253,224,71,.3);  color:#fef08a; }
        .user-info {
            display:flex;
            align-items:center;
            gap:12px;
            font-size:13px;
        }
        .logout-btn {
            background:rgba(255,255,255,.15);
            border:1px solid rgba(255,255,255,.3);
            color:#fff;
            border-radius:6px;
            padding:5px 12px;
            font-size:12px;
            font-weight:600;
            text-decoration:none;
            display:flex;
            align-items:center;
            gap:5px;
            transition:background .15s;
        }
        .logout-btn:hover { background:rgba(255,255,255,.25); color:#fff; }
        .panel-main { padding:28px 24px; max-width:900px; margin:0 auto; }
        .panel-card {
            background:#fff;
            border-radius:12px;
            box-shadow:0 1px 4px rgba(0,0,0,.08);
            padding:28px;
            margin-bottom:20px;
        }
        .panel-card-header {
            font-size:16px;
            font-weight:700;
            color:#111827;
            margin-bottom:20px;
            padding-bottom:12px;
            border-bottom:2px solid #e5e7eb;
            display:flex;
            align-items:center;
            gap:8px;
        }
        .form-label { font-size:13px; font-weight:600; color:#374151; }
        .form-control, .form-select {
            border-radius:8px;
            border-color:#d1d5db;
            font-size:13px;
        }
        .form-control:focus, .form-select:focus {
            border-color:#1e3a5f;
            box-shadow:0 0 0 3px rgba(30,58,95,.1);
        }
        .btn-save {
            background:linear-gradient(135deg,#1e3a5f,#2d6a4f);
            color:#fff;
            border:none;
            border-radius:8px;
            padding:10px 28px;
            font-weight:700;
            font-size:13px;
        }
        .btn-save:hover { opacity:.9; color:#fff; }
        .alert-success-custom {
            background:#ecfdf5;
            border:1px solid #a7f3d0;
            color:#065f46;
            border-radius:8px;
            padding:12px 16px;
            font-size:13px;
            display:flex;
            align-items:center;
            gap:8px;
            margin-bottom:16px;
        }
    </style>
    @yield('styles')
</head>
<body>

<div class="panel-topbar">
    <div class="brand">
        <div class="brand-icon"><i class="bi bi-person-badge-fill"></i></div>
        <div>
            <div class="brand-text">Panel Portal — PHRMO</div>
            <div class="brand-sub">Provincial Human Resource Management Office, Bukidnon</div>
        </div>
    </div>
    <div class="user-info">
        @php $pm = auth('panel')->user(); @endphp
        <span class="badge-type {{ $pm->isTwg() ? 'badge-twg' : 'badge-hrmpsb' }}">
            <i class="bi bi-{{ $pm->isTwg() ? 'star-fill' : 'people-fill' }}"></i>
            {{ $pm->typeLabel() }}
        </span>
        <span style="opacity:.9;">{{ $pm->name }}</span>
        <form method="POST" action="{{ route('panel.logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="bi bi-box-arrow-right"></i> Log Out
            </button>
        </form>
    </div>
</div>

<div class="panel-main">
    @if(session('success'))
        <div class="alert-success-custom">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
