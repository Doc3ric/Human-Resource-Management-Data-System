<x-dashboard-app>
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;max-width:480px;margin:0 auto;text-align:center;">
    <h2 style="font-size:16px;font-weight:800;">Set Up MFA for RACCS-Confidential Access</h2>
    <p style="font-size:12.5px;color:#6b7280;">Scan this QR code with an authenticator app (Google Authenticator, Authy, etc.), then enter the 6-digit code to confirm.</p>

    @if ($errors->any())
        <div style="background:#fef2f2;color:#991b1b;padding:10px 14px;border-radius:8px;margin:14px 0;font-size:13px;">{{ $errors->first() }}</div>
    @endif

    <div style="margin:20px 0;">{!! $qrCodeUrl !!}</div>

    <form method="POST" action="{{ route('mfa.enable') }}">
        @csrf
        <input type="text" name="one_time_password" placeholder="6-digit code" required
            style="text-align:center;font-size:20px;letter-spacing:4px;padding:10px;border:1px solid #d1d5db;border-radius:8px;width:180px;">
        <div style="margin-top:14px;">
            <button class="btn btn-primary">Enable MFA</button>
        </div>
    </form>
</div>
</x-dashboard-app>
