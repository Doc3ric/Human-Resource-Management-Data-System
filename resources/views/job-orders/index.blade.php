<x-dashboard-app>
    {{-- ══════════════════════════════════════════════════════════
    JOB ORDER INVENTORY — Index Page
    ══════════════════════════════════════════════════════════ --}}

    <style>
        /* ── Page-level overrides ─────────────────────────────── */
        .jo-page {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Stats bar */
        .jo-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }

        .jo-stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .jo-stat-label {
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .jo-stat-value {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
        }

        .jo-stat-sub {
            font-size: 11px;
            color: #64748b;
        }

        /* Filter bar */
        .jo-filter-bar {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }

        .jo-filter-bar .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 150px;
            flex: 1;
        }

        .jo-filter-bar label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .jo-filter-bar select,
        .jo-filter-bar input[type="text"] {
            height: 36px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 0 10px;
            font-size: 13px;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            transition: border .15s;
        }

        .jo-filter-bar select:focus,
        .jo-filter-bar input[type="text"]:focus {
            border-color: #3b82f6;
            background: #fff;
        }

        .filter-btn-group {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .btn-filter {
            height: 36px;
            padding: 0 18px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-filter-apply {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
        }

        .btn-filter-apply:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .btn-filter-clear {
            background: #f1f5f9;
            color: #475569;
            border: 1.5px solid #e2e8f0;
        }

        .btn-filter-clear:hover {
            background: #e2e8f0;
        }

        /* Action row */
        .jo-action-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .jo-action-row .jo-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-create {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
            box-shadow: 0 2px 8px rgba(16, 185, 129, .25);
        }

        .btn-create:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
        }

        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
            border: none;
        }

        .btn-export-pdf {
            background: #fef2f2;
            color: #dc2626;
            border: 1.5px solid #fecaca;
        }

        .btn-export-pdf:hover {
            background: #fee2e2;
        }

        /* Table */
        .jo-table-wrap {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow-x: auto;
            overflow-y: auto;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
            max-height: calc(100vh - 350px);
            min-height: 300px;
            /* Hide native horizontal scrollbar — replaced by custom strip below */
            scrollbar-width: none;
        }
        /* Hide only the horizontal scrollbar in WebKit */
        .jo-table-wrap::-webkit-scrollbar {
            width: 12px;  /* keep vertical */
            height: 0;    /* hide horizontal */
        }
        .jo-table-wrap::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 10px; }
        .jo-table-wrap::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 10px; border: 2px solid #f3f4f6; }
        .jo-table-wrap::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        .jo-table-wrap::-webkit-scrollbar-thumb:active { background: #374151; }

        /* ── Excel-style right-quarter horizontal scrollbar ────────── */
        .jo-hscroll-bar {
            display: flex;
            justify-content: flex-end;
            background: #f3f4f6;
            border-top: 1px solid #e2e8f0;
            border-radius: 0 0 12px 12px;
        }
        .jo-hscroll-inner {
            width: 25%;
            overflow-x: auto;
            overflow-y: hidden;
            height: 14px;
            scrollbar-width: thin;
            scrollbar-color: #9ca3af #f3f4f6;
        }
        .jo-hscroll-inner::-webkit-scrollbar { height: 14px; }
        .jo-hscroll-inner::-webkit-scrollbar-track { background: #f3f4f6; }
        .jo-hscroll-inner::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 10px; border: 2px solid #f3f4f6; }
        .jo-hscroll-inner::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        .jo-hscroll-ghost { height: 1px; }

        .jo-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            min-width: 1400px;
        }

        .jo-table thead tr th {
            background: #0f172a;
            padding: 10px 12px;
            text-align: center;
            font-size: 9.5px;
            font-weight: 800;
            color: rgba(255, 255, 255, .75);
            text-transform: uppercase;
            letter-spacing: .6px;
            border-bottom: 2px solid rgba(255, 255, 255, .08);
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .jo-table thead tr.sub-header th {
            background: #334155;
            font-size: 9px;
            font-weight: 700;
            color: rgba(255, 255, 255, .6);
            padding: 5px 8px;
            border-bottom: 2px solid rgba(255, 255, 255, .08);
        }

        .jo-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background .1s;
        }

        .jo-table tbody tr:last-child {
            border-bottom: none;
        }

        .jo-table tbody tr:hover {
            background: #f8fafc;
        }

        .jo-table tbody td {
            padding: 8px 8px;
            vertical-align: middle;
            color: #334155;
            text-align: center;
        }

        .jo-table tbody td.text-left {
            text-align: left;
        }

        .jo-table .check-mark {
            font-size: 14px;
            color: #059669;
            font-weight: 900;
        }

        .jo-table .no-record {
            color: #94a3b8;
            font-size: 10px;
        }

        .gender-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 800;
        }

        .gender-m {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .gender-f {
            background: #fce7f3;
            color: #be185d;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 8px;
            height: 28px;
            width: auto;
            gap: 4px;
            border-radius: 7px;
            border: none;
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
            transition: all .15s;
            text-decoration: none;
        }

        .action-edit {
            background: #eff6ff;
            color: #2563eb;
        }

        .action-edit:hover {
            background: #dbeafe;
        }

        .action-delete {
            background: #fef2f2;
            color: #dc2626;
        }

        .action-delete:hover {
            background: #fee2e2;
        }

        .action-archive {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .action-archive:hover {
            background: #ede9fe;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 14px;
            font-weight: 600;
            margin: 0;
        }

        .empty-state small {
            font-size: 12px;
        }

        /* Import modal */
        .import-modal-bg {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(3px);
        }

        .import-modal-bg.open {
            display: flex;
        }

        .import-modal {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .18);
            padding: 28px 32px;
            animation: slideUpModal .22s ease;
        }

        @keyframes slideUpModal {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .import-modal-title {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }

        .import-modal-sub {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 20px;
        }

        .import-drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 28px 18px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all .15s;
            margin-bottom: 16px;
        }

        .import-drop-zone:hover,
        .import-drop-zone.drag-over {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .import-drop-zone .drop-icon {
            font-size: 32px;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .import-drop-zone p {
            margin: 0;
            font-size: 13px;
            color: #475569;
            font-weight: 600;
        }

        .import-drop-zone small {
            font-size: 11px;
            color: #94a3b8;
        }

        .import-file-name {
            font-size: 12px;
            color: #0369a1;
            font-weight: 600;
            margin-top: 6px;
            display: none;
        }

        #import-file-input {
            display: none;
        }

        .btn-import-submit {
            width: 100%;
            padding: 11px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-import-submit:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
        }

        .btn-import-submit:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .import-template-link {
            display: block;
            text-align: center;
            margin-top: 12px;
            font-size: 12px;
            color: #0369a1;
            text-decoration: none;
            font-weight: 600;
        }

        .import-template-link:hover {
            text-decoration: underline;
        }
    </style>

    <div class="jo-page">

        {{-- Flash messages --}}
        @if(session('success'))
            <div
                style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div
                style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            </div>
        @endif

        {{-- ── STATS BAR ── --}}
        <div class="jo-stats-grid">
            <div class="jo-stat-card">
                <div class="jo-stat-label">Total JO</div>
                <div class="jo-stat-value">{{ $total }}</div>
                <div class="jo-stat-sub">employees</div>
            </div>
            <div class="jo-stat-card">
                <div class="jo-stat-label">Male</div>
                <div class="jo-stat-value" style="color:#1d4ed8;">{{ $maleCount }}</div>
                <div class="jo-stat-sub">{{ $total > 0 ? round(($maleCount / $total) * 100) : 0 }}%</div>
            </div>
            <div class="jo-stat-card">
                <div class="jo-stat-label">Female</div>
                <div class="jo-stat-value" style="color:#be185d;">{{ $femaleCount }}</div>
                <div class="jo-stat-sub">{{ $total > 0 ? round(($femaleCount / $total) * 100) : 0 }}%</div>
            </div>
            @foreach($byOffice->take(4) as $office => $count)
                <div class="jo-stat-card">
                    <div class="jo-stat-label">{{ $office ?: 'No Office' }}</div>
                    <div class="jo-stat-value" style="color:#0369a1;">{{ $count }}</div>
                    <div class="jo-stat-sub">JO employees</div>
                </div>
            @endforeach
        </div>

        {{-- ── FILTER BAR ── --}}
        <form method="GET" action="{{ route('job-orders.index') }}" class="jo-filter-bar" id="jo-filter-form">
            <div class="filter-group">
                <label>Search Name / Position</label>
                <input type="text" name="search" id="jo-search" value="{{ request('search') }}" placeholder="Search...">
            </div>
            <div class="filter-group">
                <label>Charges / Office Code</label>
                <select name="charges" id="jo-charges">
                    <option value="">All Charges</option>
                    @foreach($chargesList as $c)
                        <option value="{{ $c }}" {{ request('charges') == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Office</label>
                <select name="office" id="jo-office">
                    <option value="">All Offices</option>
                    @foreach($offices as $o)
                        <option value="{{ $o }}" {{ request('office') == $o ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Nature of Work</label>
                <select name="nature_of_work" id="jo-nature">
                    <option value="">All Types</option>
                    @foreach($natures as $n)
                        <option value="{{ $n }}" {{ request('nature_of_work') == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Gender</label>
                <select name="gender" id="jo-gender">
                    <option value="">All</option>
                    <option value="M" {{ request('gender') == 'M' ? 'selected' : '' }}>Male</option>
                    <option value="F" {{ request('gender') == 'F' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div class="filter-btn-group">
                <button type="submit" class="btn-filter btn-filter-apply" id="btn-apply-filter">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                <a href="{{ route('job-orders.index') }}" class="btn-filter btn-filter-clear">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>

        {{-- ── ACTION ROW ── --}}
        <div class="jo-action-row">
            <div class="jo-title">
                <i class="bi bi-file-earmark-person-fill" style="color:#0369a1;"></i>
                Job Order Inventory
                <span
                    style="font-size:12px;font-weight:600;color:#64748b;background:#f1f5f9;padding:3px 10px;border-radius:20px;">{{ $total }}
                    record(s)</span>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="btn-export" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;cursor:pointer;" onclick="new bootstrap.Modal(document.getElementById('exportModal')).show()">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i> Export Settings
                </button>
                <button type="button" class="btn-export" id="btn-open-import"
                    style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;"
                    onclick="document.getElementById('import-modal-bg').classList.add('open')">
                    <i class="bi bi-upload"></i> Import Excel
                </button>
                <a href="{{ route('job-orders.import.history') }}" class="btn-export"
                    style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;">
                    <i class="bi bi-clock-history"></i> Import History
                </a>
                <a href="{{ route('job-orders.create') }}" class="btn-create" id="btn-create-jo">
                    <i class="bi bi-plus-lg"></i> Add JO Record
                </a>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <button type="button" class="btn-export" id="toggle-select-multiple" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;">
                        <i class="bi bi-ui-checks-grid"></i> Select Multiple
                    </button>
                @endif
            </div>
        </div>

        {{-- Bulk Action Bar --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
        <div id="bulk-action-bar" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-top: 16px; margin-bottom: 16px; align-items: center; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="background: #3b82f6; color: white; border-radius: 9999px; padding: 2px 10px; font-size: 12px; font-weight: 700;" id="bulk-count">0</span>
                <span style="font-size: 13px; font-weight: 600; color: #334155;">records selected</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <form id="bulk-archive-form" method="POST" action="{{ route('job-orders.bulk-archive') }}" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="type" value="job_orders">
                    <div id="bulk-ids-container"></div>
                    <button type="button" onclick="confirmBulkArchive()" style="display: inline-flex; align-items: center; gap: 5px; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; border: none; cursor: pointer; background: #7c3aed; color: #fff;">
                        <i class="bi bi-archive-fill"></i> Archive Selected
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- ── DATA TABLE ── --}}
        <div class="jo-table-wrap" id="jo-table-wrap">
            <table class="jo-table" id="jo-inventory-table">
                <thead>
                    <tr>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                            <th rowspan="2" class="checkbox-col" style="display:none; text-align:center;">
                                <input type="checkbox" id="selectAll" style="cursor:pointer; width: 14px; height: 14px;">
                            </th>
                        @endif
                        <th rowspan="2">NO.</th>
                        <th rowspan="2">CHARGES</th>
                        {{-- NAME group --}}
                        <th colspan="4">NAME</th>
                        <th rowspan="2">POSITION</th>
                        <th rowspan="2">NATURE<br>OF WORK</th>
                        <th rowspan="2">OFFICE<br>ASSIGNED</th>
                        <th rowspan="2">RATE/DAY</th>
                        <th rowspan="2">FIRST DAY<br>OF SERVICE</th>
                        {{-- LENGTH OF SERVICE --}}
                        <th colspan="2">LENGTH OF SERVICE</th>
                        <th rowspan="2">BIRTHDATE</th>
                        <th rowspan="2">STATUS</th>
                        <th rowspan="2">ADDRESS</th>
                        <th rowspan="2">ELIGIBILITY</th>
                        <th rowspan="2">NATURE<br>OF WORK</th>
                        <th rowspan="2">GENDER</th>
                        <th rowspan="2">LEVEL</th>
                        <th rowspan="2">IP COMMUNITY<br>MEMBERSHIP</th>
                        <th rowspan="2">SOLO<br>PARENT</th>
                        <th rowspan="2">REMARKS</th>
                        <th rowspan="2">ACTIONS</th>
                    </tr>
                    <tr class="sub-header">
                        <th>LASTNAME</th>
                        <th>FIRSTNAME</th>
                        <th>M.I.</th>
                        <th>EXT.</th>
                        <th>YEAR/S</th>
                        <th>MONTH/S</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $index => $jo)
                        <tr>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                                <td class="checkbox-col" style="display:none; text-align:center;">
                                    <input type="checkbox" class="record-checkbox" value="{{ $jo->id }}" style="cursor:pointer; width: 14px; height: 14px;">
                                </td>
                            @endif
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $jo->charges }}</td>
                            <td class="text-left" style="font-weight:700;">{{ strtoupper($jo->last_name) }}</td>
                            <td class="text-left">{{ $jo->first_name }}</td>
                            <td>{{ $jo->middle_initial }}</td>
                            <td>{{ $jo->name_extension }}</td>
                            <td class="text-left">{{ $jo->position_title }}</td>
                            <td>{{ $jo->nature_of_work }}</td>
                            <td>{{ $jo->office }}</td>
                            <td style="font-weight:700;">
                                @if($jo->rate_per_day)
                                    ₱{{ number_format($jo->rate_per_day, 2) }}
                                @else
                                    <span class="no-record">—</span>
                                @endif
                            </td>
                            <td>
                                @if($jo->first_day_of_service)
                                    {{ $jo->first_day_of_service->format('Y/m/d') }}
                                @else
                                    <span class="no-record">—</span>
                                @endif
                            </td>
                            <td>{{ $jo->first_day_of_service ? $jo->years_of_service : '—' }}</td>
                            <td>{{ $jo->first_day_of_service ? $jo->months_of_service : '—' }}</td>
                            <td>
                                @if($jo->birthdate)
                                    {{ $jo->birthdate->format('Y-m-d') }}
                                @else
                                    <span class="no-record">—</span>
                                @endif
                            </td>
                            <td>{{ $jo->civil_status }}</td>
                            <td class="text-left">{{ $jo->address }}</td>
                            <td>{{ $jo->eligibility }}</td>
                            <td>{{ $jo->nature_of_work_detail }}</td>
                            {{-- Gender single column --}}
                            <td>
                                @if($jo->gender === 'M')
                                    <span class="gender-badge gender-m">M</span>
                                @elseif($jo->gender === 'F')
                                    <span class="gender-badge gender-f">F</span>
                                @endif
                            </td>
                            <td>{{ $jo->level }}</td>
                            <td>{{ $jo->ip_community_membership }}</td>
                            <td>
                                @if($jo->solo_parent)
                                    <span class="check-mark">✓</span>
                                @endif
                            </td>
                            <td class="text-left">{{ $jo->remarks }}</td>
                            <td>
                                <div style="display:flex;gap:4px;justify-content:center;">
                                    <a href="{{ route('employees.profile', ['type' => 'job_orders', 'id' => $jo->id]) }}" class="action-btn" style="background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe;" title="201 Profile">
                                        <i class="bi bi-person-vcard"></i> 201 PROFILE
                                    </a>
                                    <a href="{{ route('job-orders.edit', $jo) }}" class="action-btn action-edit"
                                        title="Edit">
                                        <i class="bi bi-pencil-fill"></i> EDIT
                                    </a>
                                    <form method="POST" action="{{ route('job-orders.archive', $jo) }}"
                                        id="form-arc-jo-{{ $jo->id }}" style="display:inline;">
                                        @csrf
                                        <button type="button" onclick="confirmJoArchive('{{ $jo->id }}')" class="action-btn action-archive" title="Archive">
                                            <i class="bi bi-archive-fill"></i> ARCHIVE
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="25">
                                <div class="empty-state">
                                    <i class="bi bi-file-earmark-person" style="color:#cbd5e1;"></i>
                                    <p>No Job Order records found</p>
                                    <small>{{ request()->anyFilled(['search', 'office', 'charges', 'nature_of_work', 'gender']) ? 'Try adjusting your filters.' : 'Start by adding your first JO record.' }}</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
            </table>
        </div>

        {{-- Excel-style right-quarter horizontal scrollbar --}}
        <div class="jo-hscroll-bar">
            <div class="jo-hscroll-inner" id="jo-hscroll-inner">
                <div class="jo-hscroll-ghost" id="jo-hscroll-ghost"></div>
            </div>
        </div>

    </div>{{-- end .jo-page --}}

    <script>
        // ── Excel-style right-quarter horizontal scrollbar sync (JO) ──
        (function () {
            const scrollEl = document.getElementById('jo-table-wrap');
            const hbarInner = document.getElementById('jo-hscroll-inner');
            const hbarGhost = document.getElementById('jo-hscroll-ghost');

            function syncGhostWidth() {
                const tableEl = scrollEl.querySelector('table');
                if (tableEl) {
                    hbarGhost.style.width = tableEl.scrollWidth + 'px';
                }
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


    {{-- ═══════════════════════════════════════════════════════════════
    IMPORT MODAL
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="import-modal-bg" id="import-modal-bg">
        <div class="import-modal" role="dialog" aria-modal="true" aria-labelledby="import-modal-title">

            {{-- Header --}}
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px;">
                <div class="import-modal-title" id="import-modal-title">
                    <i class="bi bi-file-earmark-arrow-up-fill" style="color:#4f46e5;font-size:20px;"></i>
                    Import JO Records from Excel
                </div>
                <button type="button" onclick="document.getElementById('import-modal-bg').classList.remove('open')"
                    style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;line-height:1;">✕</button>
            </div>
            <p class="import-modal-sub">Upload your <strong>.xlsx</strong> or <strong>.xls</strong> file. Data must
                start at <strong>row 10</strong> with columns matching the template below.</p>

            {{-- Upload Form --}}
            <form method="POST" action="{{ route('job-orders.import') }}" enctype="multipart/form-data"
                id="import-form">
                @csrf

                <div class="import-drop-zone" id="import-drop-zone"
                    onclick="document.getElementById('import-file-input').click()"
                    ondragover="event.preventDefault();this.classList.add('drag-over')"
                    ondragleave="this.classList.remove('drag-over')" ondrop="handleFileDrop(event)">
                    <div class="drop-icon"><i class="bi bi-cloud-upload"></i></div>
                    <p>Click to browse or drag & drop your file here</p>
                    <small>Accepted: .xlsx, .xls &nbsp;|&nbsp; Max size: 10 MB</small>
                    <div class="import-file-name" id="import-file-name">
                        <i class="bi bi-file-earmark-excel-fill" style="color:#16a34a;"></i>
                        <span id="import-file-label">No file selected</span>
                    </div>
                </div>

                <input type="file" name="import_file" id="import-file-input" accept=".xlsx,.xls,.csv"
                    onchange="previewFile(this)">

                <button type="submit" class="btn-import-submit" id="btn-import-submit" disabled>
                    <i class="bi bi-upload"></i> Upload & Import
                </button>

                <a href="{{ route('job-orders.template') }}" class="import-template-link" target="_blank">
                    <i class="bi bi-download"></i> Download Excel Template (.xlsx)
                </a>
            </form>

            {{-- Column guide --}}
            <details style="margin-top:18px;">
                <summary style="font-size:11px;font-weight:700;color:#64748b;cursor:pointer;user-select:none;">
                    📋 Column layout reference (row 9 headers)
                </summary>
                <div style="margin-top:10px;overflow-x:auto;">
                    <table style="width:100%;font-size:10px;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f1f5f9;">
                                <th style="padding:4px 6px;text-align:left;font-weight:700;color:#475569;">Col</th>
                                <th style="padding:4px 6px;text-align:left;font-weight:700;color:#475569;">Header</th>
                                <th style="padding:4px 6px;text-align:left;font-weight:700;color:#475569;">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([
                                    ['A', 'NO.', 'Row number (ignored)'],
                                    ['B', 'CHARGES', 'e.g. BEMO'],
                                    ['C', 'FAMILY', 'Last name'],
                                    ['D', 'FIRST', 'First name'],
                                    ['E', 'M.I.', 'Middle initial'],
                                    ['F', 'EXT', 'Jr., Sr., III, etc.'],
                                    ['G', 'POSITION', 'Position title'],
                                    ['H', 'NATURE OF WORK', 'Clerical, Trades, Technical…'],
                                    ['I', 'OFFICE', 'Assigned office'],
                                    ['J', 'RATE/DAY', 'Numeric, e.g. 615.00'],
                                    ['K', 'FIRST DAY OF SERVICE', 'YYYY-MM-DD'],
                                    ['L', 'LENGTH YRS', 'Auto-computed (skip)'],
                                    ['M', 'LENGTH MOS', 'Auto-computed (skip)'],
                                    ['N', 'BIRTHDATE', 'YYYY-MM-DD'],
                                    ['O', 'ADDRESS', 'Full address'],
                                    ['P', 'ELIGIBILITY', 'e.g. CS PROF, NO ELIGIBILITY'],
                                    ['Q', 'GENDER (M)', 'Enter / or x if Male'],
                                    ['R', 'GENDER (F)', 'Enter / or x if Female'],
                                    ['S', '1st LEVEL', '/ or x if with 1st level'],
                                    ['T', '2nd LEVEL', '/ or x if with 2nd level'],
                                    ['U', 'IP COMMUNITY MEMBERSHIP', 'Text or blank'],
                                    ['V', 'SOLO PARENT', '/ or x'],
                                    ['W', 'REMARKS', 'Any notes'],
                                ] as [$col, $hdr, $note])
                                <tr style="border-top:1px solid #f1f5f9;">
                                    <td style="padding:3px 6px;font-weight:700;color:#0369a1;">{{ $col }}</td>
                                    <td style="padding:3px 6px;font-weight:600;color:#0f172a;">{{ $hdr }}</td>
                                    <td style="padding:3px 6px;color:#64748b;">{{ $note }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
    </div>

    {{-- Export Modal --}}
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <form id="exportForm" method="GET">
                    {{-- Hidden inputs to preserve filters --}}
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="charges" value="{{ request('charges') }}">
                    <input type="hidden" name="office" value="{{ request('office') }}">
                    <input type="hidden" name="nature_of_work" value="{{ request('nature_of_work') }}">
                    <input type="hidden" name="gender" value="{{ request('gender') }}">

                    <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title" id="exportModalLabel" style="font-weight: 800; color: #831843;"><i class="bi bi-download"></i> Export Data Options</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 24px;">
                        <p style="font-size: 14px; color: #475569; margin-bottom: 16px;">Select the columns you want to include in your export:</p>
                        
                        <div style="margin-bottom: 12px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('.export-cb').forEach(cb => cb.checked = true)" style="font-size: 11px; font-weight: 600;">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('.export-cb').forEach(cb => cb.checked = false)" style="font-size: 11px; font-weight: 600;">Deselect All</button>
                        </div>

                        <div class="row">
                            @php
                                $exportColumns = [
                                    'charges' => 'CHARGES',
                                    'family_name' => 'FAMILY NAME',
                                    'first_name' => 'FIRST NAME',
                                    'mi' => 'M.I.',
                                    'ext' => 'EXT',
                                    'position' => 'POSITION',
                                    'nature_of_work' => 'NATURE OF WORK',
                                    'office' => 'OFFICE',
                                    'rate_day' => 'RATE/DAY',
                                    'first_day' => 'FIRST DAY OF SERVICE',
                                    'length_yrs' => 'LENGTH (YRS)',
                                    'length_mos' => 'LENGTH (MOS)',
                                    'birthdate' => 'BIRTHDATE',
                                    'address' => 'ADDRESS',
                                    'eligibility' => 'ELIGIBILITY',
                                    'gender_m' => 'GENDER (M)',
                                    'gender_f' => 'GENDER (F)',
                                    'level_1' => '1st LEVEL',
                                    'level_2' => '2nd LEVEL',
                                    'ip' => 'IP COMMUNITY MEMBERSHIP',
                                    'solo_parent' => 'SOLO PARENT',
                                    'remarks' => 'REMARKS',
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
                        <button type="button" onclick="handleExport('{{ route('job-orders.export.excel') }}', 'excel')" class="btn btn-success" style="font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 6px; background: #16a34a; border: none;">
                            <i class="bi bi-file-earmark-excel-fill"></i> Export to Excel
                        </button>
                        <button type="button" onclick="handleExport('{{ route('job-orders.export.pdf') }}', 'pdf')" class="btn btn-danger" style="font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 6px; background: #dc2626; border: none;">
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

                let filename = type === 'pdf' ? 'job-orders-export.pdf' : 'job-orders-export.xlsx';
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
        function previewFile(input) {
            const name  = input.files[0]?.name ?? '';
            const label = document.getElementById('import-file-label');
            const nameEl= document.getElementById('import-file-name');
            const btn   = document.getElementById('btn-import-submit');
             if (name) {
                label.textContent = name;
                nameEl.style.display = 'block';
                btn.disabled = false;
            } else {
            nameEl.style.display = 'none';
                btn.disabled = true;
            }
        }

        function handleFileDrop(event) {
            event.preventDefault();
            document.getElementById('import-drop-zone').classList.remove('drag-over');
            const file = event.dataTransfer.files[0];
            if (!file) return;
            const input = document.getElementById('import-file-input');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            previewFile(input);
        }

        // Close modal on backdrop click
        document.getElementById('import-modal-bg').addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('open');
        });

        // Show import modal if there was a validation error on re-open
        @if($errors->has('import_file'))
            document.getElementById('import-modal-bg').classList.add('open');
        @endif
    </script>

    
    <div style="padding: 12px 18px; border-top: 1px solid #f1f5f9; background: #fff; border-radius: 0 0 12px 12px;">
        {{ $records->links('pagination::bootstrap-5') }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmJoArchive(id) {
            Swal.fire({
                title: 'Archive this JO record?',
                html: 'This Job Order record will be moved to the <b>Archives</b>.<br>You can restore it later.',
                icon: 'info',
                iconColor: '#7c3aed',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, archive it'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-arc-jo-' + id).submit();
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
    </script>
</x-dashboard-app>
