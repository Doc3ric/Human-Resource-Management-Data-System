<x-dashboard-app>
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;max-width:420px;margin:0 auto;text-align:center;">
    <h2 style="font-size:16px;font-weight:800;"><i class="bi bi-shield-lock-fill"></i> MFA Challenge</h2>
    <p style="font-size:12.5px;color:#6b7280;">Enter your 6-digit authenticator code to continue to RACCS-Confidential content.</p>

    @if(session('error'))
        <div style="background:#fffbeb;color:#92400e;padding:10px 14px;border-radius:8px;margin:14px 0;font-size:13px;">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div style="background:#fef2f2;color:#991b1b;padding:10px 14px;border-radius:8px;margin:14px 0;font-size:13px;">{{ $errors->first() }}</div>
    @endif

    @if(!auth()->user()->google2fa_enabled)
        <p style="font-size:12.5px;">MFA is not yet set up for your account.</p>
        <a href="{{ route('mfa.setup') }}" class="btn btn-primary">Set Up MFA</a>
    @else
        <form method="POST" action="{{ route('mfa.verify') }}">
            @csrf
            <input type="text" name="one_time_password" placeholder="6-digit code" required
                style="text-align:center;font-size:20px;letter-spacing:4px;padding:10px;border:1px solid #d1d5db;border-radius:8px;width:180px;">
            <div style="margin-top:14px;">
                <button class="btn btn-primary">Verify</button>
            </div>
        </form>
    @endif
</div>
</x-dashboard-app>
