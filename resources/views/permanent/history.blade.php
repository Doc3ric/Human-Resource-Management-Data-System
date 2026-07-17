<x-dashboard-app>
    <style>
        .hist-hero { background:linear-gradient(135deg,#052c65,#1e40af); border-radius:14px; padding:22px 28px; margin-bottom:22px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .hist-hero h1 { color:#fff; font-size:20px; font-weight:800; margin:0; }
        .hist-hero p  { color:rgba(255,255,255,.65); font-size:12px; margin:4px 0 0; }

        .hist-table-wrap { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); }
        .hist-table { width:100%; border-collapse:separate; border-spacing:0; }
        .hist-table thead tr { background:linear-gradient(135deg,#052c65,#1e40af); }
        .hist-table th { padding:11px 14px; text-align:left; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.8px; color:rgba(255,255,255,.8); white-space:nowrap; }
        .hist-table tbody tr:nth-child(even) td { background:#eff6ff; }
        .hist-table tbody tr:hover td { background:#dbeafe !important; }
        .hist-table td { padding:11px 14px; font-size:13px; color:#374151; border-bottom:1px solid #f1f5f9; }
        .hist-table tbody tr:last-child td { border-bottom:0; }

        .badge-action { display:inline-flex; align-items:center; padding:2px 10px; border-radius:99px; font-size:10px; font-weight:700; }
        .badge-import { background:#dbeafe; color:#1e40af; }
        .badge-undo   { background:#fef3c7; color:#92400e; }

        .stat-chip { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:6px; font-size:11px; font-weight:700; margin-right:4px; }
        .chip-created { background:#d1fae5; color:#065f46; }
        .chip-skipped { background:#fef3c7; color:#92400e; }

        .btn-undo { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:7px; font-size:11px; font-weight:700; background:#fef3c7; color:#92400e; border:1px solid #fde68a; cursor:pointer; transition:all .15s; }
        .btn-undo:hover { background:#fde68a; }
        .btn-undo form { display:inline; }

        .flash-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:10px; padding:11px 16px; margin-bottom:14px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px; }
        .flash-error   { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:10px; padding:11px 16px; margin-bottom:14px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px; }
    </style>

    {{-- Hero --}}
    <div class="hist-hero">
        <div>
            <h1><i class="bi bi-clock-history me-2"></i>Permanent Import History</h1>
            <p>Records of all past Permanent/CT/Elected data imports</p>
        </div>
        <a href="{{ route('permanent.index') }}"
           style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);color:#fff;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Back to Permanent
        </a>
    </div>

    @if(session('success'))
        <div class="flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
    @endif

    <div class="hist-table-wrap">
        <table class="hist-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date &amp; Time</th>
                    <th>Action</th>
                    <th>Imported By</th>
                    <th>Stats</th>
                    <th>Undo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $info    = json_decode($log->description, true) ?? [];
                        $created = $info['created'] ?? 0;
                        $skipped = $info['skipped'] ?? 0;
                        $isUndo  = $log->action === 'Undid Permanent Import';
                    @endphp
                    <tr>
                        <td style="color:#9ca3af;font-size:11px;font-weight:600;">{{ $loop->iteration }}</td>
                        <td style="white-space:nowrap;font-size:12px;">
                            {{ $log->created_at->format('M d, Y') }}<br>
                            <span style="color:#94a3b8;">{{ $log->created_at->format('h:i A') }}</span>
                        </td>
                        <td>
                            <span class="badge-action {{ $isUndo ? 'badge-undo' : 'badge-import' }}">
                                <i class="bi {{ $isUndo ? 'bi-arrow-counterclockwise' : 'bi-upload' }}"></i>
                                {{ $log->action }}
                            </span>
                        </td>
                        <td style="font-size:12px;">
                            {{ optional($log->user)->name ?? 'System' }}
                        </td>
                        <td>
                            @if(!$isUndo)
                                <span class="stat-chip chip-created"><i class="bi bi-plus-circle-fill"></i> {{ $created }} created</span>
                                @if($skipped > 0)
                                    <span class="stat-chip chip-skipped"><i class="bi bi-skip-forward-fill"></i> {{ $skipped }} skipped</span>
                                @endif
                            @else
                                <span style="font-size:12px;color:#64748b;">{{ is_string($log->description) ? $log->description : '—' }}</span>
                            @endif
                        </td>
                        <td>
                            @if(!$isUndo && $created > 0)
                                <form method="POST"
                                      action="{{ route('permanent.import.undo', $log->id) }}"
                                      onsubmit="return confirm('This will delete {{ $created }} records that were created by this import. Continue?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-undo">
                                        <i class="bi bi-arrow-counterclockwise"></i> Undo
                                    </button>
                                </form>
                            @else
                                <span style="color:#d1d5db;font-size:11px;">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;font-size:14px;">
                            <i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:10px;"></i>
                            No import history found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($logs->hasPages())
            <div style="padding:12px 18px;">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-dashboard-app>
