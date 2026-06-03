<x-dashboard-app>
<style>
    /* ── Hero ── */
    .preview-hero {
        background: linear-gradient(135deg, #10327c 0%, #1a5276 100%);
        border-radius: 16px; padding: 28px 32px;
        position: relative; overflow: hidden; margin-bottom: 22px;
    }
    .preview-hero::before {
        content: ''; position: absolute; inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,.06) 1px, transparent 1px);
        background-size: 22px 22px;
    }
    .preview-hero-inner { position: relative; z-index: 1; }
    .step-pill {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
        color: #fff; font-size: 11px; font-weight: 700;
        padding: 3px 10px; border-radius: 99px; letter-spacing: .4px; margin-bottom: 10px;
    }

    /* ── Last Import Info Banner ── */
    .last-import-banner {
        display: flex; align-items: center; gap: 12px;
        background: #f0f9ff; border: 1px solid #bae6fd;
        border-radius: 10px; padding: 12px 16px; margin-bottom: 18px;
        font-size: 12px; color: #0369a1;
    }
    .last-import-banner i { font-size: 18px; color: #0284c7; flex-shrink: 0; }

    /* ── Session Timer ── */
    .session-timer {
        display: flex; align-items: center; gap: 8px;
        font-size: 12px; color: #475569;
        background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 8px; padding: 8px 14px; margin-bottom: 18px;
    }
    .session-timer.warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
    .session-timer.danger  { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }
    #session-countdown { font-weight: 700; font-variant-numeric: tabular-nums; }

    /* ── Stats Grid ── */
    .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
    .summary-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
        padding: 16px 18px; display: flex; align-items: center; gap: 12px;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .sc-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px; flex-shrink: 0;
    }
    .sc-icon.blue   { background: #eff6ff; color: #2563eb; }
    .sc-icon.amber  { background: #fffbeb; color: #d97706; }
    .sc-icon.cyan   { background: #ecfeff; color: #0891b2; }
    .sc-icon.green  { background: #f0fdf4; color: #16a34a; }
    .sc-icon.red    { background: #fef2f2; color: #dc2626; }
    .sc-num   { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1; }
    .sc-label { font-size: 11px; color: #64748b; margin-top: 3px; font-weight: 500; }

    /* ── Mode Banners ── */
    .mode-banner {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 14px 18px; border-radius: 10px; margin-bottom: 16px;
    }
    .mode-banner.dryrun  { background: #ede9fe; border: 1px solid #c4b5fd; }
    .mode-banner.replace { background: #fef2f2; border: 1px solid #fca5a5; }
    .mode-banner-title   { font-size: 13px; font-weight: 700; }
    .mode-banner.dryrun  .mode-banner-title { color: #5b21b6; }
    .mode-banner.replace .mode-banner-title { color: #991b1b; }
    .mode-banner-sub { font-size: 12px; opacity: .75; margin-top: 2px; }
    .mode-banner.dryrun  .mode-banner-sub { color: #6d28d9; }
    .mode-banner.replace .mode-banner-sub { color: #b91c1c; }

    /* ── Duplicate Card ── */
    .dupe-card {
        background: #fff; border: 1px solid #fcd34d;
        border-left: 4px solid #f59e0b; border-radius: 12px;
        overflow: hidden; margin-bottom: 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .dupe-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 13px 18px; background: #fffbeb; cursor: pointer; user-select: none;
    }
    .dupe-card-header:hover { background: #fef3c7; }
    .dupe-card-title { display: flex; align-items: center; gap: 10px; }
    .dupe-icon { width: 32px; height: 32px; background: #fde68a; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #b45309; flex-shrink: 0; }
    .dupe-badge { background: #f59e0b; color: #fff; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 99px; }
    .dupe-card-body { padding: 14px 18px; display: none; border-top: 1px solid #fef3c7; }
    .dupe-card-body.open { display: block; }
    .dupe-items-grid { display: flex; flex-wrap: wrap; gap: 5px; max-height: 150px; overflow-y: auto; padding-right: 4px; }
    .dupe-item-tag {
        background: #fffbeb; border: 1px solid #fcd34d; color: #92400e;
        font-size: 11px; font-family: ui-monospace, monospace;
        padding: 2px 8px; border-radius: 6px;
    }

    /* ── Error Card ── */
    .error-card { background: #fff; border: 1px solid #fca5a5; border-left: 4px solid #ef4444; border-radius: 12px; overflow: hidden; margin-bottom: 14px; }
    .error-card-header { display: flex; align-items: center; justify-content: space-between; padding: 13px 18px; background: #fef2f2; cursor: pointer; user-select: none; }
    .error-card-header:hover { background: #fee2e2; }
    .error-icon { width: 32px; height: 32px; background: #fecaca; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0; }
    .error-badge { background: #ef4444; color: #fff; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 99px; }
    .error-card-body { padding: 12px 18px; display: none; border-top: 1px solid #fee2e2; }
    .error-card-body.open { display: block; }

    /* ── Preview Table ── */
    .preview-table-wrap { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04); margin-bottom: 20px; }
    .preview-table-header { display: flex; align-items: center; justify-content: space-between; padding: 13px 18px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; flex-wrap: wrap; gap: 10px; }
    .filter-tabs { display: flex; gap: 6px; }
    .filter-tab {
        font-size: 11px; font-weight: 600; padding: 4px 12px; border-radius: 99px;
        border: 1.5px solid #e2e8f0; background: #fff; color: #64748b;
        cursor: pointer; transition: all .15s; white-space: nowrap;
    }
    .filter-tab.active { background: #10327c; border-color: #10327c; color: #fff; }
    .filter-tab:hover:not(.active) { background: #f1f5f9; }
    .prev-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .prev-table thead th {
        background: #f1f5f9; font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .6px; color: #64748b;
        padding: 10px 14px; text-align: left; border-bottom: 1px solid #e5e7eb; white-space: nowrap;
    }
    .prev-table thead th.center { text-align: center; }
    .prev-table tbody td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .prev-table tbody tr:last-child td { border-bottom: none; }
    .prev-table tbody tr.is-update { background: #fffbeb; }
    .prev-table tbody tr.is-update:hover td { background: #fef3c7; }
    .prev-table tbody tr.is-vacant { background: #f0f9ff; }
    .prev-table tbody tr.is-new:hover td { background: #f0fdf4; }
    .action-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 99px; }
    .action-badge.update { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .action-badge.new    { background: #dcfce7; color: #14532d; border: 1px solid #86efac; }
    .action-badge.vacant { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
    .item-code { font-family: ui-monospace, monospace; color: #475569; font-size: 11px; }

    /* ── Change Comparison Popover ── */
    .change-detail-btn {
        font-size: 10px; font-weight: 600; color: #d97706; background: #fef3c7;
        border: 1px solid #fcd34d; border-radius: 5px; padding: 2px 7px;
        cursor: pointer; margin-left: 6px; white-space: nowrap;
        display: inline-flex; align-items: center; gap: 3px;
    }
    .change-detail-btn:hover { background: #fde68a; }
    .change-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9998; align-items: center; justify-content: center; }
    .change-modal-overlay.open { display: flex; }
    .change-modal { background: #fff; border-radius: 16px; padding: 24px; max-width: 540px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
    .change-modal-title { font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
    .change-modal-sub   { font-size: 12px; color: #64748b; margin-bottom: 16px; }
    .change-row { display: grid; grid-template-columns: 140px 1fr 1fr; gap: 8px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; align-items: start; }
    .change-row:last-child { border-bottom: none; }
    .change-field { font-size: 11px; font-weight: 700; color: #475569; text-transform: capitalize; }
    .change-old { font-size: 12px; color: #dc2626; background: #fef2f2; padding: 3px 8px; border-radius: 5px; text-decoration: line-through; word-break: break-all; }
    .change-new { font-size: 12px; color: #16a34a; background: #f0fdf4; padding: 3px 8px; border-radius: 5px; word-break: break-all; }
    .change-arrow { color: #94a3b8; font-size: 13px; align-self: center; }

    /* ── Execute Card ── */
    .execute-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .execute-card-header { padding: 18px 22px; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 14px; }
    .execute-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .execute-icon.normal  { background: #dbeafe; color: #1d4ed8; }
    .execute-icon.replace { background: #fee2e2; color: #dc2626; }
    .execute-icon.dryrun  { background: #ede9fe; color: #7c3aed; }
    .execute-card-body { padding: 18px 22px; }
    .execute-file-ready { display: flex; align-items: flex-start; gap: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; }
    .est-time-note { display: flex; align-items: center; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #475569; }
    .execute-actions { display: flex; align-items: center; gap: 12px; }
    .btn-back-map {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 11px 18px; border: 1.5px solid #6366f1;
        border-radius: 10px; font-size: 13px; font-weight: 600;
        color: #4f46e5; background: #eef2ff; cursor: pointer;
        text-decoration: none; transition: all .15s;
    }
    .btn-back-map:hover { background: #e0e7ff; color: #3730a3; }
    .btn-cancel {
        padding: 11px 18px; border: 1.5px solid #d1d5db; border-radius: 10px;
        font-size: 13px; font-weight: 600; color: #4b5563; background: #fff;
        cursor: pointer; transition: all .15s; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-cancel:hover { background: #f9fafb; border-color: #9ca3af; }
    .btn-execute {
        flex: 1; padding: 13px 24px; border: none; border-radius: 10px;
        font-size: 14px; font-weight: 700; color: #fff; cursor: pointer;
        transition: all .2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        box-shadow: 0 4px 14px rgba(0,0,0,.15);
    }
    .btn-execute.normal  { background: linear-gradient(135deg, #10327c, #1a4a9e); }
    .btn-execute.normal:hover  { background: linear-gradient(135deg, #0c2461, #10327c); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,50,124,.35); }
    .btn-execute.replace { background: linear-gradient(135deg, #dc2626, #b91c1c); }
    .btn-execute.replace:hover { transform: translateY(-1px); }
    .btn-execute.dryrun  { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
    .btn-execute.dryrun:hover  { transform: translateY(-1px); }
    .btn-execute:disabled { opacity: .65; cursor: not-allowed; transform: none !important; }
</style>

<div style="max-width: 980px; margin: 0 auto;">

    {{-- ▌ HERO ▌ --}}
    <div class="preview-hero">
        <div class="preview-hero-inner">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px;">
                <div>
                    <div class="step-pill"><i class="bi bi-eye"></i> Step 3 of 3 — Review Before Importing</div>
                    <h1 style="color:#fff; font-size:22px; font-weight:800; margin:0 0 6px;">Review Before Importing</h1>
                    <p style="color:rgba(255,255,255,.65); font-size:13px; margin:0;">
                        Showing first 20 valid rows of your file
                        <strong style="color:#bfdbfe;">{{ $originalFileName ?? '' }}</strong>
                        — full import will process
                        <strong style="color:#bfdbfe;">{{ number_format($totalRows) }} records</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ▌ SESSION EXPIRY TIMER ▌ --}}
    <div class="session-timer" id="session-timer-bar">
        <i class="bi bi-clock" id="session-timer-icon"></i>
        <span>Your upload session expires in&nbsp;<strong id="session-countdown">30:00</strong>.
            After expiry you will need to re-upload the file.
        </span>
    </div>

    {{-- ▌ LAST IMPORT BANNER ▌ --}}
    @if(!empty($lastImportInfo))
    <div class="last-import-banner">
        <i class="bi bi-info-circle-fill"></i>
        <div>
            <strong>Previous import:</strong>
            {{ $lastImportInfo['date'] }} by <strong>{{ $lastImportInfo['by'] }}</strong>
            &mdash;
            {{ number_format($lastImportInfo['created']) }} created, {{ number_format($lastImportInfo['updated']) }} updated
            @if($lastImportInfo['file'])
                <span style="color:#0369a1; margin-left:6px;">({{ $lastImportInfo['file'] }})</span>
            @endif
        </div>
    </div>
    @endif

    {{-- ▌ SUMMARY STAT CARDS ▌ --}}
    @php
        $dupeTotal  = count($duplicates ?? []);
        $errorTotal = count($allErrors ?? []);
        $newCount   = max(0, $totalRows - $dupeTotal - $errorTotal);
        $vacantCount = $vacantCount ?? 0;
    @endphp
    <div class="summary-grid">
        <div class="summary-card">
            <div class="sc-icon blue"><i class="bi bi-file-earmark-spreadsheet"></i></div>
            <div>
                <div class="sc-num">{{ number_format($totalRows) }}</div>
                <div class="sc-label">Total Rows</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="sc-icon amber"><i class="bi bi-arrow-repeat"></i></div>
            <div>
                <div class="sc-num">{{ number_format($dupeTotal) }}</div>
                <div class="sc-label">Will Be Updated</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="sc-icon cyan"><i class="bi bi-dash-circle"></i></div>
            <div>
                <div class="sc-num">{{ number_format($vacantCount) }}</div>
                <div class="sc-label">Vacant Positions</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="sc-icon {{ $errorTotal > 0 ? 'red' : 'green' }}">
                <i class="bi bi-{{ $errorTotal > 0 ? 'exclamation-circle' : 'check-circle' }}"></i>
            </div>
            <div>
                <div class="sc-num">{{ number_format($errorTotal) }}</div>
                <div class="sc-label">{{ $errorTotal > 0 ? 'Rows w/ Errors' : 'Errors' }}</div>
            </div>
        </div>
    </div>

    {{-- ▌ MODE BANNERS ▌ --}}
    @if($dryRun)
    <div class="mode-banner dryrun">
        <div style="font-size:20px; flex-shrink:0;">🧪</div>
        <div>
            <div class="mode-banner-title">Dry Run Mode — Simulation Only</div>
            <div class="mode-banner-sub">No data will be changed. This is a completely safe preview of what would happen.</div>
        </div>
    </div>
    @endif
    @if($replaceAll)
    <div class="mode-banner replace">
        <div style="font-size:20px; flex-shrink:0;">⚠</div>
        <div>
            <div class="mode-banner-title">Replace All Mode is ON</div>
            <div class="mode-banner-sub">All existing plantilla records will be <strong>permanently deleted</strong> before this import runs.</div>
        </div>
    </div>
    @endif

    {{-- ▌ DUPLICATE WARNING — Collapsible ▌ --}}
    @if($dupeTotal > 0)
    <div class="dupe-card">
        <div class="dupe-card-header" onclick="toggleCard('dupe')">
            <div class="dupe-card-title">
                <div class="dupe-icon"><i class="bi bi-arrow-repeat"></i></div>
                <div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#92400e;">
                        Records that already exist — will be updated
                        <span class="dupe-badge">{{ number_format($dupeTotal) }}</span>
                    </div>
                    <div style="font-size:11px;color:#b45309;margin-top:2px;font-weight:400;">
                        Blank cells in your file will <u>not</u> overwrite existing values. Click to view item codes.
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:#b45309;background:#fde68a;border:1px solid #fcd34d;padding:4px 10px;border-radius:7px;">
                <i class="bi bi-chevron-down" id="dupe-chevron" style="transition:transform .2s;"></i>
                <span id="dupe-label">Show Codes</span>
            </div>
        </div>
        <div class="dupe-card-body" id="dupe-body">
            <p style="font-size:11px;color:#78716c;margin:0 0 10px;">
                Showing {{ min($dupeTotal, 120) }} of {{ number_format($dupeTotal) }} item codes that will be updated:
            </p>
            <div class="dupe-items-grid">
                @foreach(array_slice($duplicates, 0, 120) as $dup)
                    <span class="dupe-item-tag">{{ $dup }}</span>
                @endforeach
                @if($dupeTotal > 120)
                    <span style="font-size:11px;color:#b45309;align-self:center;font-style:italic;">
                        … and {{ number_format($dupeTotal - 120) }} more
                    </span>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ▌ ROW ERRORS — Collapsible + Pre-Import Download ▌ --}}
    @if($errorTotal > 0)
    <div class="error-card">
        <div class="error-card-header" onclick="toggleCard('error')">
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="error-icon"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#991b1b;">
                        Rows with errors — will be skipped
                        <span class="error-badge">{{ $errorTotal }}</span>
                    </div>
                    <div style="font-size:11px;color:#b91c1c;margin-top:2px;font-weight:400;">
                        Fix these in your source file before importing. You can download them now.
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                {{-- Download errors BEFORE import (new!) --}}
                @if(!empty($hasPreviewErrors) && $hasPreviewErrors && !empty($tmpKey))
                <a href="{{ route('imports.preview-errors', ['tmp_key' => $tmpKey]) }}"
                   onclick="event.stopPropagation()"
                   style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;color:#dc2626;background:#fff;border:1.5px solid #fca5a5;padding:5px 11px;border-radius:7px;text-decoration:none;white-space:nowrap;">
                    <i class="bi bi-download"></i> Download Errors (.xlsx)
                </a>
                @endif
                <div style="display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:#dc2626;background:#fecaca;border:1px solid #fca5a5;padding:4px 10px;border-radius:7px;">
                    <i class="bi bi-chevron-down" id="error-chevron" style="transition:transform .2s;"></i>
                    <span id="error-label">Show</span>
                </div>
            </div>
        </div>
        <div class="error-card-body" id="error-body">
            <ul style="list-style:disc;padding-left:18px;margin:0;color:#dc2626;font-size:12px;max-height:160px;overflow-y:auto;">
                @foreach($allErrors as $err)
                    <li style="margin-bottom:3px;">{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- ▌ PREVIEW TABLE ▌ --}}
    <div class="preview-table-wrap">
        <div class="preview-table-header">
            <div style="font-size:13px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:8px;">
                <i class="bi bi-table" style="color:#10327c;"></i> Preview — First 20 Valid Rows
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                {{-- Action filter tabs --}}
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterRows('all', this)">All</button>
                    <button class="filter-tab" onclick="filterRows('update', this)">
                        <i class="bi bi-arrow-repeat" style="color:#d97706;"></i> Update
                    </button>
                    <button class="filter-tab" onclick="filterRows('new', this)">
                        <i class="bi bi-plus-circle" style="color:#16a34a;"></i> New
                    </button>
                    <button class="filter-tab" onclick="filterRows('vacant', this)">
                        <i class="bi bi-dash-circle" style="color:#0891b2;"></i> Vacant
                    </button>
                </div>
                {{-- Legend --}}
                <div style="display:flex;align-items:center;gap:10px;font-size:11px;color:#64748b;">
                    <span style="display:inline-flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#fef3c7;border:1px solid #fcd34d;border-radius:2px;display:inline-block;"></span>Update</span>
                    <span style="display:inline-flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#dcfce7;border:1px solid #86efac;border-radius:2px;display:inline-block;"></span>New</span>
                    <span style="display:inline-flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:2px;display:inline-block;"></span>Vacant</span>
                </div>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="prev-table" id="preview-table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Item Code</th>
                        <th>Position Title</th>
                        <th>Employee</th>
                        <th class="center" style="width:48px;">SG</th>
                        <th class="center" style="width:52px;">Step</th>
                        <th>Status</th>
                        <th class="center" style="width:90px;">Action</th>
                    </tr>
                </thead>
                <tbody id="preview-tbody">
                    @foreach($mapped as $row)
                    @php
                        $isDupe   = isset($duplicates) && in_array($row['item'], $duplicates);
                        $isVacant = $row['is_vacant'] ?? false;
                        $rowType  = $isVacant ? 'vacant' : ($isDupe ? 'update' : 'new');
                        $rowClass = $isVacant ? 'is-vacant' : ($isDupe ? 'is-update' : 'is-new');
                        $hasChanges = isset($changeComparisons[$row['item']]);
                    @endphp
                    <tr class="{{ $rowClass }}" data-rowtype="{{ $rowType }}">
                        <td style="color:#94a3b8;font-size:11px;">{{ $row['row_number'] }}</td>
                        <td>
                            <span class="item-code">{{ $row['item'] ?: '—' }}</span>
                            @if($isDupe && $hasChanges)
                                <button class="change-detail-btn" onclick="showChanges('{{ $row['item'] }}')">
                                    <i class="bi bi-eye"></i> {{ count($changeComparisons[$row['item']]) }} change{{ count($changeComparisons[$row['item']]) > 1 ? 's' : '' }}
                                </button>
                            @elseif($isDupe)
                                <i class="bi bi-arrow-repeat ms-1" style="color:#d97706;font-size:10px;" title="Will be updated"></i>
                            @endif
                        </td>
                        <td style="color:#1e293b;font-weight:500;">{{ $row['position_title'] ?: '—' }}</td>
                        <td>
                            @if($isVacant)
                                <span style="color:#0284c7;font-size:11px;font-weight:600;"><i class="bi bi-dash-circle me-1"></i>VACANT</span>
                            @else
                                <span style="font-weight:700;color:#0f172a;">{{ strtoupper($row['last_name'] ?? '') }}</span>
                                <span style="color:#475569;">, {{ $row['first_name'] ?? '' }}</span>
                            @endif
                        </td>
                        <td style="text-align:center;font-weight:700;color:#374151;">{{ $row['salary_grade'] ?: '—' }}</td>
                        <td style="text-align:center;color:#374151;">{{ $row['step'] ?: '—' }}</td>
                        <td style="color:#64748b;font-size:11px;">{{ $row['employment_status'] ?: 'N/A' }}</td>
                        <td style="text-align:center;">
                            @if($isVacant)
                                <span class="action-badge vacant"><i class="bi bi-dash-circle"></i> VACANT</span>
                            @elseif($isDupe)
                                <span class="action-badge update"><i class="bi bi-arrow-repeat"></i> UPDATE</span>
                            @else
                                <span class="action-badge new"><i class="bi bi-plus-circle"></i> NEW</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:10px 18px;background:#f8fafc;border-top:1px solid #f1f5f9;font-size:11px;color:#94a3b8;display:flex;justify-content:space-between;align-items:center;">
            <span>Preview shows first 20 rows. Full import will process all <strong>{{ number_format($totalRows) }}</strong> records.</span>
            <span id="filter-count" style="font-weight:600;color:#475569;"></span>
        </div>
    </div>

    {{-- ▌ EXECUTE CARD ▌ --}}
    <div class="execute-card">
        <div class="execute-card-header">
            @if($dryRun)
                <div class="execute-icon dryrun"><i class="bi bi-beaker"></i></div>
                <div>
                    <div style="font-size:15px;font-weight:800;color:#5b21b6;">Run Dry Run Simulation</div>
                    <div style="font-size:12px;color:#7c3aed;margin-top:2px;">No data will be changed. This is completely safe.</div>
                </div>
            @elseif($replaceAll)
                <div class="execute-icon replace"><i class="bi bi-trash3"></i></div>
                <div>
                    <div style="font-size:15px;font-weight:800;color:#991b1b;">Delete All & Import Fresh</div>
                    <div style="font-size:12px;color:#b91c1c;margin-top:2px;">All existing records will be deleted first. Irreversible.</div>
                </div>
            @else
                <div class="execute-icon normal"><i class="bi bi-cloud-upload"></i></div>
                <div>
                    <div style="font-size:15px;font-weight:800;color:#10327c;">Execute Full Import</div>
                    <div style="font-size:12px;color:#1d4ed8;margin-top:2px;">
                        Will update <strong>{{ number_format($dupeTotal) }}</strong> existing records and create new ones from
                        <strong>{{ number_format($totalRows) }}</strong> rows.
                    </div>
                </div>
            @endif
        </div>

        <div class="execute-card-body">
            <form method="POST" action="{{ route('imports.execute') }}" enctype="multipart/form-data" id="import-form">
                @csrf
                <input type="hidden" name="replace_all"       value="{{ $replaceAll ? '1' : '0' }}">
                <input type="hidden" name="dry_run"           value="{{ $dryRun     ? '1' : '0' }}">
                <input type="hidden" name="original_filename" value="{{ $originalFileName ?? '' }}">

                @if(isset($mappingJson) && $mappingJson)
                    <input type="hidden" name="tmp_key"      value="{{ $tmpKey }}">
                    <input type="hidden" name="mapping_json" value="{{ $mappingJson }}">
                    <div class="execute-file-ready">
                        <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:17px;flex-shrink:0;margin-top:1px;"></i>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#14532d;">File ready — no re-upload needed</div>
                            <div style="font-size:11px;color:#16a34a;margin-top:1px;">
                                Your column mapping will be applied automatically when you execute.
                            </div>
                        </div>
                    </div>
                @else
                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">
                            <i class="bi bi-upload me-1"></i>Re-upload your file to execute:
                        </label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            style="width:100%;border:1.5px solid #d1d5db;border-radius:9px;padding:10px 14px;font-size:13px;">
                    </div>
                @endif

                {{-- Estimated time ─ only show for real imports --}}
                @if(!$dryRun)
                <div class="est-time-note">
                    <i class="bi bi-hourglass-split" style="color:#64748b;"></i>
                    <span>Estimated processing time for {{ number_format($totalRows) }} rows: <strong>{{ $estTime ?? '~5–15 seconds' }}</strong></span>
                </div>
                @endif

                <div class="execute-actions">
                    {{-- Back to Mapping — button links to standalone form below via HTML5 form attribute --}}
                    @if(!empty($tmpKey) && isset($mappingJson) && $mappingJson)
                    <button type="submit" form="back-to-map-form" class="btn-back-map">
                        <i class="bi bi-chevron-left"></i> Back to Mapping
                    </button>
                    @endif

                    <a href="{{ route('imports.index') }}" class="btn-cancel">
                        <i class="bi bi-x"></i> Cancel
                    </a>

                    <button type="button" id="execute-btn"
                        class="btn-execute {{ $dryRun ? 'dryrun' : ($replaceAll ? 'replace' : 'normal') }}"
                        onclick="confirmImport({{ $replaceAll ? 'true' : 'false' }}, {{ $dryRun ? 'true' : 'false' }}, {{ $totalRows }}, {{ $dupeTotal }})">
                        @if($dryRun)
                            <i class="bi bi-beaker"></i> Run Dry Run &nbsp;·&nbsp; {{ number_format($totalRows) }} rows
                        @elseif($replaceAll)
                            <i class="bi bi-trash3"></i> Delete All & Import &nbsp;·&nbsp; {{ number_format($totalRows) }} rows
                        @else
                            <i class="bi bi-cloud-upload"></i> Execute Import &nbsp;·&nbsp; {{ number_format($totalRows) }} rows
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div><!-- /max-w -->

{{-- ▌ BACK TO MAPPING FORM — standalone outside the import form ▌ --}}
{{-- Linked to the button above via HTML5 form="back-to-map-form" attribute --}}
@if(!empty($tmpKey) && isset($mappingJson) && $mappingJson)
<form method="POST" action="{{ route('imports.back-to-map') }}" id="back-to-map-form" style="display:none;">
    @csrf
    <input type="hidden" name="tmp_key"     value="{{ $tmpKey }}">
    <input type="hidden" name="replace_all" value="{{ $replaceAll ? '1' : '0' }}">
    <input type="hidden" name="dry_run"     value="{{ $dryRun ? '1' : '0' }}">  
</form>
@endif

{{-- ▌ CHANGE COMPARISON MODAL ▌ --}}
<div class="change-modal-overlay" id="change-modal-overlay" onclick="closeChanges(event)">
    <div class="change-modal" onclick="event.stopPropagation()">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
            <div class="change-modal-title" id="change-modal-title">What Will Change</div>
            <button onclick="document.getElementById('change-modal-overlay').classList.remove('open')"
                style="background:none;border:none;font-size:18px;color:#94a3b8;cursor:pointer;padding:2px 6px;border-radius:5px;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="change-modal-sub" id="change-modal-sub"></div>
        <div style="display:grid;grid-template-columns:140px 1fr 16px 1fr;gap:6px;padding:8px 0 4px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;border-bottom:2px solid #e5e7eb;margin-bottom:6px;">
            <span>Field</span><span>Current Value</span><span></span><span>New Value</span>
        </div>
        <div id="change-modal-body"></div>
        <div style="margin-top:14px;font-size:11px;color:#94a3b8;">
            <i class="bi bi-info-circle me-1"></i>Only fields with differences are shown. Fields left blank in the import file will NOT be changed.
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    /* ══════════════════════════════════════════════════
       Change comparison data from PHP
    ══════════════════════════════════════════════════ */
    const CHANGE_COMPARISONS = @json($changeComparisons ?? []);

    function showChanges(itemCode) {
        const changes = CHANGE_COMPARISONS[itemCode];
        if (!changes || !changes.length) return;

        document.getElementById('change-modal-title').textContent = 'What Will Change — Item ' + itemCode;
        document.getElementById('change-modal-sub').textContent =
            changes.length + ' field(s) differ between the file and the current database record.';

        const fieldLabels = {
            position_title: 'Position Title', salary_grade: 'Salary Grade', step: 'Step',
            employment_status: 'Employment Status', first_name: 'First Name',
            last_name: 'Last Name', organizational_unit: 'Org. Unit'
        };

        let html = '';
        changes.forEach(ch => {
            html += `<div style="display:grid;grid-template-columns:140px 1fr 16px 1fr;gap:6px;padding:8px 0;border-bottom:1px solid #f1f5f9;align-items:start;">
                <span style="font-size:11px;font-weight:700;color:#475569;text-transform:capitalize;align-self:center;">${fieldLabels[ch.field] || ch.field}</span>
                <span style="font-size:12px;color:#dc2626;background:#fef2f2;padding:4px 8px;border-radius:5px;word-break:break-all;text-decoration:line-through;">${ch.old}</span>
                <i class="bi bi-arrow-right" style="color:#94a3b8;font-size:12px;align-self:center;text-align:center;"></i>
                <span style="font-size:12px;color:#16a34a;background:#f0fdf4;padding:4px 8px;border-radius:5px;word-break:break-all;font-weight:600;">${ch.new}</span>
            </div>`;
        });
        document.getElementById('change-modal-body').innerHTML = html;
        document.getElementById('change-modal-overlay').classList.add('open');
    }
    function closeChanges(e) {
        if (e.target === document.getElementById('change-modal-overlay')) {
            document.getElementById('change-modal-overlay').classList.remove('open');
        }
    }

    /* ══════════════════════════════════════════════════
       Collapsible cards
    ══════════════════════════════════════════════════ */
    function toggleCard(type) {
        const body    = document.getElementById(type + '-body');
        const chevron = document.getElementById(type + '-chevron');
        const label   = document.getElementById(type + '-label');
        const isOpen  = body.classList.contains('open');
        body.classList.toggle('open');
        chevron.style.transform = isOpen ? '' : 'rotate(180deg)';
        if (type === 'dupe') label.textContent = isOpen ? 'Show Codes' : 'Hide Codes';
        if (type === 'error') label.textContent = isOpen ? 'Show' : 'Hide';
    }

    /* ══════════════════════════════════════════════════
       Preview table filter
    ══════════════════════════════════════════════════ */
    function filterRows(type, btn) {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');

        const rows = document.querySelectorAll('#preview-tbody tr');
        let visible = 0;
        rows.forEach(row => {
            const rt = row.dataset.rowtype;
            const show = type === 'all' || rt === type;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const countEl = document.getElementById('filter-count');
        countEl.textContent = type === 'all' ? '' : `Showing ${visible} ${type} row(s)`;
    }

    /* ══════════════════════════════════════════════════
       Session expiry countdown (30 minutes)
    ══════════════════════════════════════════════════ */
    (function startTimer() {
        let remaining = 30 * 60; // 30 minutes in seconds
        const countdownEl = document.getElementById('session-countdown');
        const timerBar    = document.getElementById('session-timer-bar');
        const timerIcon   = document.getElementById('session-timer-icon');

        function tick() {
            remaining--;
            const m = Math.floor(remaining / 60);
            const s = remaining % 60;
            countdownEl.textContent = m + ':' + String(s).padStart(2, '0');

            if (remaining <= 0) {
                timerBar.className = 'session-timer danger';
                timerIcon.className = 'bi bi-exclamation-circle';
                countdownEl.textContent = 'EXPIRED';
                clearInterval(timer);
                return;
            }
            if (remaining <= 300) { // 5 min left
                timerBar.className = 'session-timer danger';
                timerIcon.className = 'bi bi-exclamation-circle';
            } else if (remaining <= 600) { // 10 min left
                timerBar.className = 'session-timer warning';
                timerIcon.className = 'bi bi-clock';
            }
        }
        const timer = setInterval(tick, 1000);
    })();

    /* ══════════════════════════════════════════════════
       Execute confirmation dialogs
    ══════════════════════════════════════════════════ */
    function confirmImport(replaceAll, dryRun, count, dupeCount) {
        if (dryRun) {
            Swal.fire({
                title: '🧪 Run Dry Run?',
                html: `<p style="font-size:14px;color:#4b5563;line-height:1.6;">
                    Simulate a full import of <strong>${count.toLocaleString()} rows</strong>.<br>
                    <span style="color:#7c3aed;font-weight:700;">No data will be saved to the database.</span></p>`,
                icon: 'info', iconColor: '#7c3aed',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed', cancelButtonColor: '#64748b',
                confirmButtonText: '🧪 Run Simulation', cancelButtonText: 'Cancel'
            }).then(r => { if (r.isConfirmed) submitImport(); });
            return;
        }

        if (replaceAll) {
            Swal.fire({
                title: '⚠ Delete All & Import?',
                html: `<p style="font-size:14px;color:#6b7280;line-height:1.6;">
                    You are about to <strong style="color:#dc2626;">permanently delete ALL existing Plantilla records</strong>
                    and then import <strong>${count.toLocaleString()} rows</strong> fresh.<br><br>
                    This action <strong>cannot be undone!</strong></p>`,
                icon: 'warning', iconColor: '#dc2626',
                showCancelButton: true,
                confirmButtonColor: '#dc2626', cancelButtonColor: '#374151',
                confirmButtonText: '🗑 Yes, Delete All & Import', cancelButtonText: 'Cancel'
            }).then(r => { if (r.isConfirmed) submitImport(); });
            return;
        }

        Swal.fire({
            title: 'Confirm Import?',
            html: `<div style="text-align:left;font-size:13px;color:#4b5563;line-height:1.7;">
                <p style="margin:0 0 12px;">The import will process <strong>${count.toLocaleString()} rows</strong>:</p>
                <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px;margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                    <i style="color:#d97706;font-size:16px;" class="bi bi-arrow-repeat"></i>
                    <span><strong style="color:#92400e;">${dupeCount.toLocaleString()} records</strong> will be <strong>updated</strong></span>
                </div>
                <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:8px;">
                    <i style="color:#16a34a;font-size:16px;" class="bi bi-plus-circle"></i>
                    <span>Unmatched rows will be <strong>created</strong> as new records</span>
                </div></div>`,
            icon: 'question', iconColor: '#10327c',
            showCancelButton: true,
            confirmButtonColor: '#10327c', cancelButtonColor: '#64748b',
            confirmButtonText: '✔ Yes, Execute Import', cancelButtonText: 'Cancel'
        }).then(r => { if (r.isConfirmed) submitImport(); });
    }

    function submitImport() {
        const btn = document.getElementById('execute-btn');
        btn.disabled = true;
        btn.innerHTML = `<span style="display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;"></span>&nbsp;Processing — please wait…`;
        document.getElementById('import-form').submit();
    }

    const _s = document.createElement('style');
    _s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
    document.head.appendChild(_s);
</script>
</x-dashboard-app>
