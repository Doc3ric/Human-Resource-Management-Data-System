<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HRMDS Setup Wizard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
* { box-sizing: border-box; }
body { background: linear-gradient(135deg,#0f2a45 0%,#1e4d7b 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; padding: 30px 15px; }

.wizard-container { width: 100%; max-width: 680px; }

.wizard-header { text-align: center; margin-bottom: 30px; }
.wizard-logo { width: 80px; height: 80px; background: rgba(255,255,255,.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 36px; }
.wizard-title { font-size: 24px; font-weight: 800; color: #fff; margin: 0 0 4px; }
.wizard-sub { font-size: 13px; color: rgba(255,255,255,.65); margin: 0; }

.steps-nav { display: flex; gap: 0; margin-bottom: 28px; background: rgba(255,255,255,.1); border-radius: 50px; padding: 4px; }
.step-btn { flex: 1; padding: 8px 4px; border: none; background: none; color: rgba(255,255,255,.6); font-size: 11px; font-weight: 700; border-radius: 50px; text-align: center; cursor: default; transition: .2s; white-space: nowrap; }
.step-btn.done { color: rgba(255,255,255,.85); }
.step-btn.active { background: #fff; color: #113659; font-weight: 800; }

.wizard-card { background: #fff; border-radius: 20px; padding: 36px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }

.step-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; }
.step-title { font-size: 20px; font-weight: 800; color: #0f172a; text-align: center; margin-bottom: 6px; }
.step-desc  { font-size: 13px; color: #6b7280; text-align: center; margin-bottom: 24px; }

.req-row { display: flex; justify-content: space-between; align-items: center; padding: 9px 14px; border-radius: 8px; margin-bottom: 6px; }
.req-row.ok   { background: #f0fdf4; }
.req-row.fail { background: #fef2f2; }
.req-row .rl  { font-size: 13px; font-weight: 600; }
.req-row .rv  { font-size: 12px; font-weight: 700; }
.ok   .rl { color: #166534; } .ok   .rv { color: #16a34a; }
.fail .rl { color: #991b1b; } .fail .rv { color: #dc2626; }

.form-label { font-size: 12px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 5px; }
.form-control, .form-select { border-radius: 10px; font-size: 13.5px; padding: 10px 14px; border: 1.5px solid #e5e7eb; }
.form-control:focus, .form-select:focus { border-color: #113659; box-shadow: 0 0 0 3px rgba(17,54,89,.12); }

.btn-primary-custom { background: linear-gradient(135deg,#113659,#1e4d7b); color: #fff; border: none; padding: 13px 28px; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; width: 100%; transition: .2s; }
.btn-primary-custom:hover { opacity: .9; }
.btn-back { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; padding: 13px 28px; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; display: block; text-align: center; transition: .2s; }
.btn-back:hover { background: #e5e7eb; color: #374151; }

.msg-ok   { background: #f0fdf4; border: 1px solid #86efac; color: #166534; border-radius: 10px; padding: 10px 14px; font-size: 13px; font-weight: 600; display: flex; gap: 8px; }
.msg-fail { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; border-radius: 10px; padding: 10px 14px; font-size: 13px; font-weight: 600; display: flex; gap: 8px; }

.success-icon { width: 90px; height: 90px; background: linear-gradient(135deg,#10b981,#059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 40px; color: #fff; box-shadow: 0 8px 30px rgba(16,185,129,.4); }
</style>
</head>
<body>
<div class="wizard-container">

    <div class="wizard-header">
        <div class="wizard-logo"><i class="bi bi-gear-wide-connected" style="color:#fff;"></i></div>
        <h1 class="wizard-title">HRMDS Setup Wizard</h1>
        <p class="wizard-sub">Human Resource Management Data System &nbsp;·&nbsp; Provincial Government of Bukidnon</p>
    </div>

    <div class="steps-nav">
        @php $steps = ['Welcome','Requirements','Database','Migrate','Admin Account','Complete']; @endphp
        @foreach($steps as $i => $label)
            <div class="step-btn {{ $step == $i+1 ? 'active' : ($step > $i+1 ? 'done' : '') }}">
                {{ $step > $i+1 ? '✓ ' : '' }}{{ $label }}
            </div>
        @endforeach
    </div>

    <div class="wizard-card">

        @php $currentStep = $step ?? 1; @endphp

        {{-- ── STEP 1: Welcome ── --}}
        @if($currentStep == 1)
        <div class="step-icon" style="background:#eff6ff;"><i class="bi bi-house-heart-fill" style="color:#1d4ed8;"></i></div>
        <div class="step-title">Welcome to HRMDS</div>
        <div class="step-desc">This wizard will configure the system for first-time use. It will guide you through requirements, database setup, migrations, and administrator account creation.</div>

        <div style="background:#f0f7ff;border:1px solid #c3daee;border-radius:12px;padding:16px 18px;margin-bottom:24px;">
            <div style="font-size:12px;font-weight:800;color:#1e3a5f;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Before you begin</div>
            <ul style="margin:0;padding-left:18px;font-size:13px;color:#1e3a5f;line-height:1.8;">
                <li>MySQL 8.x database must be created and accessible.</li>
                <li>The <code>.env</code> file must exist in the project root.</li>
                <li>Ensure <code>storage/</code> and <code>bootstrap/cache/</code> are writable.</li>
                <li>PHP 8.1+ required.</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('setup.requirements') }}">
            @csrf
            <button type="submit" class="btn-primary-custom">
                <i class="bi bi-arrow-right-circle-fill me-2"></i>Begin Setup
            </button>
        </form>

        {{-- ── STEP 2: Requirements ── --}}
        @elseif($currentStep == 2)
        <div class="step-icon" style="background:#f0fdf4;"><i class="bi bi-clipboard2-check-fill" style="color:#16a34a;"></i></div>
        <div class="step-title">System Requirements</div>
        <div class="step-desc">Checking your server environment for compatibility.</div>

        @php $allOk = collect($checks ?? [])->every(fn($c) => $c['ok']); @endphp

        @foreach($checks ?? [] as $check)
        <div class="req-row {{ $check['ok'] ? 'ok' : 'fail' }}">
            <span class="rl"><i class="bi {{ $check['ok'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-2"></i>{{ $check['label'] }}</span>
            <span class="rv">{{ $check['value'] }}</span>
        </div>
        @endforeach

        <div class="mt-4">
            @if($allOk)
                <div class="msg-ok mb-3"><i class="bi bi-check-circle-fill"></i> All requirements met. You may proceed.</div>
                <form method="GET" action="{{ route('setup.step', 3) }}">
                    <button type="submit" class="btn-primary-custom"><i class="bi bi-database-fill me-2"></i>Configure Database</button>
                </form>
            @else
                <div class="msg-fail mb-3"><i class="bi bi-exclamation-circle-fill"></i> Some requirements are not met. Fix the issues above before continuing.</div>
                <a href="{{ route('setup.requirements') }}" class="btn-back">Re-check Requirements</a>
            @endif
        </div>

        {{-- ── STEP 3: Database ── --}}
        @elseif($currentStep == 3)
        <div class="step-icon" style="background:#fdf4ff;"><i class="bi bi-database-fill" style="color:#7c3aed;"></i></div>
        <div class="step-title">Database Connection</div>
        <div class="step-desc">Enter your MySQL database credentials to test the connection.</div>

        @if(isset($dbMsg))
            <div class="{{ $dbOk ? 'msg-ok' : 'msg-fail' }} mb-4">
                <i class="bi {{ $dbOk ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                {{ $dbMsg }}
            </div>
        @endif

        <form method="POST" action="{{ route('setup.test-database') }}">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-8">
                    <label class="form-label">Host</label>
                    <input type="text" name="db_host" class="form-control" value="{{ old('db_host', $dbData['db_host'] ?? '127.0.0.1') }}" required>
                </div>
                <div class="col-4">
                    <label class="form-label">Port</label>
                    <input type="number" name="db_port" class="form-control" value="{{ old('db_port', $dbData['db_port'] ?? '3306') }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Database Name</label>
                <input type="text" name="db_name" class="form-control" value="{{ old('db_name', $dbData['db_name'] ?? config('database.connections.mysql.database')) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="db_user" class="form-control" value="{{ old('db_user', $dbData['db_user'] ?? config('database.connections.mysql.username')) }}" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="db_password" class="form-control" placeholder="Leave blank if none">
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('setup.step', 2) }}" class="btn-back" style="flex:.4;">Back</a>
                <button type="submit" class="btn-primary-custom" style="flex:.6;">
                    <i class="bi bi-plug-fill me-2"></i>Test Connection
                </button>
            </div>
        </form>

        {{-- ── STEP 4: Migrate ── --}}
        @elseif($currentStep == 4)
        <div class="step-icon" style="background:#fffbeb;"><i class="bi bi-lightning-charge-fill" style="color:#d97706;"></i></div>
        <div class="step-title">Run Migrations</div>
        <div class="step-desc">Create all database tables for HRMDS. This will run <code>php artisan migrate --force</code>.</div>

        @if(isset($migrateOutput))
            <div class="{{ ($migrateOk??false) ? 'msg-ok' : 'msg-fail' }} mb-3" style="display:block;">
                <div class="fw-bold mb-1"><i class="bi {{ ($migrateOk??false) ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-2"></i>
                    {{ ($migrateOk??false) ? 'Migrations completed.' : 'Migration failed.' }}
                </div>
                <pre style="font-size:11px;margin:8px 0 0;white-space:pre-wrap;word-break:break-word;max-height:160px;overflow:auto;background:rgba(0,0,0,.04);border-radius:6px;padding:8px;">{{ $migrateOutput }}</pre>
            </div>
        @endif

        @if(isset($migrateOk) && $migrateOk)
            <form method="GET" action="{{ route('setup.step', 5) }}">
                <button type="submit" class="btn-primary-custom"><i class="bi bi-person-fill-add me-2"></i>Create Admin Account</button>
            </form>
        @else
            <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:12.5px;color:#78350f;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                This will create all tables required by HRMDS. Existing tables with matching names may be affected.
            </div>
            <form method="POST" action="{{ route('setup.run-migrations') }}">
                @csrf
                <div class="d-flex gap-3">
                    <a href="{{ route('setup.step', 3) }}" class="btn-back" style="flex:.4;">Back</a>
                    <button type="submit" class="btn-primary-custom" style="flex:.6;"><i class="bi bi-lightning-charge-fill me-2"></i>Run Migrations</button>
                </div>
            </form>
        @endif

        {{-- ── STEP 5: Admin Account ── --}}
        @elseif($currentStep == 5)
        <div class="step-icon" style="background:#fef2f2;"><i class="bi bi-person-badge-fill" style="color:#dc2626;"></i></div>
        <div class="step-title">Create Administrator Account</div>
        <div class="step-desc">This will be the first <strong>Super Administrator</strong> account. Use strong credentials and store them securely.</div>

        @if($errors->any())
            <div class="msg-fail mb-3" style="display:block;">
                @foreach($errors->all() as $e)
                    <div>• {{ $e }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('setup.create-admin') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Juan dela Cruz" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="admin@bukidnon.gov.ph" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="At least 10 characters" required>
                <div style="font-size:11px;color:#6b7280;margin-top:4px;">Must contain letters, numbers, and a symbol.</div>
            </div>
            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('setup.step', 4) }}" class="btn-back" style="flex:.4;">Back</a>
                <button type="submit" class="btn-primary-custom" style="flex:.6;"><i class="bi bi-person-check-fill me-2"></i>Create & Finish</button>
            </div>
        </form>

        {{-- ── STEP 6: Complete ── --}}
        @elseif($currentStep == 6)
        <div style="text-align:center;">
            <div class="success-icon"><i class="bi bi-check-lg"></i></div>
            <div class="step-title">Setup Complete!</div>
            <div class="step-desc" style="margin-bottom:24px;">
                HRMDS has been configured successfully. Your administrator account is ready.
            </div>

            <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:16px 20px;text-align:left;margin-bottom:24px;font-size:13px;color:#166534;">
                <div style="font-weight:800;margin-bottom:8px;"><i class="bi bi-check-circle-fill me-2"></i>Account Created</div>
                <div><strong>Name:</strong> {{ $admin->name }}</div>
                <div><strong>Email:</strong> {{ $admin->email }}</div>
                <div><strong>Role:</strong> Super Administrator</div>
            </div>

            <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:12px;padding:14px 18px;margin-bottom:24px;font-size:12.5px;color:#78350f;text-align:left;">
                <i class="bi bi-shield-lock-fill me-1"></i>
                <strong>Security:</strong> This setup wizard is now locked. It will redirect to the dashboard on future visits as long as at least one user account exists.
            </div>

            <a href="{{ route('login') }}"
               style="display:block;background:linear-gradient(135deg,#113659,#1e4d7b);color:#fff;text-decoration:none;padding:14px;border-radius:14px;font-size:15px;font-weight:800;">
                <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login
            </a>
        </div>
        @endif

    </div>{{-- end wizard-card --}}

    <div style="text-align:center;margin-top:18px;font-size:11.5px;color:rgba(255,255,255,.45);">
        HRMDS v2.0 &nbsp;·&nbsp; Provincial Government of Bukidnon &nbsp;·&nbsp; RA 10173 Compliant
    </div>

</div>
</body>
</html>
