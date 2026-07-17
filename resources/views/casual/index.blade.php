<x-dashboard-app>
    <style>
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

        /* Filter bar */
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

        /* Table */
        .cas-table-wrap {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
        }

        .cas-table-scroll {
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 350px);
            min-height: 300px;
            /* Hide native horizontal scrollbar — replaced by custom strip below */
            scrollbar-width: none;
        }
        /* Hide only the horizontal scrollbar in WebKit */
        .cas-table-scroll::-webkit-scrollbar {
            width: 12px;  /* keep vertical */
            height: 0;    /* hide horizontal */
        }
        .cas-table-scroll::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 10px; }
        .cas-table-scroll::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 10px; border: 2px solid #f3f4f6; }
        .cas-table-scroll::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        .cas-table-scroll::-webkit-scrollbar-thumb:active { background: #374151; }

        /* ── Excel-style right-quarter horizontal scrollbar ────────── */
        .cas-hscroll-bar {
            display: flex;
            justify-content: flex-start;
            background: #f3f4f6;
            border-top: 1px solid #e5e7eb;
        }
        .cas-hscroll-inner {
            width: 25%;
            overflow-x: auto;
            overflow-y: hidden;
            height: 14px;
            scrollbar-width: thin;
            scrollbar-color: #9ca3af #f3f4f6;
        }
        .cas-hscroll-inner::-webkit-scrollbar { height: 14px; }
        .cas-hscroll-inner::-webkit-scrollbar-track { background: #f3f4f6; }
        .cas-hscroll-inner::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 10px; border: 2px solid #f3f4f6; }
        .cas-hscroll-inner::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        .cas-hscroll-ghost { height: 1px; }

        .cas-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 900px;
        }

        .cas-table thead tr {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .cas-table th {
            padding: 10px 12px;
            text-align: left;
            white-space: nowrap;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: rgba(255, 255, 255, .75);
            border-bottom: 2px solid rgba(255, 255, 255, .08);
            border-right: 1px solid rgba(255, 255, 255, .08);
            cursor: pointer;
        }

        .cas-table th:last-child {
            border-right: 0;
        }

        .cas-table tbody tr:nth-child(even) td {
            background: #f0f9ff;
        }

        .cas-table tbody tr:hover td {
            background: #e0f2fe !important;
            transition: background .1s;
        }

        .cas-table td {
            padding: 9px 12px;
            font-size: 12px;
            color: #374151;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            white-space: nowrap;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cas-table td:last-child {
            border-right: 0;
        }

        .cas-table tbody tr:last-child td {
            border-bottom: 0;
        }

        /* Sticky col #1 */
        .cas-table td:first-child,
        .cas-table th:first-child {
            position: sticky;
            left: 0;
            z-index: 1;
            background: #fff;
            border-right: 2px solid #e5e7eb !important;
            min-width: 60px;
        }

        .cas-table thead th:first-child {
            background: #1e3a8a;
        }

        .cas-table tbody tr:nth-child(even) td:first-child {
            background: #f0f9ff;
        }

        .cas-table tbody tr:hover td:first-child {
            background: #e0f2fe !important;
        }

        /* Badges */
        .badge-vacant {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-filled {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            background: #d1fae5;
            color: #065f46;
        }

        .badge-sg {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            background: #e0f2fe;
            color: #0369a1;
        }

        /* Action buttons */
        .row-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 24px;
            padding: 0 8px;
            gap: 4px;
            border-radius: 5px;
            font-size: 10px;
            font-weight: 700;
            text-decoration: none;
            transition: all .12s;
            border: 1px solid transparent;
            cursor: pointer;
            white-space: nowrap;
        }

        .row-btn.edit {
            background: #fffbeb;
            color: #d97706;
            border-color: #fde68a;
        }

        .row-btn.edit:hover {
            background: #d97706;
            color: #fff;
        }

        .row-btn.del {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .row-btn.del:hover {
            background: #dc2626;
            color: #fff;
        }

        .row-btn.arc {
            background: #f5f3ff;
            color: #7c3aed;
            border-color: #ddd6fe;
        }

        .row-btn.arc:hover {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        /* Table footer */
        .cas-table-footer {
            padding: 12px 18px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .cas-count {
            font-size: 12px;
            color: #6b7280;
        }

        /* Flash */
        .flash-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            border-radius: 10px;
            padding: 11px 16px;
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .flash-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 10px;
            padding: 11px 16px;
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Import modal */
        .import-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(6px);
            z-index: 9000;
            justify-content: center;
            align-items: center;
        }

        .import-overlay.active {
            display: flex;
        }

        .import-card {
            background: #fff;
            border-radius: 20px;
            padding: 44px 48px;
            max-width: 680px;
            width: calc(100% - 32px);
            box-shadow: 0 25px 60px rgba(0, 0, 0, .18);
        }

        .import-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .import-sub {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 24px;
            line-height: 1.7;
        }

        .import-drop {
            border: 2px dashed #93c5fd;
            border-radius: 12px;
            padding: 36px 28px;
            text-align: center;
            background: #eff6ff;
            cursor: pointer;
            transition: all .2s;
            white-space: normal;
            word-break: break-word;
        }

        .cas-btn.filter:hover {
            border-color: #3b82f6;
            color: #3b82f6;
        }

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

        .import-drop:hover {
            border-color: #3b82f6;
            background: #e0f2fe;
        }

        .import-drop i {
            font-size: 40px;
            color: #3b82f6;
            margin-bottom: 10px;
            display: block;
        }

        .import-drop strong {
            font-size: 16px;
            display: block;
            margin-bottom: 6px;
            color: #1e293b;
        }

        .import-drop p {
            font-size: 14px;
            color: #64748b;
            margin: 0;
            white-space: normal;
            word-break: break-word;
        }

        .import-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            justify-content: flex-start;
        }

        .btn-import-submit {
            padding: 12px 28px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .btn-import-submit:hover {
            opacity: .9;
        }

        .btn-import-cancel {
            padding: 12px 20px;
            background: #f1f5f9;
            color: #475569;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-import-cancel:hover {
            background: #e2e8f0;
        }
    </style>

    {{-- Hero --}}
    <div class="cas-hero">
        <div class="cas-hero-inner">
            <div>
                <h1><i class="bi bi-person-lines-fill me-2"></i>Casual Employees</h1>
                <p>Casual personnel inventory — all {{ number_format($total) }} entries</p>
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
        $casGadTotal = $maleCount + $femaleCount;
        $casFemalePct = $casGadTotal > 0 ? round($femaleCount / $casGadTotal * 100, 1) : 0;
        $casGadOk = $casFemalePct >= 40 && $casFemalePct <= 60;
    @endphp
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px;">
        <x-stat-card icon="bi-person-lines-fill" color="indigo" label="Total Casual" :value="$total"
            sub="Active casual employees"
            compliance="CSC / RA 6656"
            analysis="Casual employees are governed by RA 6656 and CSC MC 40, s.1998. Employment is project-based or seasonal in nature." />

        <x-stat-card icon="bi-gender-male" color="blue" label="Male" :value="$maleCount"
            :pct="$casGadTotal > 0 ? round($maleCount/$casGadTotal*100,1) : 0"
            sub="of sex-tagged casuals"
            compliance="RA 9710 GAD"
            analysis="Male casual employees. GAD monitoring per RA 9710 applies to all employment categories including casual staff." />

        <x-stat-card icon="bi-gender-female" color="pink" label="Female" :value="$femaleCount"
            :pct="$casFemalePct"
            sub="of sex-tagged casuals"
            compliance="RA 9710 GAD"
            :alert="!$casGadOk"
            analysis="{{ $casGadOk ? 'GAD compliant: '.$casFemalePct.'% female ratio among casual staff meets the 40–60% target (RA 9710).' : 'ATTENTION: '.$casFemalePct.'% female ratio is outside the 40–60% GAD target (RA 9710 §12).' }}" />

        <x-stat-card icon="bi-calendar-x" color="orange" label="Vacant Slots" :value="$vacantCount ?? 0"
            sub="Unfilled casual positions"
            compliance="CSC Rules"
            analysis="Casual vacancies should be filled promptly to ensure continuity of service. Renewal of existing contracts requires office endorsement each month." />
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('casual.index') }}" id="cas-search-form">
        <div class="cas-filter" style="display: flex; flex-direction: column; gap: 12px; align-items: stretch; padding: 14px 18px; margin-bottom: 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, .04);">
            <!-- Top Row: Inputs + Search + Reset -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <input type="text" name="search" value="{{ request('search') }}" class="cas-input" placeholder="🔍 Search Name / Position..." autocomplete="off">
                
                <select name="office_department" class="cas-select">
                    <option value="">— All Offices —</option>
                    @foreach(($offices ?? collect([])) as $office)
                        <option value="{{ $office }}" {{ request('office_department') === $office ? 'selected' : '' }}>{{ $office }}</option>
                    @endforeach
                </select>

                <select name="office" class="cas-select">
                    <option value="">— All Detailed Units —</option>
                    @foreach(($detailedUnits ?? collect([])) as $unit)
                        <option value="{{ $unit }}" {{ request('office') === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                    @endforeach
                </select>

                <select name="detail" class="cas-select">
                    <option value="">— Detailed / Reassigned —</option>
                    @foreach($detailList as $detail)
                        <option value="{{ $detail }}" {{ request('detail') === $detail ? 'selected' : '' }}>{{ Str::limit($detail, 40) }}</option>
                    @endforeach
                </select>

                <select name="sex" class="cas-select">
                    <option value="">— SEX —</option>
                    <option value="M" {{ request('sex') === 'M' ? 'selected' : '' }}>M</option>
                    <option value="F" {{ request('sex') === 'F' ? 'selected' : '' }}>F</option>
                </select>

                <select name="vacancy_status" class="cas-select">
                    <option value="">— Vacancy Status —</option>
                    <option value="vacant" {{ request('vacancy_status') === 'vacant' ? 'selected' : '' }}>Vacant Only</option>
                    <option value="filled" {{ request('vacancy_status') === 'filled' ? 'selected' : '' }}>Filled Only</option>
                </select>
                
                <button type="submit" class="cas-btn primary"><i class="bi bi-search"></i> Search</button>
                <a href="{{ route('casual.index') }}" class="cas-btn reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                <select name="per_page" class="cas-select" style="min-width: 80px;" onchange="this.form.submit()">
                    <option value="20" {{ request('per_page', 50) == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>

            <!-- Bottom Row: Actions (Far Left) -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-start;">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <a href="{{ route('casual.create') }}" class="cas-btn add"><i class="bi bi-plus-lg"></i> Add Record</a>
                    <button type="button" class="cas-btn import" onclick="document.getElementById('import-overlay').classList.add('active')"><i class="bi bi-upload"></i> Import Excel</button>
                @endif
                @unless(auth()->user()->isViewer())
                <a href="{{ route('casual.import.history') }}" class="cas-btn" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;"><i class="bi bi-clock-history"></i> Import History</a>
                @endunless
                <button type="button" class="cas-btn" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;cursor:pointer;" onclick="new bootstrap.Modal(document.getElementById('exportModal')).show()"><i class="bi bi-file-earmark-arrow-down-fill"></i> Export Settings</button>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <button type="button" class="cas-btn" id="toggle-select-multiple" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;cursor:pointer;"><i class="bi bi-ui-checks-grid"></i> Select Multiple</button>
                @endif
                @if(auth()->user()->isSuperAdmin())
                    <button type="button" id="delete-all-btn" style="display:inline-flex;align-items:center;gap:5px;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;background:#ffffff;color:#334155;border:1px solid #cbd5e1;transition:all .15s;" onclick="document.getElementById('delete-all-overlay').classList.add('active')"><i class="bi bi-trash3-fill"></i> Delete All Data</button>
                @endif
            </div>
        </div>
    </form>

    {{-- Bulk Action Bar --}}
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
    <div id="bulk-action-bar" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; align-items: center; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="background: #3b82f6; color: white; border-radius: 9999px; padding: 2px 10px; font-size: 12px; font-weight: 700;" id="bulk-count">0</span>
            <span style="font-size: 13px; font-weight: 600; color: #334155;">records selected</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <form id="bulk-archive-form" method="POST" action="{{ route('casual.bulk-archive') }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="type" value="casual">
                <div id="bulk-ids-container"></div>
                <button type="button" onclick="confirmBulkArchive()" class="cas-btn" style="background: #7c3aed; color: #fff;">
                    <i class="bi bi-archive-fill"></i> Archive Selected
                </button>
            </form>
            @if(auth()->user()->isSuperAdmin())
                <form id="bulk-force-delete-form" method="POST" action="{{ route('casual.bulk-force-delete') }}" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="type" value="casual">
                    <div id="bulk-ids-container-fd"></div>
                    <button type="button" onclick="confirmBulkForceDelete()" class="cas-btn" style="background: #dc2626; color: #fff;">
                        <i class="bi bi-trash-fill"></i> Permanently Delete
                    </button>
                </form>
            @endif
        </div>
    </div>
    @endif

    @include('partials.table-column-controls', ['tabKey' => 'casual'])

    {{-- Table --}}
    <div class="cas-table-wrap">
        <div class="cas-table-scroll" id="cas-table-scroll">
            <table class="cas-table">
                <thead>
                    <tr>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                            <th rowspan="2" class="checkbox-col" style="display:none; text-align:center; padding-left: 16px;">
                                <input type="checkbox" id="selectAll" style="cursor:pointer; width: 14px; height: 14px;">
                            </th>
                        @endif
                        <th rowspan="2" style="width: 40px; text-align: center;">#</th>
                        <th rowspan="2" data-col="office_department" data-sort data-label="OFFICE"><x-sort-link column="office_department">OFFICE</x-sort-link></th>
                        <th colspan="4" style="text-align:center;">NAME</th>
                        <th rowspan="2" data-col="position_title" data-sort data-label="POSITION"><x-sort-link column="position_title">POSITION</x-sort-link></th>
                        <th rowspan="2" style="text-align:center;" data-col="date_of_birth" data-sort data-label="DATE OF BIRTH"><x-sort-link column="date_of_birth">DATE OF BIRTH</x-sort-link></th>
                        <th rowspan="2" style="text-align:center;" data-col="sex" data-sort data-label="SEX"><x-sort-link column="sex">SEX</x-sort-link></th>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                            <th rowspan="2" style="text-align:center;">ACTIONS</th>
                        @endif
                    </tr>
                    <tr>
                        <th style="background:#1a337a;font-size:9px;" data-col="last_name" data-sort data-label="LAST NAME"><x-sort-link column="last_name">LAST NAME</x-sort-link></th>
                        <th style="background:#1a337a;font-size:9px;" data-col="first_name" data-sort data-label="FIRST NAME"><x-sort-link column="first_name">FIRST NAME</x-sort-link></th>
                        <th style="background:#1a337a;font-size:9px;" data-col="middle_name" data-sort data-label="MIDDLE NAME"><x-sort-link column="middle_name">MIDDLE NAME</x-sort-link></th>
                        <th style="background:#1a337a;font-size:9px;" data-col="name_extension" data-label="SUFFIX">SUFFIX</th>
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

                            <td data-col="office_department" title="{{ $r->office_department }}" style="font-size:11px;color:#64748b;">{{ $r->office_department }}</td>

                            <td data-col="last_name" style="font-weight:700;color:#0f172a;">
                                @if($r->is_vacant)
                                    <span style="color:#dc2626;font-style:italic;font-size:10px;">VACANT</span>
                                @else
                                    {{ strtoupper($r->last_name) }}
                                @endif
                            </td>

                            <td data-col="first_name" style="color:#374151;">{{ $r->first_name }}</td>

                            <td data-col="middle_name" style="font-size:11px;color:#64748b;">{{ $r->middle_name ?: '—' }}</td>

                            <td data-col="name_extension" style="font-size:11px;color:#64748b;">{{ $r->name_extension ?: '—' }}</td>

                            <td data-col="position_title" title="{{ $r->position_title }}" style="font-weight:600;color:#0f172a;">
                                <div class="pos-title-text" style="font-size:12px;">{{ $r->position_title }}</div>
                                <div style="font-size:11px;color:#64748b;">Item #{{ $r->item_no_new ?? $r->item_no_old ?? 'N/A' }}</div>
                            </td>

                            <td data-col="date_of_birth" style="color:#6b7280;text-align:center;font-size:11px;">
                                {{ $r->date_of_birth ? \Carbon\Carbon::parse($r->date_of_birth)->format('M d, Y') : '—' }}
                            </td>

                            <td data-col="sex" style="text-align:center;">
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
                                    <a href="{{ route('employees.profile', ['type' => 'casual', 'id' => $r->id]) }}" class="row-btn" style="background: #fce7f3; color: #be185d; border-color: #fbcfe8;" title="201 Profile">
                                        <i class="bi bi-person-vcard"></i> 201 Profile
                                    </a>
                                    <a href="{{ route('casual.edit', $r) }}" class="row-btn edit">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('casual.archive', $r) }}"
                                        id="form-arc-cas-{{ $r->id }}" class="d-inline">
                                        @csrf
                                        <button type="button" onclick="confirmCasualArchive('{{ $r->id }}')" class="row-btn arc">
                                            <i class="bi bi-archive-fill"></i> Archive
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:40px;color:#9ca3af;font-size:14px;">
                                <i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:10px;"></i>
                                No casual records found.
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                                    <a href="{{ route('casual.create') }}" style="color:#be185d;font-weight:700;">Add one now
                                        →</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Excel-style right-quarter horizontal scrollbar --}}
        <div class="cas-hscroll-bar">
            <div class="cas-hscroll-inner" id="cas-hscroll-inner">
                <div class="cas-hscroll-ghost" id="cas-hscroll-ghost"></div>
            </div>
        </div>

        <div class="cas-table-footer">
            <span class="cas-count">Showing {{ number_format($records->total()) }} record(s)</span>
            <div style="font-size:11px;color:#94a3b8;">
                Male: <strong>{{ $maleCount }}</strong> &nbsp;|&nbsp;
                Female: <strong>{{ $femaleCount }}</strong>
            </div>
        </div>
    </div>

    {{-- Import Modal --}}
    <div id="import-overlay" class="import-overlay" onclick="if(event.target===this) this.classList.remove('active')">
        <div class="import-card">
            <div class="import-title"><i class="bi bi-upload" style="color:#be185d;"></i> Import Casual Data</div>
            <div class="import-sub">
                Upload your Casual Excel file. Supports two formats:<br>
                <strong>① Simple Template</strong> (download below) &nbsp;|&nbsp;
                <strong>② Native Plantilla Excel</strong> (auto-detected)
            </div>

            <form method="POST" action="{{ route('casual.import') }}" enctype="multipart/form-data" id="import-form">
                @csrf
                <label for="import-file" class="import-drop" id="import-drop">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    <strong>Click to select or drag & drop</strong>
                    <p>Accepted: .xlsx, .xls, .csv — Max 10MB</p>
                    <p id="import-filename" style="color:#be185d;font-weight:700;margin-top:6px;"></p>
                </label>
                <input type="file" name="import_file" id="import-file" accept=".xlsx,.xls,.csv" style="display:none;"
                    onchange="document.getElementById('import-filename').textContent=this.files[0]?.name||''">

                <div class="import-actions">
                    <button type="button" class="btn-import-cancel"
                        onclick="document.getElementById('import-overlay').classList.remove('active')">
                        Cancel
                    </button>
                    <a href="{{ route('casual.template') }}" class="btn-import-cancel" style="text-decoration:none;">
                        <i class="bi bi-download"></i> Download Template
                    </a>
                    <button type="submit" class="btn-import-submit">
                        <i class="bi bi-cloud-upload-fill"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- Delete All Modal (Super Admin Only) --}}
    @if(auth()->user()->isSuperAdmin())
        <div id="delete-all-overlay" class="import-overlay"
            onclick="if(event.target===this) this.classList.remove('active')">
            <div class="import-card" style="border-top: 5px solid #dc2626;">
                <div class="import-title" style="color: #dc2626;">
                    <i class="bi bi-exclamation-triangle-fill"></i> Delete ALL Casual Data
                </div>
                <div class="import-sub" style="color: #4b5563; font-size: 14px; margin-bottom: 24px;">
                    <strong style="color: #dc2626;">WARNING:</strong> This action is for testing purposes only.
                    It will permanently delete <strong>EVERY</strong> casual employee record
                    and all of their synced "All Data" entries.
                    <br><br>
                    This action cannot be undone automatically.
                </div>

                <form method="POST" action="{{ route('casual.delete-all') }}">
                    @csrf
                    @method('DELETE')
                    <div class="import-actions">
                        <button type="button" class="btn-import-cancel" style="background:#f1f5f9; color:#475569;"
                            onclick="document.getElementById('delete-all-overlay').classList.remove('active')">
                            Cancel
                        </button>
                        <button type="submit" class="btn-import-submit" style="background:#dc2626; color:white;">
                            <i class="bi bi-trash3-fill"></i> Yes, Delete All
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Export Modal --}}
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <form id="exportForm" method="GET">
                    {{-- Hidden inputs to preserve filters --}}
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="office" value="{{ request('office_department') }}">
                    <input type="hidden" name="sex" value="{{ request('sex') }}">
                    <input type="hidden" name="vacant" value="{{ request('vacant') }}">

                    <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title" id="exportModalLabel" style="font-weight: 800; color: #831843;"><i class="bi bi-download"></i> Export Data Options</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 24px;">
                        {{-- Record Status Filter --}}
                        <div style="margin-bottom:16px;padding:12px 16px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                            <p style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:8px;"><i class="bi bi-person-check-fill"></i> Record Status</p>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status_filter" value="active" id="sf_cas_active" checked>
                                    <label class="form-check-label" for="sf_cas_active" style="font-size:13px;font-weight:600;color:#16a34a;">Active Only</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status_filter" value="inactive" id="sf_cas_inactive">
                                    <label class="form-check-label" for="sf_cas_inactive" style="font-size:13px;font-weight:600;color:#dc2626;">Inactive Only</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status_filter" value="both" id="sf_cas_both">
                                    <label class="form-check-label" for="sf_cas_both" style="font-size:13px;font-weight:600;color:#2563eb;">Both</label>
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
                                    'office' => 'OFFICE',
                                    'item_no_old' => 'ITEM NO. (OLD)',
                                    'item_no_new' => 'ITEM NO. (NEW)',
                                    'position_title' => 'POSITION TITLE',
                                    'is_vacant' => 'VACANT?',
                                    'last_name' => 'LAST NAME',
                                    'first_name' => 'FIRST NAME',
                                    'middle_initial' => 'MIDDLE NAME',
                                    'name_extension' => 'SUFFIX',
                                    'legislative_district' => 'LEGISLATIVE DISTRICT',
                                    'sg_current' => 'SALARY GRADE (CURRENT)',
                                    'step_current' => 'STEP (CURRENT)',
                                    'salary_current' => 'ANNUAL SALARY (CURRENT)',
                                    'sg_proposed' => 'SALARY GRADE (PROPOSED)',
                                    'step_proposed' => 'STEP (PROPOSED)',
                                    'salary_proposed' => 'ANNUAL SALARY (PROPOSED)',
                                    'increase_decrease' => 'INCREASE / DECREASE',
                                    'previous_rate' => 'PREVIOUS RATE',
                                    'current_rate' => 'CURRENT RATE (MONTHLY)',
                                    'sex' => 'SEX',
                                    'date_of_birth' => 'DATE OF BIRTH',
                                    'first_day_of_service' => 'FIRST DAY OF SERVICE',
                                    'eligibility' => 'ELIGIBILITY',
                                    'annotation' => 'ANNOTATION',
                                    'employee_code' => 'EMPLOYEE CODE',
                                    'address' => 'ADDRESS',
                                    'solo_parent' => 'SOLO PARENT',
                                    'ip_community_membership' => 'IP COMMUNITY MEMBERSHIP'
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
                        <button type="button" onclick="handleExport('{{ route('casual.export.excel') }}', 'excel')" class="btn btn-success" style="font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 6px; background: #16a34a; border: none;">
                            <i class="bi bi-file-earmark-excel-fill"></i> Export to Excel
                        </button>
                        <button type="button" onclick="handleExport('{{ route('casual.export.pdf') }}', 'pdf')" class="btn btn-danger" style="font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 6px; background: #dc2626; border: none;">
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

                let filename = type === 'pdf' ? 'casual-export.pdf' : 'casual-export.xlsx';
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

    <script>
        // Drag & drop on import area
        const dropArea = document.getElementById('import-drop');
        const fileInput = document.getElementById('import-file');
        if (dropArea) {
            dropArea.addEventListener('dragover', e => { e.preventDefault(); dropArea.style.borderColor = '#be185d'; });
            dropArea.addEventListener('dragleave', () => { dropArea.style.borderColor = '#f9a8d4'; });
            dropArea.addEventListener('drop', e => {
                e.preventDefault();
                dropArea.style.borderColor = '#f9a8d4';
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    document.getElementById('import-filename').textContent = e.dataTransfer.files[0].name;
                }
            });
        }
    </script>

    <div style="padding: 12px 18px; border-top: 1px solid #f1f5f9; background: #fff; border-radius: 0 0 12px 12px;">
        {{ $records->links('pagination::bootstrap-5') }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ── Excel-style right-quarter horizontal scrollbar sync (Casual) ──
        (function () {
            const scrollEl = document.getElementById('cas-table-scroll');
            const hbarInner = document.getElementById('cas-hscroll-inner');
            const hbarGhost = document.getElementById('cas-hscroll-ghost');

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
        function confirmCasualArchive(id) {
            Swal.fire({
                title: 'Archive this Casual record?',
                html: 'This Casual employee record will be moved to the <b>Archives</b>.<br>You can restore it later.',
                icon: 'info',
                iconColor: '#7c3aed',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, archive it'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-arc-cas-' + id).submit();
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
</x-dashboard-app>