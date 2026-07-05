<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password — HRMDS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #0f2942 0%, #113659 60%, #1a4a73 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 24px 64px rgba(0,0,0,.22);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #113659, #1a5276);
            padding: 32px 32px 28px;
            text-align: center;
        }
        .lock-icon {
            width: 64px; height: 64px;
            background: rgba(255,255,255,.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px; color: #fff;
        }
        .card-header h1 { color: #fff; font-size: 20px; font-weight: 800; margin-bottom: 6px; }
        .card-header p  { color: rgba(255,255,255,.75); font-size: 13px; line-height: 1.5; }
        .card-body { padding: 32px; }
        .notice {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 10px;
            padding: 12px 14px;
            display: flex; gap: 10px; align-items: flex-start;
            margin-bottom: 24px;
            font-size: 13px; color: #92400e; line-height: 1.5;
        }
        .notice i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
        label { display: block; font-size: 12px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
        .input-wrap { position: relative; margin-bottom: 18px; }
        input[type=password], input[type=text] {
            width: 100%; padding: 11px 44px 11px 14px;
            border: 1.5px solid #d1d5db; border-radius: 8px;
            font-size: 14px; color: #111827;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        input:focus { border-color: #113659; box-shadow: 0 0 0 3px rgba(17,54,89,.1); }
        .toggle-btn {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; font-size: 11px; font-weight: 800;
            color: #6b7280; cursor: pointer; padding: 4px;
        }
        .error-msg { font-size: 12px; color: #dc2626; margin-top: 4px; margin-bottom: 8px; }
        .strength-bar { height: 4px; border-radius: 99px; background: #e5e7eb; margin-top: 6px; overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 99px; transition: width .3s, background .3s; width: 0; }
        .strength-label { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .btn {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #113659, #1a5276);
            color: #fff; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            transition: opacity .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 8px;
        }
        .btn:hover { opacity: .9; }
        .req-list { font-size: 12px; color: #6b7280; margin: -6px 0 18px; list-style: none; }
        .req-list li { display: flex; align-items: center; gap: 6px; margin-bottom: 3px; }
        .req-list li.met { color: #16a34a; }
        .req-list li i { font-size: 12px; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <div class="lock-icon"><i class="bi bi-key-fill"></i></div>
        <h1>Set Your New Password</h1>
        <p>For security, you must create a personal password before accessing the system.</p>
    </div>
    <div class="card-body">
        <div class="notice">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Your account was assigned a temporary password by an administrator. Choose a strong personal password that only you know.</span>
        </div>

        <form method="POST" action="{{ route('password.force-change.update') }}" id="pwForm">
            @csrf

            <label for="password">New Password</label>
            <div class="input-wrap">
                <input type="password" id="password" name="password"
                       autocomplete="new-password" autofocus
                       oninput="checkStrength(this.value)">
                <button type="button" class="toggle-btn" onclick="togglePw('password', this)">SHOW</button>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
            <div class="strength-label" id="strengthLabel">Enter a password</div>
            <ul class="req-list" id="reqList" style="margin-top:8px;">
                <li id="req-len"><i class="bi bi-circle"></i> At least 8 characters</li>
                <li id="req-upper"><i class="bi bi-circle"></i> One uppercase letter</li>
                <li id="req-num"><i class="bi bi-circle"></i> One number</li>
                <li id="req-special"><i class="bi bi-circle"></i> One special character</li>
            </ul>
            @error('password')
                <div class="error-msg">{{ $message }}</div>
            @enderror

            <label for="password_confirmation">Confirm New Password</label>
            <div class="input-wrap">
                <input type="password" id="password_confirmation" name="password_confirmation"
                       autocomplete="new-password">
                <button type="button" class="toggle-btn" onclick="togglePw('password_confirmation', this)">SHOW</button>
            </div>
            @error('password_confirmation')
                <div class="error-msg">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn">
                <i class="bi bi-shield-lock-fill"></i> Set Password & Continue
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin-top:16px;text-align:center;">
            @csrf
            <button type="submit" style="background:none;border:none;font-size:13px;color:#6b7280;cursor:pointer;text-decoration:underline;">
                Log out instead
            </button>
        </form>
    </div>
</div>

<script>
function togglePw(id, btn) {
    var inp = document.getElementById(id);
    if (inp.type === 'password') { inp.type = 'text'; btn.textContent = 'HIDE'; }
    else { inp.type = 'password'; btn.textContent = 'SHOW'; }
}

function checkStrength(val) {
    var fill = document.getElementById('strengthFill');
    var lbl  = document.getElementById('strengthLabel');

    var checks = {
        len:     val.length >= 8,
        upper:   /[A-Z]/.test(val),
        num:     /[0-9]/.test(val),
        special: /[^A-Za-z0-9]/.test(val),
    };

    ['len','upper','num','special'].forEach(function(k) {
        var li = document.getElementById('req-' + k);
        var ic = li.querySelector('i');
        if (checks[k]) {
            li.classList.add('met');
            ic.className = 'bi bi-check-circle-fill';
        } else {
            li.classList.remove('met');
            ic.className = 'bi bi-circle';
        }
    });

    var score = Object.values(checks).filter(Boolean).length;
    var colors = ['#ef4444','#f97316','#eab308','#22c55e'];
    var labels = ['Weak','Fair','Good','Strong'];
    fill.style.width = (score * 25) + '%';
    fill.style.background = colors[score - 1] || '#e5e7eb';
    lbl.textContent = score > 0 ? labels[score - 1] : 'Enter a password';
    lbl.style.color = colors[score - 1] || '#6b7280';
}
</script>
</body>
</html>
