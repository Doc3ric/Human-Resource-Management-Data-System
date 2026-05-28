<x-dashboard-app>
    <style>
        .cas-hero {
            background: linear-gradient(135deg, #052c65 0%, #052c65 55%, #052c65 100%);
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

        /* Stats bar */
        .cas-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) {
            .cas-stats {
                grid-template-columns: repeat(2, 1fr);
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
            background: linear-gradient(135deg, #059669, #047857);
            color: #fff;
        }

        .cas-btn.add:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .cas-btn.import {
            background: #7c3aed;
            color: #fff;
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
            justify-content: flex-end;
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
            min-width: 1600px;
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
            background: #7c3aed;
            color: #fff;
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
            justify-content: flex-end;
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
    <div class="cas-stats">
        <div class="cas-stat">
            <div class="cas-stat-top">
                <div>
                    <div class="cas-stat-label">Total Records</div>
                    <div class="cas-stat-value">{{ number_format($total) }}</div>
                </div>
                <div class="cas-stat-icon indigo"><i class="bi bi-people-fill"></i></div>
            </div>
            <div class="cas-stat-sub">All casual employees</div>
        </div>
        <div class="cas-stat">
            <div class="cas-stat-top">
                <div>
                    <div class="cas-stat-label">Male</div>
                    <div class="cas-stat-value">{{ number_format($maleCount) }}</div>
                </div>
                <div class="cas-stat-icon blue"><i class="bi bi-gender-male"></i></div>
            </div>
            <div class="cas-stat-sub">Male employees</div>
        </div>
        <div class="cas-stat">
            <div class="cas-stat-top">
                <div>
                    <div class="cas-stat-label">Female</div>
                    <div class="cas-stat-value">{{ number_format($femaleCount) }}</div>
                </div>
                <div class="cas-stat-icon purple"><i class="bi bi-gender-female"></i></div>
            </div>
            <div class="cas-stat-sub">Female employees</div>
        </div>
        <div class="cas-stat">
            <div class="cas-stat-top">
                <div>
                    <div class="cas-stat-label">Vacant</div>
                    <div class="cas-stat-value">{{ number_format($vacantCount) }}</div>
                </div>
                <div class="cas-stat-icon red"><i class="bi bi-person-dash-fill"></i></div>
            </div>
            <div class="cas-stat-sub">Vacant casual positions</div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('casual.index') }}" id="cas-search-form">
        <div class="cas-filter">
            <input type="text" name="search" value="{{ request('search') }}" class="cas-input"
                placeholder="🔍  Search name, position, office…" autocomplete="off">

            <select name="office[]" class="cas-select" id="office-select" multiple="multiple" style="min-width:250px;">
                @php $selectedOffices = (array) request('office', []); @endphp
                @foreach($offices as $office)
                    <option value="{{ $office }}" {{ in_array($office, $selectedOffices) ? 'selected' : '' }}>
                        {{ Str::limit($office, 45) }}
                    </option>
                @endforeach
            </select>

            <select name="gender" class="cas-select" style="min-width:110px;">
                <option value="">All Genders</option>
                <option value="M" {{ request('gender') === 'M' ? 'selected' : '' }}>Male</option>
                <option value="F" {{ request('gender') === 'F' ? 'selected' : '' }}>Female</option>
            </select>

            <select name="vacant" class="cas-select" style="min-width:140px;">
                <option value="">Position Status</option>
                <option value="vacant" {{ request('vacant') === 'vacant' ? 'selected' : '' }}>🔴 Vacant Only</option>
                <option value="filled" {{ request('vacant') === 'filled' ? 'selected' : '' }}>🟢 Filled Only</option>
            </select>

            <button type="submit" class="cas-btn primary"><i class="bi bi-search"></i> Search</button>
            <a href="{{ route('casual.index') }}" class="cas-btn reset"><i class="bi bi-arrow-counterclockwise"></i>
                Reset</a>

            @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                <a href="{{ route('casual.create') }}" class="cas-btn add"><i class="bi bi-plus-lg"></i> Add Record</a>
                <button type="button" class="cas-btn import"
                    onclick="document.getElementById('import-overlay').classList.add('active')">
                    <i class="bi bi-upload"></i> Import Excel
                </button>
            @endif

            @if(auth()->user()->isSuperAdmin())
                <button type="button" id="delete-all-btn"
                    style="display:inline-flex;align-items:center;gap:5px;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;background:#dc2626;color:#fff;transition:all .15s;"
                    onclick="document.getElementById('delete-all-overlay').classList.add('active')">
                    <i class="bi bi-trash3-fill"></i> Delete All Data
                </button>
            @endif

            <div style="margin-left:auto;display:flex;gap:6px;align-items:center;">
                <a href="{{ route('casual.import.history') }}" class="cas-btn" style="background:#7c3aed;color:#fff;">
                    <i class="bi bi-clock-history"></i> Import History
                </a>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <button type="button" class="cas-btn" id="toggle-select-multiple" style="background:#3b82f6;color:#fff;border:none;cursor:pointer;">
                        <i class="bi bi-ui-checks-grid"></i> Select Multiple
                    </button>
                @endif
                <button type="button" class="cas-btn" style="background:#16a34a;color:#fff;border:none;cursor:pointer;" onclick="new bootstrap.Modal(document.getElementById('exportModal')).show()">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i> Export Settings
                </button>
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
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="cas-table-wrap">
        <div class="cas-table-scroll" id="cas-table-scroll">
            <table class="cas-table">
                <thead>
                    <tr>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                            <th class="checkbox-col" style="display:none; text-align:center; padding-left: 16px;">
                                <input type="checkbox" id="selectAll" style="cursor:pointer; width: 14px; height: 14px;">
                            </th>
                        @endif
                        <th>#</th>
                        <th>Office / Department</th>
                        <th>Item No.</th>
                        <th>Position Title</th>
                        <th>Name of Incumbent</th>
                        <th>Legislative District</th>
                        <th>Gender</th>
                        <th>SG / Step (Cur)</th>
                        <th>Annual Salary (Cur)</th>
                        <th>SG / Step (Prop)</th>
                        <th>Annual Salary (Prop)</th>
                        <th>Increase / Decrease</th>
                        <th>Monthly Rate</th>
                        <th>Eligibility</th>
                        <th>Annotation</th>
                        <th>Status</th>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                            <th style="text-align:center;">Actions</th>
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

                            <td title="{{ $r->office }}" style="font-size:11px;color:#64748b;">{{ $r->office }}</td>

                            <td style="font-family:monospace;font-size:11px;color:#9d174d;font-weight:700;">
                                {{ $r->item_no_new ?? $r->item_no_old ?? '—' }}
                            </td>

                            <td title="{{ $r->position_title }}" style="font-weight:600;color:#0f172a;">
                                {{ $r->position_title }}
                            </td>

                            <td style="font-weight:700;color:#0f172a;">
                                @if($r->is_vacant)
                                    <span style="color:#dc2626;font-style:italic;font-size:10px;">VACANT</span>
                                @else
                                    {{ $r->full_name }}
                                @endif
                            </td>

                            <td style="font-size:11px;color:#64748b;">{{ $r->legislative_district ?: '—' }}</td>

                            <td style="text-align:center;font-weight:700;">{{ $r->gender ?: '—' }}</td>

                            <td style="text-align:center;">
                                @if($r->sg_current)
                                    <span class="badge-sg">SG-{{ $r->sg_current }}/{{ $r->step_current }}</span>
                                @else
                                    <span style="color:#d1d5db;">—</span>
                                @endif
                            </td>

                            <td style="text-align:right;font-family:monospace;font-size:11px;">
                                @if($r->salary_current)
                                    ₱{{ number_format($r->salary_current, 2) }}
                                @else
                                    <span style="color:#d1d5db;">—</span>
                                @endif
                            </td>

                            <td style="text-align:center;">
                                @if($r->sg_proposed)
                                    <span class="badge-sg"
                                        style="background:#e0e7ff;color:#3730a3;">SG-{{ $r->sg_proposed }}/{{ $r->step_proposed }}</span>
                                @else
                                    <span style="color:#d1d5db;">—</span>
                                @endif
                            </td>

                            <td style="text-align:right;font-family:monospace;font-size:11px;">
                                @if($r->salary_proposed)
                                    ₱{{ number_format($r->salary_proposed, 2) }}
                                @else
                                    <span style="color:#d1d5db;">—</span>
                                @endif
                            </td>

                            <td style="text-align:right;font-family:monospace;font-size:11px;">
                                @if($r->increase_decrease !== null)
                                    @php $inc = (float) $r->increase_decrease; @endphp
                                    <span style="color:{{ $inc >= 0 ? '#16a34a' : '#dc2626' }};">
                                        {{ $inc >= 0 ? '+' : '' }}₱{{ number_format($inc, 2) }}
                                    </span>
                                @else
                                    <span style="color:#d1d5db;">—</span>
                                @endif
                            </td>

                            <td style="text-align:right;font-family:monospace;font-size:11px;">
                                @if($r->current_rate)
                                    ₱{{ number_format($r->current_rate, 2) }}
                                @else
                                    <span style="color:#d1d5db;">—</span>
                                @endif
                            </td>

                            <td style="font-size:11px;color:#64748b;">{{ Str::limit($r->eligibility, 28) ?: '—' }}</td>

                            <td style="font-size:11px;color:#64748b;max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                                title="{{ $r->annotation }}">
                                {{ $r->annotation ?: '—' }}
                            </td>

                            <td>
                                @if($r->is_vacant)
                                    <span class="badge-vacant">Vacant</span>
                                @else
                                    <span class="badge-filled">Filled</span>
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
                            <td colspan="15" style="text-align:center;padding:40px;color:#9ca3af;font-size:14px;">
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
            <span class="cas-count">Showing {{ number_format($records->count()) }} record(s)</span>
            <div style="font-size:11px;color:#94a3b8;">
                Male: <strong>{{ $maleCount }}</strong> &nbsp;|&nbsp;
                Female: <strong>{{ $femaleCount }}</strong> &nbsp;|&nbsp;
                Vacant: <strong>{{ $vacantCount }}</strong>
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
                    @foreach((array)request('office', []) as $off)
                        <input type="hidden" name="office[]" value="{{ $off }}">
                    @endforeach
                    <input type="hidden" name="gender" value="{{ request('gender') }}">
                    <input type="hidden" name="vacant" value="{{ request('vacant') }}">

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
                                    'office' => 'Office',
                                    'item_old' => 'Item Old',
                                    'item_new' => 'Item New',
                                    'position_title' => 'Position Title',
                                    'name' => 'Name',
                                    'legislative_district' => 'Legislative District',
                                    'gender' => 'Gender',
                                    'sg_step_current' => 'SG/Step (Cur)',
                                    'annual_salary_current' => 'Annual Salary (Cur)',
                                    'sg_step_proposed' => 'SG/Step (Prop)',
                                    'annual_salary_proposed' => 'Annual Salary (Prop)',
                                    'increase_decrease' => 'Increase/Decrease',
                                    'monthly_rate' => 'Monthly Rate'
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

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new TomSelect('#office-select', {
                plugins: ['remove_button'],
                placeholder: 'All Offices',
                maxOptions: 50,
                hideSelected: true
            });
        });
    </script>

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