<x-dashboard-app>
    <style>
/* Filter bar */
        .cas-hero {
            background: linear-gradient(135deg, #052c65 0%, #1e3a8a 55%, #1e40af 100%);
            border-radius: 14px;
            padding: 24px 28px;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .cas-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .07) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .cas-hero-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .cas-hero h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            margin: 0;
        }

        .cas-hero p {
            color: rgba(255, 255, 255, .65);
            font-size: 12px;
            margin: 4px 0 0;
        }

        .cas-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .25);
            color: #fff;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
        }

        .cas-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) {
            .cas-stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 640px) {
            .cas-stats {
                grid-template-columns: 1fr;
            }
        }

        .cas-stat {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .cas-stat-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .cas-stat-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #475569;
            margin-bottom: 4px;
        }

        .cas-stat-value {
            font-size: 30px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            margin: 0;
        }

        .cas-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .cas-stat-icon.indigo {
            background: #e0e7ff;
            color: #4338ca;
        }

        .cas-stat-icon.blue {
            background: #e0f2fe;
            color: #0284c7;
        }

        .cas-stat-icon.purple {
            background: #f3e8ff;
            color: #9333ea;
        }

        .cas-stat-icon.red {
            background: #fee2e2;
            color: #dc2626;
        }

        .cas-stat-sub {
            margin-top: 14px;
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
        }
        .cas-filter {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 16px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        }

        .cas-input,
        .cas-select {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            color: #374151;
            outline: none;
            background: #fafafa;
            transition: border .15s, box-shadow .15s;
        }

        .cas-input:focus,
        .cas-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
            background: #fff;
        }

        .cas-input {
            flex: 1;
            min-width: 220px;
        }

        .cas-select {
            min-width: 140px;
        }

        .cas-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all .15s;
            white-space: nowrap;
        }

        .cas-btn.primary {
            background: #2563eb;
            color: #fff;
        }

        .cas-btn.primary:hover {
            background: #1d4ed8;
        }

        .cas-btn.reset {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .cas-btn.reset:hover {
            background: #e2e8f0;
        }

        .cas-btn.add {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            color: #fff;
        }

        .cas-btn.add:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .cas-btn.import {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .cas-btn.import:hover {
            background: #6d28d9;
        }

        /* ── Hero ─────────────────────────────────────────────────────── */
        .gender-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 26px; height: 26px; font-size: 11px; font-weight: 800;
            border-radius: 50%;
        }
        .gender-m {
            background: #eff6ff; color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        .gender-f {
            background: #fdf2f8; color: #db2777;
            border: 1px solid #fbcfe8;
        }

        .perm-hero {
            background: linear-gradient(135deg, #052c65 0%, #1e3a8a 55%, #1e40af 100%);
            border-radius: 14px;
            padding: 24px 28px;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .perm-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
            background-size: 22px 22px;
        }
        .perm-hero-inner {
            position: relative; z-index: 1;
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; flex-wrap: wrap;
        }
        .perm-hero h1 { color: #fff; font-size: 22px; font-weight: 800; margin: 0; }
        .perm-hero p  { color: rgba(255,255,255,.6); font-size: 12px; margin: 4px 0 0; }
        .perm-hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
            color: #fff; padding: 6px 14px; border-radius: 8px;
            font-size: 12px; font-weight: 700;
        }

        /* ── Stats ─────────────────────────────────────────────────────── */
        .perm-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 18px;
        }
        @media(max-width:900px) { .perm-stats { grid-template-columns: repeat(2,1fr); } }
        .perm-stat {
            background: #fff; border: 1px solid #e5e7eb;
            border-radius: 12px; padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .perm-stat-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .perm-stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #64748b; }
        .perm-stat-value { font-size: 28px; font-weight: 800; color: #0f172a; }
        .perm-stat-sub { font-size: 11px; color: #94a3b8; margin-top: 10px; }
        .perm-stat-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 20px;
        }
        .perm-stat-icon.green { background: #e0e7ff; color: #4338ca; }
        .perm-stat-icon.blue  { background: #dbeafe; color: #2563eb; }
        .perm-stat-icon.pink  { background: #fce7f3; color: #be185d; }
        .perm-stat-icon.red   { background: #fee2e2; color: #dc2626; }

        /* ── Filter ─────────────────────────────────────────────────────── */
        .perm-filter {
            background: #fff; border: 1px solid #e5e7eb;
            border-radius: 12px; padding: 14px 18px;
            margin-bottom: 16px; display: flex; gap: 10px;
            align-items: center; flex-wrap: wrap;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .perm-input, .perm-select {
            border: 1px solid #e5e7eb; border-radius: 8px;
            padding: 8px 12px; font-size: 12px; color: #374151;
            outline: none; background: #fafafa;
            transition: border .15s, box-shadow .15s;
        }
        .perm-input:focus, .perm-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,.1);
            background: #fff;
        }
        .perm-input { flex: 1; min-width: 200px; }
        .perm-select { min-width: 140px; }
        .perm-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 8px 16px; border-radius: 8px; font-size: 12px;
            font-weight: 700; border: none; cursor: pointer;
            transition: all .15s; white-space: nowrap; text-decoration: none;
        }
        .perm-btn.primary { background: #2563eb; color: #fff; }
        .perm-btn.primary:hover { background: #1d4ed8; }
        .perm-btn.reset { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .perm-btn.reset:hover { background: #e2e8f0; }
        .perm-btn.export { background: #16a34a; color: #fff; }
        .perm-btn.export:hover { background: #15803d; }

        /* ── Table ─────────────────────────────────────────────────────── */
        .perm-table-wrap {
            background: #fff; border: 1px solid #e5e7eb;
            border-radius: 12px; overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .perm-table-scroll {
            overflow-x: auto; overflow-y: auto;
            max-height: calc(100vh - 380px); min-height: 300px;
            /* Hide native horizontal scrollbar — replaced by custom strip below */
            scrollbar-width: none;
        }
        /* Hide only the horizontal scrollbar in WebKit */
        .perm-table-scroll::-webkit-scrollbar { width: 12px; height: 0; }
        .perm-table-scroll::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 10px; }
        .perm-table-scroll::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 10px; border: 2px solid #f3f4f6; }
        .perm-table-scroll::-webkit-scrollbar-thumb:hover { background: #6b7280; }

        /* ── Excel-style right-quarter horizontal scrollbar ────────── */
        .perm-hscroll-bar {
            display: flex; justify-content: flex-end;
            background: #f3f4f6; border-top: 1px solid #e5e7eb;
        }
        .perm-hscroll-inner {
            width: 25%; overflow-x: auto; overflow-y: hidden;
            height: 14px; scrollbar-width: thin; scrollbar-color: #9ca3af #f3f4f6;
        }
        .perm-hscroll-inner::-webkit-scrollbar { height: 14px; }
        .perm-hscroll-inner::-webkit-scrollbar-track { background: #f3f4f6; }
        .perm-hscroll-inner::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 10px; border: 2px solid #f3f4f6; }
        .perm-hscroll-inner::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        .perm-hscroll-ghost { height: 1px; }

        .perm-table {
            width: 100%; border-collapse: separate; border-spacing: 0;
            min-width: 900px;
        }
        .perm-table thead tr {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            position: sticky; top: 0; z-index: 2;
        }
        .perm-table th {
            padding: 10px 12px; text-align: left; white-space: nowrap;
            font-size: 9.5px; font-weight: 800; text-transform: uppercase;
            letter-spacing: .8px; color: rgba(255,255,255,.75);
            border-bottom: 2px solid rgba(255,255,255,.08);
            border-right: 1px solid rgba(255,255,255,.08);
        }
        .perm-table th:last-child { border-right: 0; }
        .perm-table tbody tr:nth-child(even) td { background: #eef2ff; }
        .perm-table tbody tr:hover td { background: #e0e7ff !important; transition: background .1s; }
        .perm-table td {
            padding: 9px 12px; font-size: 12px; color: #374151;
            border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9;
            white-space: nowrap; max-width: 200px;
            overflow: hidden; text-overflow: ellipsis;
        }
        .perm-table td:last-child { border-right: 0; }
        .perm-table tbody tr:last-child td { border-bottom: 0; }

        /* Sticky col #1 */
        .perm-table td:first-child, .perm-table th:first-child {
            position: sticky; left: 0; z-index: 1;
            background: #fff; border-right: 2px solid #e5e7eb !important;
            min-width: 60px;
        }
        .perm-table thead th:first-child { background: #312e81; }
        .perm-table tbody tr:nth-child(even) td:first-child { background: #eef2ff; }
        .perm-table tbody tr:hover td:first-child { background: #e0e7ff !important; }

        /* Status badges */
        .badge-perm {
            display: inline-flex; align-items: center;
            padding: 2px 8px; border-radius: 99px;
            font-size: 10px; font-weight: 700;
            background: #e0e7ff; color: #3730a3;
        }
        .badge-ct {
            display: inline-flex; align-items: center;
            padding: 2px 8px; border-radius: 99px;
            font-size: 10px; font-weight: 700;
            background: #dbeafe; color: #1e40af;
        }
        .badge-elected {
            display: inline-flex; align-items: center;
            padding: 2px 8px; border-radius: 99px;
            font-size: 10px; font-weight: 700;
            background: #fef3c7; color: #92400e;
        }
        .badge-vacant {
            display: inline-flex; align-items: center;
            padding: 2px 8px; border-radius: 99px;
            font-size: 10px; font-weight: 700;
            background: #fee2e2; color: #991b1b;
        }

        /* Action buttons */
        .row-btn {
            display: inline-flex; align-items: center; justify-content: center;
            height: 24px; padding: 0 8px; gap: 4px; border-radius: 5px;
            font-size: 10px; font-weight: 700; text-decoration: none;
            transition: all .12s; border: 1px solid transparent;
            cursor: pointer; white-space: nowrap; font-family: inherit;
        }
        .row-btn.edit { background: #fffbeb; color: #d97706; border-color: #fde68a; }
        .row-btn.edit:hover { background: #d97706; color: #fff; }
        .row-btn.arc { background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe; }
        .row-btn.arc:hover { background: #7c3aed; color: #fff; }

        /* Table footer */
        .perm-table-footer {
            padding: 12px 18px; border-top: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; flex-wrap: wrap;
        }
        .perm-count { font-size: 12px; color: #6b7280; }

        /* Flash */
        .flash-success {
            background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534;
            border-radius: 10px; padding: 11px 16px; margin-bottom: 14px;
            font-size: 13px; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
        }
        .flash-error {
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            border-radius: 10px; padding: 11px 16px; margin-bottom: 14px;
            font-size: 13px; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
        }
    </style>

        {{-- Hero --}}
    <div class="cas-hero">
        <div class="cas-hero-inner">
            <div>
                <h1><i class="bi bi-person-badge me-2"></i>Regular Employees</h1>
                <p>Regular/Permanent personnel inventory — all {{ number_format($total) }} entries</p>
            </div>
            <span class="cas-hero-badge"><i class="bi bi-database-fill"></i> {{ number_format($total) }} Records</span>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
    @endif

    {{-- Stats bar --}}
    @php
        $permGadTotal = $maleCount + $femaleCount;
        $permFemalePct = $permGadTotal > 0 ? round($femaleCount / $permGadTotal * 100, 1) : 0;
        $permGadOk = $permFemalePct >= 40 && $permFemalePct <= 60;
        $filledTotal = $total - ($vacantCount ?? 0);
    @endphp
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px;">
        <x-stat-card icon="bi-person-badge-fill" color="indigo" label="Total Regular" :value="$total"
            sub="Permanent/CT/Elected items"
            compliance="Civil Service"
            analysis="Regular appointments covered by PD 807 (Civil Service Decree) and RA 7041 (Publication Law). All items require DBM authority." />

        <x-stat-card icon="bi-gender-male" color="blue" label="Male" :value="$maleCount"
            :pct="$permGadTotal > 0 ? round($maleCount/$permGadTotal*100,1) : 0"
            sub="of sex-tagged regulars"
            compliance="RA 9710 GAD"
            analysis="Male regular employees. Track sex balance as required by RA 9710 and CSC MC No. 3, s.2004 (Gender Development)." />

        <x-stat-card icon="bi-gender-female" color="pink" label="Female" :value="$femaleCount"
            :pct="$permFemalePct"
            sub="of sex-tagged regulars"
            compliance="RA 9710 GAD"
            :alert="!$permGadOk"
            analysis="{{ $permGadOk ? 'GAD compliant: '.$permFemalePct.'% female among regular staff is within the 40–60% window (RA 9710).' : 'ATTENTION: '.$permFemalePct.'% female ratio deviates from the 40–60% GAD target. Review hiring plans (RA 9710 §12).' }}" />

        <x-stat-card icon="bi-person-x-fill" color="red" label="Vacant Positions" :value="$vacantCount ?? 0"
            sub="Unfilled plantilla items"
            compliance="RA 7041"
            :alert="($vacantCount ?? 0) > 0"
            analysis="Vacant positions must be published per RA 7041 (Publication Law) before filling. Prolonged vacancies affect service delivery." />
    </div>
    <form method="GET" action="{{ route('permanent.index') }}" id="perm-search-form">
    <div class="cas-filter" style="display: flex; flex-direction: column; gap: 12px; align-items: stretch; padding: 14px 18px; margin-bottom: 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, .04);">
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <input type="text" name="search" value="{{ request('search') }}" class="cas-input" placeholder="🔍 Search name, position, office…" autocomplete="off">
                
                <select name="office" class="cas-select" style="min-width:200px;">
                    <option value="">— All Offices —</option>
                    @foreach($offices as $office)
                        <option value="{{ $office }}" {{ request('office') === $office ? 'selected' : '' }}>{{ Str::limit($office, 40) }}</option>
                    @endforeach
                </select>

                <select name="status" class="cas-select" style="min-width:140px;">
                    <option value="">— All Status —</option>
                    <option value="P"  {{ request('status') === 'P'  ? 'selected' : '' }}>Regular</option>
                    <option value="CT" {{ request('status') === 'CT' ? 'selected' : '' }}>Co-Terminous</option>
                    <option value="E"  {{ request('status') === 'E'  ? 'selected' : '' }}>Elected</option>
                </select>

                <select name="sex" class="cas-select" style="min-width:110px;">
                    <option value="">— All Genders —</option>
                    <option value="M" {{ request('sex') === 'M' ? 'selected' : '' }}>Male</option>
                    <option value="F" {{ request('sex') === 'F' ? 'selected' : '' }}>Female</option>
                </select>
                
                <button type="submit" class="cas-btn primary"><i class="bi bi-search"></i> Search</button>
                <a href="{{ route('permanent.index') }}" class="cas-btn reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            </div>
            
            <!-- Bottom Row: Actions (Far Left) -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-start;">
                
                <a href="{{ route('archives.index') }}" class="cas-btn" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;"><i class="bi bi-archive-fill"></i> View Archives</a>
                <button type="button" class="cas-btn" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;gap:5px;" onclick="new bootstrap.Modal(document.getElementById('exportModal')).show()"><i class="bi bi-file-earmark-arrow-down-fill"></i> Export Settings</button>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <button type="button" class="cas-btn" id="toggle-select-multiple" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;"><i class="bi bi-ui-checks-grid"></i> Select Multiple</button>
                @endif
                @if(auth()->user()->isSuperAdmin())
                    <button type="button" id="perm-delete-all-btn" class="cas-btn" style="display:inline-flex;align-items:center;gap:5px;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;background:#ffffff;color:#334155;border:1px solid #cbd5e1;transition:all .15s;" onclick="document.getElementById('perm-delete-all-overlay').classList.add('active')"><i class="bi bi-trash3-fill"></i> Delete All Data</button>
                @endif
            </div>
        </div></form>

    {{-- Bulk Action Bar --}}
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
    <div id="bulk-action-bar" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; align-items: center; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="background: #3b82f6; color: white; border-radius: 9999px; padding: 2px 10px; font-size: 12px; font-weight: 700;" id="bulk-count">0</span>
            <span style="font-size: 13px; font-weight: 600; color: #334155;">records selected</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <form id="bulk-archive-form" method="POST" action="{{ route('permanent.bulk-archive') }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="type" value="permanent">
                <div id="bulk-ids-container"></div>
                <button type="button" onclick="confirmBulkArchive()" class="perm-btn" style="background: #7c3aed; color: #fff;">
                    <i class="bi bi-archive-fill"></i> Archive Selected
                </button>
            </form>
            @if(auth()->user()->isSuperAdmin())
                <form id="bulk-force-delete-form" method="POST" action="{{ route('permanent.bulk-force-delete') }}" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="type" value="permanent">
                    <div id="bulk-ids-container-fd"></div>
                    <button type="button" onclick="confirmBulkForceDelete()" class="perm-btn" style="background: #dc2626; color: #fff;">
                        <i class="bi bi-trash-fill"></i> Permanently Delete
                    </button>
                </form>
            @endif
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="perm-table-wrap">
        <div class="perm-table-scroll" id="perm-table-scroll">
            <table class="perm-table">
                <thead>
                    <tr>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                            <th class="checkbox-col" style="display:none; text-align:center; padding-left: 16px;">
                                <input type="checkbox" id="selectAll" style="cursor:pointer; width: 14px; height: 14px;">
                            </th>
                        @endif
                        <th>#</th>
                        <th>OFFICE</th>
                        <th colspan="4" style="text-align:center;">NAME</th>
                        <th>Position Title</th>
                        <th style="text-align:center;">DATE OF BIRTH</th>
                        <th style="text-align:center;">SEX</th>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                            <th style="text-align:center;">Actions</th>
                        @endif
                    </tr>
                    <tr>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                            <th class="checkbox-col" style="display:none;"></th>
                        @endif
                        <th></th>
                        <th></th>
                        <th>LAST NAME</th>
                        <th>First Name</th>
                        <th>MIDDLE NAME</th>
                        <th>SUFFIX</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                            <th></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $i => $r)
                        <tr>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                                <td class="checkbox-col" style="display:none; text-align:center; padding-left: 16px;">
                                    <input type="checkbox" class="record-checkbox" value="{{ $r->id }}" style="cursor:pointer; width: 14px; height: 14px;">
                                </td>
                            @endif

                            <td style="color:#9ca3af;font-size:10px;font-weight:600;">{{ $i + 1 }}</td>

                            <td title="{{ $r->office_department }}" style="font-size:11px;color:#64748b;">
                                {{ $r->office_department }}
                            </td>

                            <td style="font-weight:700;text-transform:uppercase;color:#0f172a;">
                                {{ strtoupper($r->last_name ?: '—') }}
                            </td>

                            <td>{{ $r->first_name ?: '—' }}</td>

                            <td style="color:#6b7280;">{{ $r->middle_name ?: '—' }}</td>

                            <td style="color:#9ca3af;font-size:11px;">—</td>

                            <td title="{{ $r->position_title }}" style="font-weight:600;color:#0f172a;">
                                {{ $r->position_title }}
                            </td>

                            <td style="color:#6b7280;text-align:center;font-size:11px;">
                                {{ $r->date_of_birth ? \Carbon\Carbon::parse($r->date_of_birth)->format('M d, Y') : '—' }}
                            </td>

                            <td style="text-align:center;">
                                @if(strtoupper($r->sex) === 'M')
                                    <span class="gender-badge gender-m">M</span>
                                @elseif(strtoupper($r->sex) === 'F')
                                    <span class="gender-badge gender-f">F</span>
                                @else
                                    <span style="color:#94a3b8;font-size:11px;">-</span>
                                @endif
                            </td>

                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                                <td style="text-align:center;white-space:nowrap;">
                                    <div style="display:flex;gap:6px;justify-content:center;align-items:center;">
                                        <a href="{{ route('employees.profile', ['type' => 'permanent', 'id' => $r->id]) }}" class="row-btn" style="background: #e0e7ff; color: #4338ca; border-color: #c7d2fe;" title="201 Profile">
                                            <i class="bi bi-person-vcard"></i> 201 Profile
                                        </a>
                                        <a href="{{ route('plantilla.edit', $r) }}" class="row-btn edit" title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('permanent.archive', $r) }}"
                                            id="form-arc-perm-{{ $r->id }}" style="display:contents;">
                                            @csrf
                                            <button type="button"
                                                onclick="confirmPermArchive('{{ $r->id }}')"
                                                class="row-btn arc" title="Archive">
                                                <i class="bi bi-archive"></i> Archive
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:48px;color:#9ca3af;font-style:italic;">
                                <i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:10px;"></i>
                                No permanent employee records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Excel-style right-quarter horizontal scrollbar --}}
        <div class="perm-hscroll-bar">
            <div class="perm-hscroll-inner" id="perm-hscroll-inner">
                <div class="perm-hscroll-ghost" id="perm-hscroll-ghost"></div>
            </div>
        </div>

        <div class="perm-table-footer">
            <span class="perm-count">Showing {{ number_format($records->total()) }} record(s)</span>
            <div style="font-size:11px;color:#94a3b8;">
                Male: <strong>{{ $maleCount }}</strong> &nbsp;|&nbsp;
                Female: <strong>{{ $femaleCount }}</strong>
            </div>
        </div>
    </div>

    {{-- SweetAlert2 --}}
    
    <div style="padding: 12px 18px; border-top: 1px solid #f1f5f9; background: #fff; border-radius: 0 0 12px 12px;">
        {{ $records->links('pagination::bootstrap-5') }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ── Excel-style right-quarter horizontal scrollbar sync (Permanent) ──
        (function () {
            const scrollEl = document.getElementById('perm-table-scroll');
            const hbarInner = document.getElementById('perm-hscroll-inner');
            const hbarGhost = document.getElementById('perm-hscroll-ghost');

            function syncGhostWidth() {
                const tableEl = scrollEl.querySelector('table');
                if (tableEl) { hbarGhost.style.width = tableEl.scrollWidth + 'px'; }
            }
            syncGhostWidth();
            window.addEventListener('resize', syncGhostWidth);

            let syncingFromBar = false;
            hbarInner.addEventListener('scroll', function () {
                if (syncingFromBar) return;
                syncingFromBar = true;
                const fakeRatio = hbarInner.scrollLeft / (hbarInner.scrollWidth - hbarInner.clientWidth);
                scrollEl.scrollLeft = fakeRatio * (scrollEl.scrollWidth - scrollEl.clientWidth);
                syncingFromBar = false;
            });

            let syncingFromTable = false;
            scrollEl.addEventListener('scroll', function () {
                if (syncingFromTable) return;
                syncingFromTable = true;
                const tableRatio = scrollEl.scrollLeft / (scrollEl.scrollWidth - scrollEl.clientWidth);
                hbarInner.scrollLeft = tableRatio * (hbarInner.scrollWidth - hbarInner.clientWidth);
                syncingFromTable = false;
            });
        })();
    </script>
    <script>
        function confirmPermArchive(id) {
            Swal.fire({
                title: 'Archive this record?',
                html: 'This permanent employee record will be moved to the <b>Archives</b>.<br>You can restore it later.',
                icon: 'info',
                iconColor: '#7c3aed',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, archive it'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-arc-perm-' + id).submit();
                }
            });
        }

        // Bulk Selection Logic
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.record-checkbox');
            const bulkBar = document.getElementById('bulk-action-bar');
            const bulkCount = document.getElementById('bulk-count');
            const bulkIdsContainer = document.getElementById('bulk-ids-container');

            function updateBulkBar() {
                const checked = document.querySelectorAll('.record-checkbox:checked');
                bulkCount.textContent = checked.length;
                if (checked.length > 0) {
                    bulkBar.style.display = 'flex';
                } else {
                    bulkBar.style.display = 'none';
                }
                
                // Update hidden inputs
                bulkIdsContainer.innerHTML = '';
                checked.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = cb.value;
                    bulkIdsContainer.appendChild(input);
                });

                const bulkIdsContainerFd = document.getElementById('bulk-ids-container-fd');
                if (bulkIdsContainerFd) {
                    bulkIdsContainerFd.innerHTML = '';
                    checked.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = cb.value;
                        bulkIdsContainerFd.appendChild(input);
                    });
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => {
                        cb.checked = selectAll.checked;
                    });
                    updateBulkBar();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    // Update selectAll state
                    if (selectAll) {
                        selectAll.checked = document.querySelectorAll('.record-checkbox:checked').length === checkboxes.length && checkboxes.length > 0;
                    }
                    updateBulkBar();
                });
            });

            // Toggle Select Multiple
            const toggleSelectBtn = document.getElementById('toggle-select-multiple');
            const checkboxCols = document.querySelectorAll('.checkbox-col');
            if (toggleSelectBtn) {
                toggleSelectBtn.addEventListener('click', function() {
                    let isHidden = checkboxCols.length > 0 && checkboxCols[0].style.display === 'none';
                    checkboxCols.forEach(col => {
                        col.style.display = isHidden ? 'table-cell' : 'none';
                    });
                    if (!isHidden) {
                        // Uncheck all if hiding
                        if (selectAll) selectAll.checked = false;
                        checkboxes.forEach(cb => cb.checked = false);
                        updateBulkBar();
                    }
                });
            }
        });

        function confirmBulkArchive() {
            const count = document.getElementById('bulk-count').textContent;
            Swal.fire({
                title: `Archive ${count} records?`,
                html: 'Selected records will be moved to the <b>Archives</b>.<br>You can restore them later.',
                icon: 'info',
                iconColor: '#7c3aed',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, archive selected'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('bulk-archive-form').submit();
                }
            });
        }

        function confirmBulkForceDelete() {
            const count = document.getElementById('bulk-count').textContent;
            Swal.fire({
                title: `Permanently delete ${count} records?`,
                html: '<span style="color:#dc2626;font-weight:bold;">WARNING:</span> This action cannot be undone!<br>The selected records will be permanently removed from the system.',
                icon: 'warning',
                iconColor: '#dc2626',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, permanently delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('bulk-force-delete-form').submit();
                }
            });
        }
    </script>

    {{-- Export Modal --}}
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <form id="exportForm" method="GET">
                    {{-- Hidden inputs to preserve filters --}}
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="office" value="{{ request('office') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="sex" value="{{ request('sex') }}">
                    <input type="hidden" name="vacant" value="{{ request('vacant') }}">

                    <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title" id="exportModalLabel" style="font-weight: 800; color: #312e81;"><i class="bi bi-download"></i> Export Data Options</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 24px;">
                        {{-- Record Status Filter --}}
                        <div style="margin-bottom:16px;padding:12px 16px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                            <p style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:8px;"><i class="bi bi-person-check-fill"></i> Record Status</p>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status_filter" value="active" id="sf_perm_active" checked>
                                    <label class="form-check-label" for="sf_perm_active" style="font-size:13px;font-weight:600;color:#16a34a;">Active Only</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status_filter" value="inactive" id="sf_perm_inactive">
                                    <label class="form-check-label" for="sf_perm_inactive" style="font-size:13px;font-weight:600;color:#dc2626;">Inactive Only</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status_filter" value="both" id="sf_perm_both">
                                    <label class="form-check-label" for="sf_perm_both" style="font-size:13px;font-weight:600;color:#2563eb;">Both</label>
                                </div>
                            </div>
                        </div>

                        <p style="font-size: 14px; color: #475569; margin-bottom: 16px;">Select the columns you want to include in your export:</p>

                        <div style="margin-bottom: 12px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('.export-cb').forEach(cb => cb.checked = true)" style="font-size: 11px; font-weight: 600;">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('.export-cb').forEach(cb => cb.checked = false)" style="font-size: 11px; font-weight: 600;">Deselect All</button>
                        </div>

                        <div class="row">
                            @php
                                $exportColumns = [
                                    'office_department'       => 'OFFICE',
                                    'item_no_new'                      => 'Item No.',
                                    'position_title'            => 'Position Title',
                                    'salary_grade'              => 'Salary Grade',
                                    'authorized_annual_salary'  => 'Authorized Annual Salary',
                                    'base_salary_amount'      => 'Actual Annual Salary',
                                    'step'                      => 'Step',
                                    'area_code'                 => 'Area Code',
                                    'area_type'                 => 'Area Type',
                                    'level'                     => 'Level',
                                    'last_name'                 => 'Last Name',
                                    'first_name'                => 'First Name',
                                    'middle_name'               => 'Middle Name',
                                    'name_extension'            => 'Name Extension',
                                    'sex'                       => 'Sex',
                                    'religion'                  => 'Religion',
                                    'date_of_birth'             => 'Birthday',
                                    'tin'                       => 'TIN',
                                    'date_original_appointment' => 'Date of Original Appointment',
                                    'date_last_promotion'       => 'Date of Last Promotion',
                                    'status'                    => 'Status',
                                    'civil_service_eligibility' => 'Civil Service Eligibility',
                                    'pwd'                       => 'PWD',
                                    'admin_charges'             => 'Comment / Annotation',
                                    'nature_of_separation'      => 'Nature of Separation',
                                    'indigenous_people'         => 'Indigenous People',
                                    'solo_parent'               => 'Solo Parent',
                                    'abolished'                 => 'Abolished',
                                    'dissolved'                 => 'Dissolved',
                                    'gsis_bp_number'            => 'GSIS BP Number',
                                    'position_classification'   => 'Position Classification',
                                    'employee_code'             => 'Employee No.',
                                ];
                            @endphp
                            @foreach($exportColumns as $key => $label)
                                <div class="col-md-4 col-sm-6" style="margin-bottom: 8px;">
                                    <div class="form-check">
                                        <input class="form-check-input export-cb" type="checkbox" name="columns[]" value="{{ $key }}" id="col_{{ $key }}" checked>
                                        <label class="form-check-label" for="col_{{ $key }}" style="font-size: 13px; color: #1e293b;">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 16px 24px;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 6px;">Cancel</button>
                        <button type="button" onclick="handleExport('{{ route('permanent.export.excel') }}', 'excel')" class="btn btn-success" style="font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 6px; background: #16a34a; border: none;">
                            <i class="bi bi-file-earmark-excel-fill"></i> Export to Excel
                        </button>
                        <button type="button" onclick="handleExport('{{ route('permanent.export.pdf') }}', 'pdf')" class="btn btn-danger" style="font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 6px; background: #dc2626; border: none;">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Export to PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        async function handleExport(url, type) {
            bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
            Swal.fire({
                title: 'Generating ' + (type === 'pdf' ? 'PDF' : 'Excel') + '...',
                html: 'This may take a moment depending on the number of records. Please do not close the window.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const form = document.getElementById('exportForm');
            const formData = new FormData(form);
            const queryParams = new URLSearchParams(formData).toString();
            
            try {
                const response = await fetch(`${url}?${queryParams}`, {
                    method: 'GET',
                    headers: { 'Accept': type === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }
                });

                if (!response.ok) throw new Error('Server returned an error while generating the file.');

                let filename = type === 'pdf' ? 'permanent-export.pdf' : 'permanent-export.xlsx';
                const disposition = response.headers.get('Content-Disposition');
                if (disposition && disposition.indexOf('filename=') !== -1) {
                    filename = disposition.split('filename=')[1].replace(/["']/g, '');
                }

                const blob = await response.blob();
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = downloadUrl;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Download Successful!',
                    text: 'Your ' + (type === 'pdf' ? 'PDF' : 'Excel') + ' file has been downloaded successfully.',
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-box-arrow-up-right"></i> Open File',
                    denyButtonText: '<i class="bi bi-eye-fill"></i> View File',
                    cancelButtonText: 'Close',
                    confirmButtonColor: '#10b981',
                    denyButtonColor: '#3b82f6',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(downloadUrl, '_blank');
                    } else if (result.isDenied) {
                        if (type === 'pdf') {
                            Swal.fire({
                                title: 'PDF Preview',
                                html: '<iframe src="' + downloadUrl + '" style="width:100%; height:70vh; border:none; border-radius:8px;"></iframe>',
                                width: '80%',
                                showCloseButton: true,
                                showConfirmButton: false
                            });
                        } else {
                            window.open(downloadUrl, '_blank');
                        }
                    } else {
                        window.URL.revokeObjectURL(downloadUrl);
                    }
                });
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Export Failed', text: 'There was an issue generating your file. ' + error.message });
            }
        }
    </script>

    {{-- Delete All Modal (Super Admin Only) --}}
    @if(auth()->user()->isSuperAdmin())
        <style>
            .perm-delete-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, .55);
                z-index: 9999;
                align-items: center;
                justify-content: center;
                backdrop-filter: blur(3px);
            }
            .perm-delete-overlay.active { display: flex; }
            .perm-delete-card {
                background: #fff;
                border-radius: 16px;
                width: 100%;
                max-width: 480px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, .18);
                padding: 32px;
                border-top: 5px solid #dc2626;
                animation: slideUpModal .22s ease;
            }
            @keyframes slideUpModal {
                from { opacity: 0; transform: translateY(20px); }
                to   { opacity: 1; transform: translateY(0); }
            }
        </style>
        <div id="perm-delete-all-overlay" class="perm-delete-overlay"
            onclick="if(event.target===this) this.classList.remove('active')">
            <div class="perm-delete-card">
                <div style="font-size:17px;font-weight:800;color:#dc2626;display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                    <i class="bi bi-exclamation-triangle-fill"></i> Delete ALL Permanent Data
                </div>
                <p style="font-size:14px;color:#4b5563;margin:12px 0 24px;line-height:1.65;">
                    <strong style="color:#dc2626;">WARNING:</strong> This action will
                    <strong>permanently delete EVERY</strong> Permanent, Co-Terminous and Elected employee record.
                    <br><br>
                    This action <strong>cannot be undone</strong> automatically.
                </p>
                <form method="POST" action="{{ route('permanent.delete-all') }}">
                    @csrf
                    @method('DELETE')
                    <div style="display:flex;gap:10px;justify-content:flex-end;">
                        <button type="button"
                            style="padding:10px 20px;background:#f1f5f9;color:#475569;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;"
                            onclick="document.getElementById('perm-delete-all-overlay').classList.remove('active')">
                            Cancel
                        </button>
                        <button type="submit"
                            style="padding:10px 22px;background:#dc2626;color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;">
                            <i class="bi bi-trash3-fill"></i> Yes, Delete All
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-dashboard-app>
