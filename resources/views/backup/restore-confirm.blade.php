<x-dashboard-app>

<div style="max-width:600px;margin:0 auto;padding:20px 0;">

    <div style="background:#fff;border-radius:16px;padding:32px;border:1.5px solid #fca5a5;box-shadow:0 4px 20px rgba(220,38,38,.1);">

        <div style="text-align:center;margin-bottom:24px;">
            <div style="width:70px;height:70px;background:linear-gradient(135deg,#ef4444,#dc2626);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 6px 20px rgba(220,38,38,.3);">
                <i class="bi bi-arrow-counterclockwise" style="font-size:28px;color:#fff;"></i>
            </div>
            <h2 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 6px;">Restore Database</h2>
            <p style="font-size:13px;color:#6b7280;margin:0;">This action will overwrite the current database with the selected backup.</p>
        </div>

        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px;margin-bottom:20px;">
            <div style="font-size:12px;font-weight:700;color:#991b1b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">⚠ Irreversible Action</div>
            <ul style="font-size:12.5px;color:#7f1d1d;line-height:1.7;padding-left:16px;margin:0;">
                <li>All data entered AFTER this backup was created will be <strong>permanently lost</strong>.</li>
                <li>This action is permanently logged in the audit trail regardless of outcome.</li>
                <li>Only the <strong>Administrator</strong> role can perform this action.</li>
                <li>You cannot undo a restore — create a fresh backup before proceeding if needed.</li>
            </ul>
        </div>

        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:12.5px;">
            <div style="display:grid;grid-template-columns:auto 1fr;gap:6px 16px;color:#374151;">
                <span style="font-weight:600;">File:</span>      <span style="font-family:monospace;">{{ $backup->filename }}</span>
                <span style="font-weight:600;">Created:</span>   <span>{{ $backup->created_at->format('d M Y H:i') }}</span>
                <span style="font-weight:600;">Size:</span>      <span>{{ $backup->formatted_size }}</span>
                <span style="font-weight:600;">Created By:</span><span>{{ $backup->creator?->name ?? '—' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('backup.restore', $backup) }}">
            @csrf
            @method('POST')
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12px;font-weight:800;color:#374151;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">
                    Type <strong style="color:#dc2626;">CONFIRM</strong> to proceed:
                </label>
                <input type="text" name="confirmation" id="confirmation" autocomplete="off"
                       style="width:100%;padding:10px 14px;border:1.5px solid #d1d5db;border-radius:8px;font-size:14px;font-family:monospace;letter-spacing:.08em;outline:none;"
                       placeholder="CONFIRM">
                @error('confirmation')
                    <div style="color:#dc2626;font-size:12px;margin-top:4px;font-weight:600;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:flex;gap:12px;">
                <a href="{{ route('backup.index') }}"
                   style="flex:1;background:#f3f4f6;border:1px solid #d1d5db;color:#374151;padding:11px;border-radius:10px;font-weight:600;font-size:13px;text-align:center;text-decoration:none;">
                    Cancel — Go Back
                </a>
                <button type="submit" id="restoreBtn"
                        style="flex:1;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;border:none;padding:11px;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer;">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Confirm Restore
                </button>
            </div>
        </form>

    </div>
</div>

<script>
document.getElementById('confirmation').addEventListener('input', function() {
    var btn = document.getElementById('restoreBtn');
    btn.style.opacity = this.value === 'CONFIRM' ? '1' : '0.5';
    btn.disabled = this.value !== 'CONFIRM';
});
document.getElementById('restoreBtn').disabled = true;
document.getElementById('restoreBtn').style.opacity = '0.5';
</script>

</x-dashboard-app>
