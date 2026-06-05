<x-dashboard-app>
    <style>
        /* Page-specific styles for Inventory of Personnel */
        .inv-hero {
            background: linear-gradient(135deg, #052c65 0%, #1e3a8a 55%, #1e40af 100%);
            border-radius: 14px;
            padding: 28px 32px;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .inv-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .08) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .inv-hero-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .inv-hero h1 {
            color: #fff;
            font-size: 26px;
            font-weight: 800;
            margin: 0;
            line-height: 1.2;
        }

        .inv-hero p {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin: 6px 0 0;
        }

        .inv-hero-stat {
            text-align: center;
        }

        .inv-hero-stat .num {
            font-size: 48px;
            font-weight: 900;
            color: #fff;
            line-height: 1;
        }

        .inv-hero-stat .lbl {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255, 255, 255, .5);
            margin-top: 4px;
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .3);
            color: #fff;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background .2s;
        }

        .hero-btn:hover {
            background: rgba(255, 255, 255, .28);
            color: #fff;
        }

        /* Card wrapper */
        .card-panel {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
        }

        .card-panel-header {
            padding: 14px 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .card-panel-body {
            padding: 20px;
        }

        .section-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #9ca3af;
            margin-bottom: 14px;
            display: block;
        }

        /* Status cards */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        @media (max-width: 1100px) {
            .status-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 640px) {
            .status-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-card {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            background: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 16px;
            gap: 16px;
            position: relative;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            border-color: #d1d5db;
        }

        /* Selected / active */
        .stat-card.active {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
            border-width: 2px;
            padding: 15px; /* Adjust for 2px border */
            z-index: 2;
        }

        .stat-card-check {
            position: absolute;
            top: 10px;
            right: 10px;
            background: currentColor;
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-card-content {
            flex: 1;
            min-width: 0;
        }

        .stat-card-num {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-card-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Active filter banner */
        .active-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, #1e3a5f, #1a5276);
            color: #fff;
            border-radius: 10px;
            padding: 13px 20px;
            margin-bottom: 18px;
            box-shadow: 0 4px 14px rgba(30, 58, 95, .22);
            animation: slideDown .25s ease both;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .active-filter-label {
            font-size: 13px;
            font-weight: 600;
        }

        .active-filter-clear {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .3);
            color: #fff;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s;
        }

        .active-filter-clear:hover {
            background: rgba(255, 255, 255, .28);
            color: #fff;
        }

        /* Two-column analytics section */
        .analytics-row {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        @media (max-width: 860px) {
            .analytics-row {
                grid-template-columns: 1fr;
            }
        }

        /* Age bars */
        .age-bar-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .age-bar-label {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            width: 38px;
            text-align: right;
            flex-shrink: 0;
        }

        .age-bar-track {
            flex: 1;
            height: 10px;
            border-radius: 99px;
            background: #f3f4f6;
            overflow: hidden;
        }

        .age-bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .5s;
        }

        .age-bar-count {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            width: 28px;
            text-align: right;
            flex-shrink: 0;
        }

        .age-retire-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 14px;
            padding: 10px 12px;
            background: #fef2f2;
            border-radius: 8px;
            border: 1px solid #fecaca;
        }

        /* Gender */
        .gender-card:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,0.08); }

/* Vacant rows */
.both-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
@media (max-width: 640px) { .both-col { grid-template-columns: 1fr; } }
.vacant-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 16px; border-bottom: 1px solid #f3f4f6; }
.vacant-row:last-child { border-bottom: 0; }
.vacant-row:hover { background: #f9fafb; }
.vacant-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; border-radius: 50%; font-size: 11px; font-weight: 800;
    color: #fff; flex-shrink: 0;
}
.vacant-scrollable { max-height: 200px; overflow-y: auto; }

/* Retirement alert card */
.retire-alert { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
.retire-alert-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid #fecaca; cursor: pointer; transition: background .15s; }
.retire-alert-header:hover { background: #fee2e2; }
.retire-alert-left { display: flex; align-items: center; gap: 14px; }
.retire-pulse { width: 38px; height: 38px; border-radius: 50%; background: #ef4444;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.4);} 50%{box-shadow:0 0 0 8px rgba(239,68,68,0);} }

/* Filter bar */
.filter-bar {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
    padding: 16px 20px; margin-bottom: 20px;
}
.filter-bar form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
.filter-field label { display: block; font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; }
.filter-field input, .filter-field select {
    border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 8px 12px; font-size: 13px; outline: none;
    transition: border .15s;
}
.filter-field input:focus, .filter-field select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
.filter-field-lg { min-width: 200px; flex: 1; }
.filter-field-sm { min-width: 160px; }

/* Office accordion (Redesigned) */
.office-card {
    background: #ffffff; border: 1px solid #eef2f6; border-radius: 12px;
    margin-bottom: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -2px rgba(0,0,0,0.02);
    overflow: hidden;
}
.office-btn {
    width: 100%; border: none; background: #ffffff; cursor: pointer;
    display: flex; align-items: center; justify-content: space-between; gap: 24px;
    padding: 24px 32px; text-align: left; transition: background 0.2s;
}
.office-btn:hover { background: #f8fafc; }

@media(max-width: 1024px) {
    .office-btn { flex-direction: column; align-items: stretch; gap: 16px; padding: 20px; }
}

.off-left { display: flex; align-items: center; gap: 16px; flex: 1.5; min-width: 250px; }
.off-icon-box {
    width: 54px; height: 54px; border-radius: 14px;
    background: #f0f5ff; color: #2563eb;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; flex-shrink: 0;
}
.off-name { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px; line-height: 1.2; word-break: break-word; }
.off-sub { font-size: 13px; color: #64748b; }

.off-middle {
    display: flex; align-items: center; gap: 32px; flex: 3; justify-content: center; flex-wrap: wrap;
}
@media(max-width: 1024px) { .off-middle { justify-content: flex-start; } }

.off-stat-col { display: flex; flex-direction: column; align-items: center; gap: 10px; }
.off-stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
.off-stat-num { font-size: 18px; font-weight: 800; color: #0f172a; line-height: 1; }
.off-pill { border: 1px solid transparent; font-size: 11px; font-weight: 600; padding: 6px 14px; border-radius: 99px; white-space: nowrap; }

.off-right { display: flex; align-items: center; justify-content: flex-end; flex: 1; min-width: 120px; }
.off-view-btn { color: #2563eb; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
.chevron { font-size: 14px; line-height: 1; transition: transform .2s; }

/* Category sub-header */
.cat-header { display: flex; align-items: center; gap: 8px; padding: 8px 20px; border-top: 1px solid #f3f4f6; border-left: 4px solid; }
.cat-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .8px; }
.cat-count { font-size: 11px; color: #9ca3af; }

/* ── Premium Personnel Table (Redesigned) ─────────────────────────────────────────── */
.personnel-table {
    width: 100%; border-collapse: collapse; border-spacing: 0;
}
.personnel-table thead tr {
    /* Transparent or clean white header based on screenshot */
    border-bottom: 1px solid #eef2f6;
}
.personnel-table th {
    text-align: left; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.8px; color: #64748b;
    padding: 16px 20px; white-space: nowrap;
}
.personnel-table th.tc { text-align: center; }
.personnel-table tbody tr {
    transition: background 0.15s;
    border-bottom: 1px solid #f1f5f9;
}
.personnel-table tbody tr:hover { background: #f8fafc; }
.personnel-table td {
    padding: 16px 20px; vertical-align: middle;
    font-size: 14px; color: #334155;
}
.personnel-table td.tc { text-align: center; }
.personnel-table tbody tr:last-child { border-bottom: 0; }

/* Item code */
.item-code {
    font-family: ui-monospace, monospace; font-size: 13px; font-weight: 500;
    color: #64748b;
}

/* Position Title Column */
.pos-title-text { font-weight: 700; color: #0f172a; font-size: 14px; margin-bottom: 2px; }
.pos-dept-text { font-size: 12px; color: #64748b; }

/* Employee name & Avatar */
.emp-cell-wrapper { display: flex; align-items: center; gap: 12px; }
.emp-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; flex-shrink: 0;
}
/* Assign colors dynamically based on initials in blade later, using a default light blue here */
.emp-avatar-default { background: #eff6ff; color: #2563eb; }
.emp-avatar-vacant { background: #f1f5f9; color: #94a3b8; }

.emp-name-text { font-weight: 500; color: #0f172a; font-size: 14px; }
.emp-vacant-text { color: #94a3b8; font-size: 14px; font-style: italic; }

/* Numeric Columns */
.num-text { font-size: 14px; font-weight: 600; color: #0f172a; }

/* Status Pill */
.status-pill {
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 99px; border: 1px solid transparent;
}
.status-permanent { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
.status-contractual { background: #fff7ed; color: #ea580c; border-color: #fed7aa; }
.status-probationary { background: #fefce8; color: #ca8a04; border-color: #fef08a; }
.status-vacant { background: #f1f5f9; color: #64748b; border-color: #e2e8f0; }
.status-default { background: #f3f4f6; color: #4b5563; border-color: #d1d5db; }

/* Action buttons */
.table-actions { display: flex; gap: 4px; justify-content: center; }
.btn-icon-plain {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 8px; font-size: 16px;
    color: #94a3b8; background: transparent; border: none; cursor: pointer;
    transition: all 0.15s; text-decoration: none;
}
.btn-icon-plain:hover { color: #0f172a; background: #f1f5f9; }

/* Empty state */
.empty-state { text-align: center; padding: 64px 20px; }
.empty-icon {
    width: 60px; height: 60px;
    background: linear-gradient(135deg,#f1f5f9,#e2e8f0);
    border-radius: 50%; display: inline-flex; align-items: center;
    justify-content: center; font-size: 26px; color: #94a3b8; margin-bottom: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,.06);
}


</style>

{{-- ▌▌ HERO ▌▌ --}}
<div class="inv-hero">
    <div class="inv-hero-inner">
        <div>
            <h1><i class="bi bi-people-fill me-2"></i>Inventory of Personnel</h1>
            <p>Real-time snapshot of all positions across every office</p>
        </div>
        <div style="display:flex;align-items:center;gap:28px;">
            <a href="{{ route('plantilla.pwd-report') }}" class="hero-btn">
                <i class="bi bi-person-wheelchair"></i> PWD Report
                <span style="background: #fef08a; color: #a16207; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 99px; margin-left: 4px;">{{ $pwdCount ?? 0 }}</span>
            </a>

            <a href="{{ route('plantilla.ip-report') }}" class="hero-btn">
                <i class="bi bi-people-fill"></i> IP Report
                <span style="background: #fed7aa; color: #c2410c; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 99px; margin-left: 4px;">{{ $ipCount ?? 0 }}</span>
            </a>

            <a href="{{ route('plantilla.position-report') }}" class="hero-btn">
                <i class="bi bi-file-text-fill"></i> Position Report
            </a>

            <a href="{{ route('plantilla.reports') }}" class="hero-btn">
                <i class="bi bi-file-earmark-bar-graph"></i> Reports
            </a>

            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('plantilla.quick-add.form') }}" class="hero-btn" style="background:rgba(255, 255, 255, .18); color:#fff; font-weight:600; border: 1px solid rgba(255, 255, 255, .3);" title="Quickly create an Office and Position Title">
                    <i class="bi bi-lightning-fill" style="color: #fbbf24;"></i> Quick Add Position
                </a>
                <a href="{{ route('plantilla.create') }}" class="hero-btn" style="background:#fff; color:#1e3a8a; font-weight:800; border:none;">
                    <i class="bi bi-plus-lg"></i> Add Record
                </a>
            @endif
        </div>
    </div>
</div>

{{-- ▌▌ STATUS CARDS ▌▌ --}}
<span class="section-label"><i class="bi bi-person-check me-1"></i> Appointment Status</span>
@php
    $statCards = [
        ['cat' => 'Elected',         'label' => 'Elected',         'icon' => 'bi-star-fill',       'bg' => '#f3e8ff', 'text' => '#7e22ce', 'border' => '#c084fc'],
        ['cat' => 'Co-Terminous',    'label' => 'Co-Terminous',    'icon' => 'bi-link-45deg',      'bg' => '#fce7f3', 'text' => '#be185d', 'border' => '#f472b6'],
        ['cat' => 'Permanent',       'label' => 'Permanent',       'icon' => 'bi-shield-check',    'bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#4ade80'],
        ['cat' => 'Casual',          'label' => 'Casual',          'icon' => 'bi-clock-history',   'bg' => '#ffedd5', 'text' => '#c2410c', 'border' => '#fb923c'],
        ['cat' => 'Job Order',       'label' => 'Job Order',       'icon' => 'bi-file-text',       'bg' => '#e0f2fe', 'text' => '#0369a1', 'border' => '#38bdf8'],
        ['cat' => 'Vacant Funded',   'label' => 'Vacant Funded',   'icon' => 'bi-building-add',    'bg' => '#ccfbf1', 'text' => '#0f766e', 'border' => '#2dd4bf'],
        ['cat' => 'Vacant Unfunded', 'label' => 'Vacant Unfunded', 'icon' => 'bi-x-circle',        'bg' => '#f3f4f6', 'text' => '#4b5563', 'border' => '#9ca3af'],
    ];
@endphp
@php $activeCat = request('category'); @endphp
<div class="status-grid">
    @foreach($statCards as $s)
        @php $isActive = $activeCat === $s['cat']; @endphp
        <a href="{{ route('plantilla.index', ['category' => $s['cat']]) }}#results"
           class="stat-card{{ $isActive ? ' active' : '' }}"
           style="{{ $isActive ? 'border-color: ' . $s['border'] . '; background: ' . $s['bg'] . ';' : '' }}"
           title="Filter by {{ $s['label'] }}">
            
            <div class="stat-card-icon" style="background: {{ $s['bg'] }}; color: {{ $s['text'] }};">
                <i class="bi {{ $s['icon'] }}"></i>
            </div>
            <div class="stat-card-content">
                <div class="stat-card-num">{{ number_format($statusCounts[$s['cat']]) }}</div>
                <div class="stat-card-label" style="{{ $isActive ? 'color: ' . $s['text'] . ';' : '' }}">{{ $s['label'] }}</div>
            </div>

            @if($isActive)
                <div class="stat-card-check" style="color: {{ $s['text'] }};"><i class="bi bi-check-lg" style="color:#fff;"></i></div>
            @endif
        </a>
    @endforeach
</div>

{{-- Active-filter banner --}}
@php
    $activeAge = request('age_range');
    $activeOffice = request('office');
    $activeSearch = request('search');
    $activePosition = request('position');
    $hasFilter = $activeCat || $activeAge || $activeOffice || $activeSearch || $activePosition;
    $totalRecords = array_reduce($grouped, function ($carry, $cats) {
        return $carry + array_sum(array_map('count', $cats));
    }, 0);
@endphp

@if($hasFilter)
    <div class="active-filter-bar">
        <span class="active-filter-label" style="display:inline-flex; align-items:center; flex-wrap:wrap; gap:6px;">
            <i class="bi bi-funnel-fill"></i>
            <span>Showing:</span>
            @if($activeCat) <strong style="background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:6px; font-weight:700;">{{ $activeCat }}</strong> @endif
            @if($activeAge) <strong style="background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:6px; font-weight:700;">Age: {{ $activeAge }}</strong> @endif
            @if($activeOffice) <strong style="background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:6px; font-weight:700;">Office: {{ Str::limit($activeOffice, 25) }}</strong> @endif
            @if($activePosition) <strong style="background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:6px; font-weight:700;">Position: {{ Str::limit($activePosition, 30) }}</strong> @endif
            @if($activeSearch) <strong style="background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:6px; font-weight:700;">Search: "{{ $activeSearch }}"</strong> @endif
            <span style="opacity:0.8;">&nbsp;·&nbsp; {{ number_format($totalRecords) }} record(s) found</span>
        </span>
        <a href="{{ route('plantilla.index') }}" class="active-filter-clear">
            <i class="bi bi-x-lg"></i> Clear
        </a>
    </div>
@endif

{{-- ▌▌ AGE + GENDER ▌▌ --}}
<div class="analytics-row">

    {{-- Age Distribution --}}
    <div class="card-panel">
        <div class="card-panel-header" style="display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:14px;font-weight:700;color:#1f2937;">Age Distribution</div>
                <div style="font-size:12px;color:#9ca3af;margin-top:2px;">Active personnel by age group</div>
            </div>
            <span style="background:#fef2f2;color:#b91c1c;font-size:11px;font-weight:700;padding:4px 10px;border-radius:99px;border:1px solid #fecaca;">
                ⚠ {{ $ageRanges['61-65'] }} nearing retirement
            </span>
        </div>
        <div class="card-panel-body">
            @php
                $maxAge = max(array_values($ageRanges)) ?: 1;
                $ageDefs = [
                    ['label' => '21–30', 'key' => '21-30', 'color' => '#3b82f6'],
                    ['label' => '31–40', 'key' => '31-40', 'color' => '#3b82f6'],
                    ['label' => '41–50', 'key' => '41-50', 'color' => '#3b82f6'],
                    ['label' => '51–60', 'key' => '51-60', 'color' => '#3b82f6'],
                ];
            @endphp
            @foreach($ageDefs as $ag)
                @php 
                                $cnt = $ageRanges[$ag['key']];
                    $w = round($cnt / $maxAge * 100);
                    $isActiveAge = request('age_range') === $ag['key'];
                    $filterParams = array_merge(request()->query(), ['age_range' => $isActiveAge ? null : $ag['key']]);
                @endphp
                <a href="{{ route('plantilla.index', $filterParams) }}#results" class="age-bar-row {{ $isActiveAge ? 'active-age-bar' : '' }}" style="text-decoration:none; display:flex; padding:6px; border-radius:8px; margin:0 -6px 4px; transition:background .15s; {{ $isActiveAge ? 'background:#f1f5f9;' : '' }}">
                    <span class="age-bar-label">{{ $ag['label'] }}</span>
                    <div class="age-bar-track">
                        <div class="age-bar-fill" style="width:{{ $w }}%;background:{{ $ag['color'] }}; {{ $isActiveAge ? 'box-shadow: 0 0 0 2px #f1f5f9, 0 0 0 4px ' . $ag['color'] . ';' : '' }}"></div>
                    </div>
                    <span class="age-bar-count">{{ $cnt }}</span>
                </a>
            @endforeach
            {{-- Retirement belt --}}
            @php 
                                $cnt61 = $ageRanges['61-65'];
                $w61 = round($cnt61 / $maxAge * 100);
                $isActive61 = request('age_range') === '61-65';
                $retireParams = array_merge(request()->query(), ['age_range' => $isActive61 ? null : '61-65']);
            @endphp
            <a href="{{ route('plantilla.index', $retireParams) }}#results" class="age-retire-row" style="text-decoration:none; transition:box-shadow .15s; {{ $isActive61 ? 'box-shadow: 0 0 0 2px #fff, 0 0 0 4px #ef4444;' : '' }}">
                <span class="age-bar-label" style="color:#b91c1c;font-weight:800;">61–65</span>
                <div class="age-bar-track" style="background:#fecaca;">
                    <div class="age-bar-fill" style="width:{{ $w61 }}%;background:linear-gradient(90deg,#ef4444,#b91c1c);"></div>
                </div>
                <span class="age-bar-count" style="color:#b91c1c;">{{ $cnt61 }}</span>
            </a>
            <div style="font-size:11px;color:#f87171;margin-top:8px;padding-left:4px;font-style:italic;">
                ⚠ Employees aged 61–65 must be monitored for compulsory retirement
            </div>
        </div>
    </div>

    {{-- Gender --}}
    <div class="card-panel">
        <div class="card-panel-header">
            <div style="font-size:14px;font-weight:700;color:#1f2937;">Gender Breakdown</div>
            <div style="font-size:12px;color:#9ca3af;margin-top:2px;">Active filled positions only</div>
        </div>
        <div class="card-panel-body">
            @php
                $totalG = ($genderCounts['M'] + $genderCounts['F']) ?: 1;
                $mPct = round($genderCounts['M'] / $totalG * 100, 1);
                $fPct = round($genderCounts['F'] / $totalG * 100, 1);
                $activeSex = request('sex');
            @endphp
            <div style="display:flex;gap:12px;">
                <a href="{{ route('plantilla.index', array_merge(request()->query(), ['sex' => $activeSex === 'M' ? null : 'M'])) }}#results"
                   class="gender-card" style="text-decoration:none; flex: 1; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; gap: 20px; transition: all 0.2s; {{ $activeSex === 'M' ? 'box-shadow:0 0 0 2px #fff,0 0 0 4px #1d4ed8; border-color:transparent;' : 'background: #fff;' }}">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="background: #10327cff; color: #fff; width: 60px; height: 60px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 32px; flex-shrink: 0;">
                            <i class="bi bi-gender-male"></i>
                        </div>
                        <div>
                            <div style="font-size: 28px; font-weight: 800; color: #111827; line-height: 1.1;">{{ number_format($genderCounts['M']) }}</div>
                            <div style="font-size: 15px; font-weight: 500; color: #111827; margin-top: 4px;">Male</div>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 8px;">{{ $mPct }}%</div>
                        <div style="height: 10px; background: #e5e7eb; border-radius: 99px; overflow: hidden;">
                            <div style="height: 100%; background: #10327cff; width: {{ $mPct }}%; border-radius: 99px;"></div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('plantilla.index', array_merge(request()->query(), ['sex' => $activeSex === 'F' ? null : 'F'])) }}#results"
                   class="gender-card" style="text-decoration:none; flex: 1; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; gap: 20px; transition: all 0.2s; {{ $activeSex === 'F' ? 'box-shadow:0 0 0 2px #fff,0 0 0 4px #ec4899; border-color:transparent;' : 'background: #fff;' }}">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="background: #ec4899; color: #fff; width: 60px; height: 60px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 32px; flex-shrink: 0;">
                            <i class="bi bi-gender-female"></i>
                        </div>
                        <div>
                            <div style="font-size: 28px; font-weight: 800; color: #111827; line-height: 1.1;">{{ number_format($genderCounts['F']) }}</div>
                            <div style="font-size: 15px; font-weight: 500; color: #111827; margin-top: 4px;">Female</div>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 8px;">{{ $fPct }}%</div>
                        <div style="height: 10px; background: #e5e7eb; border-radius: 99px; overflow: hidden;">
                            <div style="height: 100%; background: #ec4899; width: {{ $fPct }}%; border-radius: 99px;"></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ▌▌ VACANT POSITIONS ▌▌ --}}
<div class="both-col">
    {{-- Funded --}}
    <div class="card-panel">
        <div class="card-panel-header" style="display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#eff6ff,#dbeafe);">
            <div>
                <div style="font-size:14px;font-weight:700;color:#1e40af;"><i class="bi bi-building-add me-1"></i> Vacant Funded</div>
                <div style="font-size:11px;color:#93c5fd;margin-top:2px;">{{ number_format($vacantFunded->sum()) }} total open slots</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <a href="{{ route('plantilla.form9') }}" target="_blank" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;font-size:11px;font-weight:700;padding:6px 12px;border-radius:6px;text-decoration:none;" title="Generate CSC Form No. 9">
                    <i class="bi bi-printer"></i> Print Form 9
                </a>
                <a href="{{ route('plantilla.vacant.export.pdf', ['type' => 'funded']) }}" target="_blank" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;font-size:11px;font-weight:700;padding:6px 10px;border-radius:6px;text-decoration:none;" title="Export PDF">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <a href="{{ route('plantilla.vacant.export.excel', ['type' => 'funded']) }}" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;font-size:11px;font-weight:700;padding:6px 10px;border-radius:6px;text-decoration:none;" title="Export Excel">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
                <span style="font-size:32px;font-weight:900;color:#1d4ed8;line-height:1;">{{ number_format($vacantFunded->count()) }}</span>
            </div>
        </div>
        @if($vacantFunded->isEmpty())
            <div style="text-align:center;padding:24px;color:#9ca3af;font-size:13px;font-style:italic;">No vacant funded positions</div>
        @else
            <div class="vacant-scrollable">
                @foreach($vacantFunded as $title => $count)
                    <a href="{{ route('plantilla.vacant-funded.detail', ['position' => $title]) }}"
                       class="vacant-row"
                       style="display:flex;align-items:center;justify-content:space-between;text-decoration:none;cursor:pointer;transition:background .15s;"
                       title="View all {{ $count }} open slot(s) for {{ $title }}">
                        <span style="font-size:13px;color:#1e40af;font-weight:600;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $title ?: 'Untitled Position' }}
                        </span>
                        <span style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                            <span class="vacant-badge" style="background:#1d4ed8;">{{ $count }}</span>
                            <i class="bi bi-chevron-right" style="font-size:11px;color:#93c5fd;"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>


    {{-- Unfunded --}}
    <div class="card-panel">
        <div class="card-panel-header" style="display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#f9fafb,#f3f4f6);">
            <div>
                <div style="font-size:14px;font-weight:700;color:#4b5563;"><i class="bi bi-x-circle me-1"></i> Vacant Unfunded</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:2px;">{{ number_format($vacantUnfunded->sum()) }} total abolished slots</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <a href="{{ route('plantilla.vacant.export.pdf', ['type' => 'unfunded']) }}" target="_blank" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;font-size:11px;font-weight:700;padding:6px 10px;border-radius:6px;text-decoration:none;" title="Export PDF">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <a href="{{ route('plantilla.vacant.export.excel', ['type' => 'unfunded']) }}" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;font-size:11px;font-weight:700;padding:6px 10px;border-radius:6px;text-decoration:none;" title="Export Excel">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
                <span style="font-size:32px;font-weight:900;color:#9ca3af;line-height:1;">{{ number_format($vacantUnfunded->count()) }}</span>
            </div>
        </div>
        @if($vacantUnfunded->isEmpty())
            <div style="text-align:center;padding:24px;color:#9ca3af;font-size:13px;font-style:italic;">No vacant unfunded positions</div>
        @else
            <div class="vacant-scrollable">
                @foreach($vacantUnfunded as $title => $count)
                    <a href="{{ route('plantilla.vacant-unfunded.detail', ['position' => $title]) }}"
                       class="vacant-row" style="text-decoration:none;">
                        <span style="font-size:13px;color:#374151;font-weight:600;">{{ $title ?: 'Untitled Position' }}</span>
                        <span class="vacant-badge" style="background:#6b7280;">{{ $count }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ▌▌ NEAR RETIREMENT ALERT ▌▌ --}}
@if($nearRetirement->isNotEmpty())
    <div class="retire-alert">
        <div class="retire-alert-header" onclick="toggleRetirementAlert()">
            <div class="retire-alert-left">
                <div class="retire-pulse">
                    <i class="bi bi-alarm" style="color:#fff;font-size:18px;"></i>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:800;color:#991b1b;">⚠ Compulsory Retirement Alert — Ages 61–65</div>
                    <div style="font-size:12px;color:#ef4444;margin-top:3px;">
                        {{ $nearRetirement->count() }} employee(s) within the retirement-eligible age range. Click to view.
                    </div>
                </div>
            </div>
            <div>
                <i class="bi bi-chevron-down" id="retire-alert-chev" style="color: #ef4444; font-size: 16px; transition: transform .2s; display: block;"></i>
            </div>
        </div>
        <div class="overflow-x-auto" id="retire-alert-table" style="background:#fff; display: none;">
            <table class="personnel-table">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Position Title</th>
                        <th>Office / Unit</th>
                        <th class="tc">Age</th>
                        <th class="tc">Date of Birth</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nearRetirement as $r)
                        <tr>
                            <td class="emp-name">
                                {{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}
                                {{ $r->middle_name ? strtoupper(substr($r->middle_name, 0, 1)) . '.' : '' }}
                            </td>
                            <td style="color:#4b5563;">{{ $r->position_title }}</td>
                            <td style="color:#9ca3af;font-size:12px;">{{ $r->organizational_unit }}</td>
                            <td class="tc">
                                <span style="background:#fef2f2;color:#b91c1c;font-weight:800;font-size:12px;padding:3px 10px;border-radius:99px;border:1px solid #fecaca;">
                                    {{ \Carbon\Carbon::parse($r->date_of_birth)->age }}
                                </span>
                            </td>
                            <td class="tc" style="color:#9ca3af;font-size:12px;">
                                {{ \Carbon\Carbon::parse($r->date_of_birth)->format('M d, Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- ▌▌ FILTER BAR ▌▌ --}}
<div id="results" class="filter-bar">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;margin-bottom:12px;">
        <i class="bi bi-funnel me-1"></i> Filter Records
    </div>
    <form method="GET" action="{{ route('plantilla.index') }}">
        <div class="filter-field filter-field-lg">
            <label>Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, item, position..." style="width:100%;">
        </div>
        <div class="filter-field filter-field-sm">
            <label>Office</label>
            <select name="office" style="width:100%;">
                <option value="">All Offices</option>
                @foreach($offices as $office)
                    <option value="{{ $office }}" @selected(request('office') === $office)>{{ $office }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-field filter-field-sm">
            <label>Position</label>
            <select name="position" style="width:100%;">
                <option value="">All Positions</option>
                @foreach($positions as $pos)
                    <option value="{{ $pos }}" @selected(request('position') === $pos)>{{ Str::limit($pos, 40) }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-field filter-field-sm">
            <label>Appointment Type</label>
            <select name="category" style="width:100%;">
                <option value="">All Types</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-end;">
            <button type="submit" style="background:#2563eb;color:#fff;border:none;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                <i class="bi bi-search me-1"></i> Filter
            </button>
            <a href="{{ route('plantilla.index') }}" style="border:1px solid #e5e7eb;color:#6b7280;padding:9px 16px;border-radius:8px;font-size:13px;text-decoration:none;background:#fff;">
                Reset
            </a>
        </div>
    </form>
</div>



{{-- ▌▌ PER-OFFICE ACCORDION ▌▌ --}}
@php
    $catDefs = [
        'Elected' => ['color' => '#7c3aed', 'bg' => '#f5f3ff', 'pill' => '#ede9fe', 'pillText' => '#7c3aed'],
        'Co-Terminous' => ['color' => '#4338ca', 'bg' => '#eef2ff', 'pill' => '#e0e7ff', 'pillText' => '#4338ca'],
        'Permanent' => ['color' => '#047857', 'bg' => '#ecfdf5', 'pill' => '#d1fae5', 'pillText' => '#047857'],
        'Casual' => ['color' => '#d97706', 'bg' => '#fffbeb', 'pill' => '#fef3c7', 'pillText' => '#b45309'],
        'Job Order' => ['color' => '#c2410c', 'bg' => '#fff7ed', 'pill' => '#fed7aa', 'pillText' => '#c2410c'],
        'Vacant Funded' => ['color' => '#1d4ed8', 'bg' => '#eff6ff', 'pill' => '#dbeafe', 'pillText' => '#1d4ed8'],
        'Vacant Unfunded' => ['color' => '#6b7280', 'bg' => '#f9fafb', 'pill' => '#f3f4f6', 'pillText' => '#4b5563'],
    ];
@endphp

@forelse($grouped as $office => $cats_data)
    @php
        $officeTotal = array_sum(array_map('count', $cats_data));
        $oid = 'off-' . md5($office);
        $subName = 'Bukidnon Provincial Office';
        if (str_starts_with($office, 'BPH')) {
            $subName = 'Bukidnon Provincial Hospital';
        }
    @endphp
    <div class="office-card">
        <button type="button" onclick="toggleOffice('{{ $oid }}')" class="office-btn">

            <div class="off-left">
                <div class="off-icon-box"><i class="bi bi-building"></i></div>
                <div>
                    <div class="off-name">{{ $office }}</div>
                    <div class="off-sub">{{ $subName }}</div>
                </div>
            </div>

            <div class="off-middle">
                <div class="off-stat-col">
                    <span class="off-stat-label">TOTAL POSITIONS</span>
                    <span class="off-stat-num">{{ str_pad($officeTotal, 2, '0', STR_PAD_LEFT) }}</span>
                </div>

                @foreach($categories as $cat)
                    @if(!empty($cats_data[$cat]))
                        @php $cd = $catDefs[$cat]; @endphp
                        <div class="off-stat-col">
                            <span class="off-pill" style="background:{{ $cd['pill'] }};color:{{ $cd['pillText'] }}; border-color:{{ $cd['pillText'] }}30;">{{ $cat }}</span>
                            <span class="off-stat-num">{{ str_pad(count($cats_data[$cat]), 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="off-right">
                <span class="off-view-btn">
                    View Details <i class="bi bi-chevron-right chevron" id="{{ $oid }}-chev"></i>
                </span>
            </div>

        </button>

        <div id="{{ $oid }}" style="border-top:1px solid #f3f4f6; display: none;">
            @foreach($categories as $cat)
                @if(!empty($cats_data[$cat]))
                    @php $recs = $cats_data[$cat];
                    $cd = $catDefs[$cat]; @endphp
                    <div>
                        <div class="cat-header" style="background:{{ $cd['bg'] }};border-color:{{ $cd['color'] }};">
                            <span class="cat-label" style="color:{{ $cd['color'] }};">{{ $cat }}</span>
                            <span class="cat-count">{{ count($recs) }} position{{ count($recs) !== 1 ? 's' : '' }}</span>
                        </div>
                        <div class="overflow-x-auto">
                        <table class="personnel-table">
                            <thead>
                                <tr>
                                    <th style="width:110px;">Item No.</th>
                                    <th>Position Title</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th class="tc" style="width:60px;">SG</th>
                                    <th class="tc" style="width:60px;">Step</th>
                                    <th class="tc" style="width:140px;">Status</th>
                                    <th class="tc" style="width:100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recs as $rec)
                                    <tr>
                                        <td><span class="item-code">#{{ $rec->item }}</span></td>
                                        <td>
                                            <div class="pos-title-text">{{ $rec->position_title }}</div>
                                            <div class="pos-dept-text">{{ $subName }}</div>
                                        </td>
                                        {{-- Last Name --}}
                                        <td>
                                            @if($rec->is_vacant)
                                                <div class="emp-vacant-text">Vacant</div>
                                            @else
                                                <div class="emp-name-text">{{ strtoupper($rec->last_name ?? '') }}</div>
                                            @endif
                                        </td>
                                        {{-- First Name --}}
                                        <td>
                                            @if(!$rec->is_vacant)
                                                <div class="emp-name-text">{{ $rec->first_name ?? '' }}</div>
                                            @endif
                                        </td>
                                        <td class="tc"><span class="num-text">{{ $rec->salary_grade }}</span></td>
                                        <td class="tc"><span class="num-text">{{ $rec->step }}</span></td>
                                        <td class="tc">
                                            @php
                                                $pillClass = 'status-default';
                                                $statusText = $cat; // uses category
                                                if ($rec->is_vacant) {
                                                    $pillClass = 'status-vacant';
                                                    $statusText = 'Vacant';
                                                } elseif (str_contains(strtolower($cat), 'permanent')) {
                                                    $pillClass = 'status-permanent';
                                                } elseif (str_contains(strtolower($cat), 'casual') || str_contains(strtolower($cat), 'contractual')) {
                                                    $pillClass = 'status-contractual';
                                                } elseif (str_contains(strtolower($cat), 'job order')) {
                                                    $pillClass = 'status-probationary'; // using yellow for JO
                                                }
                                            @endphp
                                            <span class="status-pill {{ $pillClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td class="tc">
                                            <div class="table-actions" style="display:flex;gap:4px;justify-content:center;">
                                                <a href="{{ route('plantilla.show', $rec) }}" class="btn-icon-plain" style="width:auto;padding:0 8px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:4px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;text-decoration:none;transition:all 0.15s;color:#3b82f6;" title="View details" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe';" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0';">
                                                    <i class="bi bi-eye"></i> VIEW
                                                </a>
                                                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                                                    <a href="{{ route('plantilla.edit', $rec) }}" class="btn-icon-plain" style="width:auto;padding:0 8px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:4px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;text-decoration:none;transition:all 0.15s;color:#f59e0b;" title="Edit record" onmouseover="this.style.background='#fffbeb';this.style.borderColor='#fde68a';" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0';">
                                                        <i class="bi bi-pencil"></i> EDIT
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@empty
    <div class="card-panel">
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-search"></i></div>
            <div style="font-size:16px;font-weight:700;color:#1e3a5f;margin-bottom:6px;">No records found</div>
            <div style="font-size:13px;color:#94a3b8;">Try adjusting your search or filter criteria.</div>
            <a href="{{ route('plantilla.index') }}"
               style="display:inline-flex;align-items:center;gap:6px;margin-top:18px;
                      background:linear-gradient(135deg,#1e3a5f,#1a5276);color:#fff;
                      padding:9px 22px;border-radius:10px;font-size:13px;font-weight:600;
                      text-decoration:none;box-shadow:0 4px 12px rgba(30,58,95,.25);">
                <i class="bi bi-arrow-counterclockwise"></i> Clear all filters
            </a>
        </div>
    </div>
@endforelse

<script>
function toggleOffice(id) {
    var body = document.getElementById(id);
    var chev = document.getElementById(id + '-chev');
    if (!body) return;
    var isOpen = body.style.display !== 'none';
    body.style.display   = isOpen ? 'none' : '';
    chev.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(90deg)';
}

function toggleRetirementAlert() {
    var table = document.getElementById('retire-alert-table');
    var chev = document.getElementById('retire-alert-chev');
    if (!table) return;
    var isHidden = table.style.display === 'none';
    table.style.display = isHidden ? 'block' : 'none';
    chev.style.transform = isHidden ? 'rotate(-180deg)' : 'rotate(0deg)';
}

// Auto-expand all office panels when a category or search filter is active
(function () {
    var params = new URLSearchParams(window.location.search);
    var hasFilter = params.get('category') || params.get('search') || params.get('office');
    if (!hasFilter) return;
    document.querySelectorAll('[id^="off-"]').forEach(function (panel) {
        if (!panel.id.endsWith('-chev')) {
            panel.style.display = '';
            var chev = document.getElementById(panel.id + '-chev');
            if (chev) chev.style.transform = 'rotate(90deg)';
        }
    });
})();
</script>
</x-dashboard-app>
