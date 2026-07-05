<x-dashboard-app>

<style>
.backup-card { background:#fff;border-radius:14px;padding:24px;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:20px; }
.status-badge { padding:3px 10px;border-radius:6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em; }
.status-success { background:#d1fae5;color:#065f46; }
.status-pending { background:#fef3c7;color:#92400e; }
.status-failed  { background:#fee2e2;color:#991b1b; }
.status-restored{ background:#ede9fe;color:#5b21b6; }
</style>

<div style="max-width:1100px;margin:0 auto;">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:22px;font-weight:800;color:#111827;margin:0;">Backup &amp; Recovery</h1>
            <p style="font-size:12.5px;color:#6b7280;margin:4px 0 0;">
                AES-256-GCM encrypted backups &nbsp;·&nbsp; Administrator access only
            </p>
        </div>
        <button data-bs-toggle="modal" data-bs-target="#createBackupModal"
                style="background:var(--color-primary,#113659);color:#fff;border:none;padding:10px 20px;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:7px;">
            <i class="bi bi-cloud-arrow-up-fill"></i> Create Backup
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="border-radius:10px;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius:10px;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Security Notice --}}
    <div style="background:#f0f7ff;border:1px solid #c3daee;border-radius:12px;padding:14px 18px;display:flex;gap:12px;margin-bottom:20px;">
        <i class="bi bi-shield-lock-fill" style="color:#1d4ed8;font-size:20px;flex-shrink:0;margin-top:2px;"></i>
        <div style="font-size:12.5px;color:#1e3a5f;line-height:1.6;">
            <strong>Security:</strong> Backups are encrypted with AES-256-GCM using a key derived from <code>APP_KEY</code>
            (stored only in <code>.env</code> — never in the database or any file). The backup log table
            is append-only at the application level. Restore operations require typing
            <strong>CONFIRM</strong> and are permanently recorded in the audit trail.
            @if($driveConfigured)
                <br><span style="color:#065f46;"><i class="bi bi-check-circle-fill"></i> Google Drive integration is configured.</span>
            @else
                <br><span style="color:#92400e;"><i class="bi bi-exclamation-triangle-fill"></i> Google Drive not configured — local storage only. Set <code>GOOGLE_SA_KEY_PATH</code> in <code>.env</code> to enable.</span>
            @endif
        </div>
    </div>

    {{-- Backup Log Table --}}
    <div class="backup-card">
        <h3 style="font-size:16px;font-weight:800;color:#111827;margin-bottom:16px;">Backup History</h3>
        @if($logs->count())
        <div class="table-responsive">
            <table class="table" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Filename</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Storage</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                <tr>
                    <td class="text-muted">{{ $log->id }}</td>
                    <td style="font-family:monospace;font-size:11px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $log->filename }}">
                        {{ $log->filename }}
                    </td>
                    <td><span class="badge bg-secondary">{{ ucfirst($log->type) }}</span></td>
                    <td>{{ $log->formatted_size }}</td>
                    <td>
                        <span class="badge {{ $log->storage_driver === 'google_drive' ? 'bg-primary' : 'bg-secondary' }}">
                            <i class="bi {{ $log->storage_driver === 'google_drive' ? 'bi-google' : 'bi-hdd-fill' }}"></i>
                            {{ $log->storage_driver === 'google_drive' ? 'Google Drive' : 'Local' }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $log->status }}">{{ ucfirst($log->status) }}</span>
                    </td>
                    <td>{{ $log->creator?->name ?? '—' }}</td>
                    <td style="white-space:nowrap;font-size:11px;">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:nowrap;">
                            @if($log->status === 'success' && $log->storage_driver === 'local' && $log->storage_path)
                                <a href="{{ route('backup.download', $log) }}"
                                   style="background:#f3f4f6;border:1px solid #d1d5db;color:#374151;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                <a href="{{ route('backup.restore-confirm', $log) }}"
                                   style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </a>
                            @endif
                            @if($log->status === 'success' && $log->storage_path)
                                <form method="POST" action="{{ route('backup.delete-file', $log) }}"
                                      onsubmit="return confirm('Delete this backup file from server storage? The log entry will be kept.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:#9ca3af;cursor:pointer;font-size:11px;font-weight:600;padding:4px 8px;" title="Delete file">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-cloud-slash" style="font-size:2.5rem;opacity:.3;"></i>
                <p style="margin-top:12px;font-size:13px;">No backup records yet. Create your first backup above.</p>
            </div>
        @endif
    </div>

</div>

{{-- Create Backup Modal --}}
<div class="modal fade" id="createBackupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <form method="POST" action="{{ route('backup.create') }}" id="backupForm">
                @csrf
                <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:20px 24px;">
                    <h5 class="modal-title fw-bold" style="font-size:17px;">
                        <i class="bi bi-cloud-arrow-up-fill me-2 text-primary"></i>Create New Backup
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:20px 24px;">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">Storage Destination</label>
                        <select name="driver" class="form-select" style="font-size:13px;">
                            <option value="local">Local Server Storage</option>
                            @if($driveConfigured)
                                <option value="google_drive">Google Drive (Service Account)</option>
                            @endif
                        </select>
                    </div>
                    <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:8px;padding:10px 12px;font-size:12px;color:#78350f;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Warning:</strong> Creating a backup may take several minutes depending on database size.
                        Do not close this window until the process completes.
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold" id="backupSubmitBtn">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Start Backup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('backupForm').addEventListener('submit', function() {
    var btn = document.getElementById('backupSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating backup…';
});
</script>

</x-dashboard-app>
