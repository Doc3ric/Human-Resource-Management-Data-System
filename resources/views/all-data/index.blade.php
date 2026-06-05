<x-dashboard-app>
    <style>
        /* ── Hero ───────────────────────────────────────────────────────────── */
        .ad-hero {
            background: linear-gradient(135deg, #052c65 0%, #052c65 55%, #052c65 100%);
            border-radius: 14px;
            padding: 24px 28px;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .ad-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .07) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .ad-hero-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .ad-hero h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            margin: 0;
        }

        .ad-hero p {
            color: rgba(255, 255, 255, .6);
            font-size: 12px;
            margin: 4px 0 0;
        }

        .ad-hero-badge {
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

        /* ── Stats bar ──────────────────────────────────────────────────────── */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) {
            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .stats-bar {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-card-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-card-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #475569;
            margin-bottom: 4px;
        }

        .stat-card-value {
            font-size: 30px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            margin: 0;
        }

        .stat-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-card-icon.blue {
            background: #e0f2fe;
            color: #0284c7;
        }

        .stat-card-icon.orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .stat-card-icon.indigo {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .stat-card-icon.purple {
            background: #f3e8ff;
            color: #9333ea;
        }

        .stat-card-sub {
            margin-top: 18px;
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-card-sub.green {
            color: #10b981;
        }

        /* ── Filter bar ─────────────────────────────────────────────────────── */
        .filter-bar {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        }

        .filter-input,
        .filter-select {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            color: #374151;
            outline: none;
            background: #fafafa;
            transition: border .15s, box-shadow .15s;
        }

        .filter-input:focus,
        .filter-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
            background: #fff;
        }

        .filter-input {
            flex: 1;
            min-width: 200px;
        }

        .search-wrap {
            flex: 1;
            min-width: 200px;
        }

        .filter-select {
            min-width: 140px;
        }

        .filter-btn {
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

        .filter-btn.primary {
            background: #2563eb;
            color: #fff;
        }

        .filter-btn.primary:hover {
            background: #1d4ed8;
        }

        .filter-btn.reset {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .filter-btn.reset:hover {
            background: #e2e8f0;
        }

        .filter-btn.add {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            color: #fff;
        }

        .filter-btn.add:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        /* ── Table container ─────────────────────────────────────────────────── */
        .table-wrap {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
        }

        .table-scroll {
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 350px);
            min-height: 300px;
            /* Hide native horizontal scrollbar — replaced by custom strip below */
            scrollbar-width: none;
            /* Firefox: hide all scrollbars temporarily */
        }

        /* Hide only the horizontal scrollbar in WebKit */
        .table-scroll::-webkit-scrollbar {
            width: 12px;
            /* keep vertical scrollbar */
            height: 0;
            /* hide horizontal scrollbar */
        }

        .table-scroll::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 10px;
        }

        .table-scroll::-webkit-scrollbar-thumb {
            background: #9ca3af;
            border-radius: 10px;
            border: 2px solid #f3f4f6;
        }

        .table-scroll::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        .table-scroll::-webkit-scrollbar-thumb:active {
            background: #374151;
        }

        /* ── Excel-style right-quarter horizontal scrollbar ────────── */
        .ad-hscroll-bar {
            display: flex;
            justify-content: flex-end;
            background: #f3f4f6;
            border-top: 1px solid #e5e7eb;
        }

        .ad-hscroll-inner {
            width: 25%;
            overflow-x: auto;
            overflow-y: hidden;
            height: 14px;
            scrollbar-width: thin;
            scrollbar-color: #9ca3af #f3f4f6;
        }

        .ad-hscroll-inner::-webkit-scrollbar {
            height: 14px;
        }

        .ad-hscroll-inner::-webkit-scrollbar-track {
            background: #f3f4f6;
        }

        .ad-hscroll-inner::-webkit-scrollbar-thumb {
            background: #9ca3af;
            border-radius: 10px;
            border: 2px solid #f3f4f6;
        }

        .ad-hscroll-inner::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        .ad-hscroll-ghost {
            height: 1px;
        }

        /* ── Main data table ─────────────────────────────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 2000px;
        }

        .data-table thead tr {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .data-table th {
            padding: 10px 12px;
            text-align: left;
            white-space: nowrap;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: rgba(255, 255, 255, .7);
            border-bottom: 2px solid rgba(255, 255, 255, .08);
            border-right: 1px solid rgba(255, 255, 255, .08);
            cursor: pointer;
            user-select: none;
        }

        .data-table th:last-child {
            border-right: 0;
        }

        .data-table th:hover {
            color: #fff;
            background: rgba(255, 255, 255, .07);
        }

        .data-table tbody tr:nth-child(even) td {
            background: #f0f9ff;
        }

        .data-table tbody tr:hover td {
            background: #e0f2fe !important;
            transition: background .1s;
        }

        .data-table td {
            padding: 12px 14px;
            font-size: 12px;
            color: #374151;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            white-space: nowrap;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .data-table td:last-child {
            border-right: 0;
        }

        .data-table tbody tr:last-child td {
            border-bottom: 0;
        }

        /* Col #1 sticky */
        .data-table td:first-child,
        .data-table th:first-child {
            position: sticky;
            left: 0;
            z-index: 1;
            background: #fff;
            border-right: 2px solid #e5e7eb !important;
            min-width: 90px;
        }

        .data-table thead th:first-child {
            background: #1e3a8a;
        }

        .data-table tbody tr:nth-child(even) td:first-child {
            background: #f0f9ff;
        }

        .data-table tbody tr:hover td:first-child {
            background: #e0f2fe !important;
        }

        /* Status badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-P {
            background: #d1fae5;
            color: #065f46;
        }

        .status-CT {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-E {
            background: #fef3c7;
            color: #92400e;
        }

        .status-Casual {
            background: #fce7f3;
            color: #9d174d;
        }

        .status-JO {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .status-vacant {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-default {
            background: #f1f5f9;
            color: #475569;
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
            font-family: inherit;
            line-height: 1;
            text-decoration: none;
            transition: all .12s;
            border: 1px solid transparent;
            cursor: pointer;
            white-space: nowrap;
            box-sizing: border-box;
            vertical-align: middle;
            margin: 0;
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

        /* ── Pagination + footer ─────────────────────────────────────────────── */
        .table-footer {
            padding: 12px 18px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .table-count {
            font-size: 12px;
            color: #6b7280;
        }

        /* ── Flash alerts ────────────────────────────────────────────────────── */
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

        /* Per-page select */
        .per-page-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #6b7280;
        }
    </style>

    {{-- Hero --}}
    <div class="ad-hero">
        <div class="ad-hero-inner">
            <div>
                <h1><i class="bi bi-table me-2"></i>All Data</h1>
                <p>Complete plantilla records — all {{ number_format($total) }} entries</p>
            </div>
            <span class="ad-hero-badge"><i class="bi bi-database-fill"></i> {{ number_format($total) }} Records</span>
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
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-card-info">
                    <div class="stat-card-label">Total Records</div>
                    <div class="stat-card-value">{{ number_format($total) }}</div>
                </div>
                <div class="stat-card-icon blue">
                    <i class="bi bi-list-task"></i>
                </div>
            </div>
            <div class="stat-card-sub green">
                <i class="bi bi-arrow-up-short"></i> System-wide records
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-card-info">
                    <div class="stat-card-label">Showing Now</div>
                    <div class="stat-card-value">{{ number_format($records->total()) }}</div>
                </div>
                <div class="stat-card-icon orange">
                    <i class="bi bi-eye-fill"></i>
                </div>
            </div>
            <div class="stat-card-sub">
                Active view/filter results
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-card-info">
                    <div class="stat-card-label">Current Page</div>
                    <div class="stat-card-value">{{ $records->currentPage() }}</div>
                </div>
                <div class="stat-card-icon indigo">
                    <i class="bi bi-book-fill"></i>
                </div>
            </div>
            <div class="stat-card-sub">
                of {{ number_format($records->lastPage()) }} pages
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-card-info">
                    <div class="stat-card-label">Per Page</div>
                    <div class="stat-card-value">{{ $records->perPage() }}</div>
                </div>
                <div class="stat-card-icon purple">
                    <i class="bi bi-list-nested"></i>
                </div>
            </div>
            <div class="stat-card-sub">
                Configurable in filters
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('all-data.index') }}" id="search-form">
        <div class="filter-bar">

            {{-- TOP ROW: Filters --}}
            <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                {{-- Search input --}}
                <div class="search-wrap" style="display: flex;">
                    <input type="text" name="search" value="{{ request('search') }}" class="filter-input"
                        placeholder="🔍  Search name, item, position, TIN…" autocomplete="off" style="width: 100%;">
                </div>

                <select name="office" class="filter-select">
                    <option value="">All Offices</option>
                    @foreach($offices as $office)
                        <option value="{{ $office }}" {{ request('office') === $office ? 'selected' : '' }}>
                            {{ Str::limit($office, 45) }}
                        </option>
                    @endforeach
                </select>

                <select name="position" class="filter-select" style="min-width:180px;">
                    <option value="">All Positions</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos }}" {{ request('position') === $pos ? 'selected' : '' }}>
                            {{ Str::limit($pos, 45) }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="P" {{ request('status') === 'P' ? 'selected' : '' }}>Permanent</option>
                    <option value="CT" {{ request('status') === 'CT' ? 'selected' : '' }}>Co-Terminous</option>
                    <option value="E" {{ request('status') === 'E' ? 'selected' : '' }}>Elected</option>
                    <option value="Casual" {{ request('status') === 'Casual' ? 'selected' : '' }}>Casual</option>
                    <option value="JO" {{ request('status') === 'JO' ? 'selected' : '' }}>Job Order</option>
                </select>

                <select name="sex" class="filter-select" style="min-width:100px;">
                    <option value="">All Genders</option>
                    <option value="M" {{ request('sex') === 'M' ? 'selected' : '' }}>Male</option>
                    <option value="F" {{ request('sex') === 'F' ? 'selected' : '' }}>Female</option>
                </select>

                <select name="vacant" class="filter-select" style="min-width:130px;">
                    <option value="">Position Status</option>
                    <option value="vacant" {{ request('vacant') === 'vacant' ? 'selected' : '' }}>🔴 Vacant Only</option>
                    <option value="filled" {{ request('vacant') === 'filled' ? 'selected' : '' }}>🟢 Filled Only</option>
                </select>

                <select name="per_page" class="filter-select" style="min-width:90px;">
                    @foreach([25, 50, 100, 200] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 50) == $n ? 'selected' : '' }}>{{ $n }} / page
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- BOTTOM ROW: Actions --}}
            <div
                style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; border-top: 1px solid #f1f5f9; padding-top: 16px;">

                {{-- Left Side: Core Actions --}}
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="filter-btn primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="{{ route('all-data.index') }}" class="filter-btn reset">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                        <a href="{{ route('all-data.create') }}" class="filter-btn add">
                            <i class="bi bi-plus-lg"></i> Add Record
                        </a>
                    @endif
                </div>

                {{-- Right Side: Extra Utilities --}}
                <div style="display:flex; gap:6px; align-items:center;">
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                        <button type="button" class="filter-btn" id="toggle-select-multiple"
                            style="background:#fff;color:#334155;border:1px solid #cbd5e1;gap:5px;"
                            onmouseover="this.style.background='#f8fafc';this.style.borderColor='#94a3b8';"
                            onmouseout="this.style.background='#fff';this.style.borderColor='#cbd5e1';">
                            <i class="bi bi-ui-checks-grid"></i> Select Multiple
                        </button>
                    @endif
                    <a href="{{ route('archives.index') }}" class="filter-btn"
                        style="background:#7c3aed;color:#fff;gap:5px;"
                        onmouseover="this.style.background='#f8fafc';this.style.borderColor='#94a3b8';"
                        onmouseout="this.style.background='#fff';this.style.borderColor='#cbd5e1';">
                        <i class="bi bi-archive-fill"></i> View Archives
                    </a>
                    <button type="button" class="filter-btn"
                        style="background:#fff;color:#334155;border:1px solid #cbd5e1;gap:5px;"
                        onmouseover="this.style.background='#f8fafc';this.style.borderColor='#94a3b8';"
                        onmouseout="this.style.background='#fff';this.style.borderColor='#cbd5e1';"
                        onclick="new bootstrap.Modal(document.getElementById('exportModal')).show()">
                        <i class="bi bi-file-earmark-arrow-down-fill"></i> Export Settings
                    </button>
                </div>

            </div>

        </div>
    </form>

    {{-- Bulk Action Bar --}}
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
        <div id="bulk-action-bar"
            style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; align-items: center; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span
                    style="background: #3b82f6; color: white; border-radius: 9999px; padding: 2px 10px; font-size: 12px; font-weight: 700;"
                    id="bulk-count">0</span>
                <span style="font-size: 13px; font-weight: 600; color: #334155;">records selected</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <form id="bulk-archive-form" method="POST" action="{{ route('all-data.bulk-archive') }}" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="type" value="plantilla">
                    <div id="bulk-ids-container"></div>
                    <button type="button" onclick="confirmBulkArchive()" class="filter-btn"
                        style="background: #7c3aed; color: #fff;">
                        <i class="bi bi-archive-fill"></i> Archive Selected
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="table-wrap">
        <div class="table-scroll" id="ad-table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                            <th class="checkbox-col" style="display:none; text-align:center; padding-left: 16px;">
                                <input type="checkbox" id="selectAll" style="cursor:pointer; width: 14px; height: 14px;">
                            </th>
                        @endif
                        <th>#</th>
                        <th>Org. Unit</th>
                        <th>Item</th>
                        <th>Emp. Code</th>
                        <th>Position Title</th>
                        <th>SG</th>
                        <th>Auth. Annual Salary</th>
                        <th>Actual Annual Salary</th>
                        <th>Step</th>
                        <th>Area Code</th>
                        <th>Area Type</th>
                        <th>Level</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Gender</th>
                        <th>Religion</th>
                        <th>Date of Birth</th>
                        <th>TIN</th>
                        <th>Date Orig. Appointment</th>
                        <th>Date Last Promotion</th>
                        <th style="text-align:center;">PWD</th>
                        <th>Civil Service Eligibility</th>
                        <th>Status</th>
                        <th>Termination</th>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                            <th style="text-align:center; min-width: 280px;">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $r)
                        <tr>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                                <td class="checkbox-col" style="display:none; text-align:center; padding-left: 16px;">
                                    <input type="checkbox" class="record-checkbox" value="{{ $r->id }}"
                                        style="cursor:pointer; width: 14px; height: 14px;">
                                </td>
                            @endif
                            {{-- Row # (sticky col) --}}
                            <td style="color:#9ca3af;font-size:10px;font-weight:600;">
                                {{ $records->firstItem() + $loop->index }}
                            </td>

                            {{-- Organizational Unit --}}
                            <td title="{{ $r->organizational_unit }}">{{ $r->organizational_unit }}</td>

                            {{-- Item --}}
                            <td style="font-family:monospace;font-size:11px;color:#1e40af;font-weight:700;">
                                {{ $r->item }}
                            </td>

                            {{-- Employee Code --}}
                            <td style="font-family:monospace;font-size:11px;color:#2563eb;font-weight:700;">
                                {{ $r->employee_code ?: '-' }}
                            </td>

                            {{-- Position Title --}}
                            <td title="{{ $r->position_title }}" style="font-weight:600;color:#0f172a;">
                                {{ $r->position_title }}
                            </td>

                            {{-- Salary Grade --}}
                            <td style="text-align:center;font-weight:700;color:#0369a1;">
                                SG-{{ $r->salary_grade }}
                            </td>

                            {{-- Authorized Annual Salary --}}
                            <td style="text-align:right;font-family:monospace;font-size:11px;">
                                @if($r->authorized_annual_salary)
                                    ₱{{ number_format($r->authorized_annual_salary, 2) }}
                                @else<span style="color:#d1d5db;">—</span>@endif
                            </td>

                            {{-- Actual Annual Salary --}}
                            <td style="text-align:right;font-family:monospace;font-size:11px;">
                                @if($r->actual_annual_salary)
                                    ₱{{ number_format($r->actual_annual_salary, 2) }}
                                @else<span style="color:#d1d5db;">—</span>@endif
                            </td>

                            {{-- Step --}}
                            <td style="text-align:center;font-weight:700;">{{ $r->step }}</td>

                            {{-- Area Code --}}
                            <td style="color:#6b7280;">{{ $r->area_code ?: '—' }}</td>

                            {{-- Area Type --}}
                            <td style="color:#6b7280;">{{ $r->area_type ?: '—' }}</td>

                            {{-- Level --}}
                            <td style="color:#6b7280;">{{ $r->level ?: '—' }}</td>

                            {{-- Last Name --}}
                            <td style="font-weight:700;text-transform:uppercase;color:#0f172a;">
                                @if($r->is_vacant)
                                    <span style="color:#dc2626;font-style:italic;font-size:10px;">VACANT</span>
                                @else
                                    {{ $r->last_name ?: '—' }}
                                @endif
                            </td>

                            {{-- First Name --}}
                            <td>{{ $r->first_name ?: '—' }}</td>

                            {{-- Middle Name --}}
                            <td style="color:#6b7280;">{{ $r->middle_name ?: '—' }}</td>

                            {{-- Sex --}}
                            <td style="text-align:center;font-weight:700;color:#374151;">
                                {{ $r->sex ?: '—' }}
                            </td>

                            {{-- Religion --}}
                            <td style="color:#6b7280;">
                                {{ $r->religion ?: '—' }}
                            </td>

                            {{-- Date of Birth --}}
                            <td style="color:#6b7280;font-size:11px;">
                                {{ $r->date_of_birth?->format('m/d/Y') ?: '—' }}
                            </td>

                            {{-- TIN --}}
                            <td style="font-family:monospace;font-size:11px;color:#6b7280;">
                                {{ $r->tin ?: '—' }}
                            </td>

                            {{-- Date Original Appointment --}}
                            <td style="font-size:11px;color:#6b7280;">
                                {{ $r->date_original_appointment?->format('m/d/Y') ?: '—' }}
                            </td>

                            {{-- Date Last Promotion --}}
                            <td style="font-size:11px;color:#6b7280;">
                                {{ $r->date_last_promotion?->format('m/d/Y') ?: '—' }}
                            </td>

                            {{-- PWD --}}
                            <td style="text-align:center;">
                                @if($r->is_pwd)
                                    <span
                                        style="background:#fefce8;color:#a16207;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;border:1px solid #fef08a;"
                                        title="{{ $r->type_of_disability ?: 'Unspecified' }}">
                                        YES
                                    </span>
                                @else
                                    <span style="color:#d1d5db;">—</span>
                                @endif
                            </td>

                            {{-- Civil Service Eligibility --}}
                            <td title="{{ $r->civil_service_eligibility }}" style="color:#374151;">
                                {{ $r->civil_service_eligibility ?: '—' }}
                            </td>

                            {{-- Employment Status --}}
                            <td>
                                @php
                                    $s = $r->employment_status;
                                    $badge = match (true) {
                                        in_array($s, ['P', 'Permanent']) => ['status-P', 'Permanent'],
                                        in_array($s, ['CT', 'Co-Terminous', 'Coterminous']) => ['status-CT', 'Co-Term.'],
                                        in_array($s, ['E', 'Elected']) => ['status-E', 'Elected'],
                                        in_array($s, ['Casual', 'Cas']) => ['status-Casual', 'Casual'],
                                        in_array($s, ['JO', 'Job Order', 'J.O.']) => ['status-JO', 'Job Order'],
                                        $r->is_vacant => ['status-vacant', 'Vacant'],
                                        default => ['status-default', $s ?: '—'],
                                    };
                                @endphp
                                <span class="status-badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                            </td>

                            {{-- Termination --}}
                            <td>
                                <span style="color:#6b7280; font-size: 11px;">
                                    {{ $r->nature_of_separation ?: '—' }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                                <td style="text-align:center; min-width: 280px; max-width: none; white-space: nowrap;">
                                    <div
                                        style="display:flex; flex-wrap: nowrap; gap:6px; justify-content:center; align-items:center;">
                                        <a href="{{ route('employees.profile', ['type' => 'plantilla', 'id' => $r->id]) }}"
                                            class="row-btn" style="background: #e0e7ff; color: #4338ca; border-color: #c7d2fe;"
                                            title="201 Profile">
                                            <i class="bi bi-person-vcard"></i> Profile
                                        </a>
                                        <a href="{{ route('all-data.edit', $r) }}" class="row-btn edit" title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        @if(!$r->is_vacant)
                                            <a href="{{ route('plantilla.promote.form', $r) }}" class="row-btn"
                                                style="background: #e0e7ff; color: #4f46e5; border-color: #c7d2fe;"
                                                title="Promote / Transfer">
                                                <i class="bi bi-person-up"></i> Promote
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('all-data.archive', $r) }}"
                                            id="form-archive-{{ $r->id }}" style="display:contents;">
                                            @csrf
                                            <button type="button" onclick="confirmArchive('{{ $r->id }}')" class="row-btn arc"
                                                title="Archive Slot">
                                                <i class="bi bi-archive"></i> Archive
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="22" style="text-align:center;padding:48px;color:#9ca3af;font-style:italic;">
                                No records found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Excel-style right-quarter horizontal scrollbar --}}
        <div class="ad-hscroll-bar">
            <div class="ad-hscroll-inner" id="ad-hscroll-inner">
                <div class="ad-hscroll-ghost" id="ad-hscroll-ghost"></div>
            </div>
        </div>

        {{-- Table footer: pagination + count --}}
        <div class="table-footer">
            <div class="table-count">
                Showing {{ $records->firstItem() }}–{{ $records->lastItem() }}
                of {{ number_format($records->total()) }} records
            </div>
            <div>{{ $records->links() }}</div>
        </div>
    </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ── Excel-style right-quarter horizontal scrollbar sync ──
        (function () {
            const scrollEl = document.getElementById('ad-table-scroll');
            const hbarInner = document.getElementById('ad-hscroll-inner');
            const hbarGhost = document.getElementById('ad-hscroll-ghost');

            function syncGhostWidth() {
                const tableEl = scrollEl.querySelector('table');
                if (tableEl) {
                    hbarGhost.style.width = tableEl.scrollWidth + 'px';
                }
            }

            // Sync ghost width on load and resize
            syncGhostWidth();
            window.addEventListener('resize', syncGhostWidth);

            // When user scrolls the fake bar → scroll the table
            let syncingFromBar = false;
            hbarInner.addEventListener('scroll', function () {
                if (syncingFromBar) return;
                syncingFromBar = true;
                // Map fake scrollLeft (0 to ghost.width - inner.width) → table scrollLeft (0 to scrollWidth - clientWidth)
                const fakeRatio = hbarInner.scrollLeft / (hbarInner.scrollWidth - hbarInner.clientWidth);
                scrollEl.scrollLeft = fakeRatio * (scrollEl.scrollWidth - scrollEl.clientWidth);
                syncingFromBar = false;
            });

            // When user scrolls the table → update fake bar
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

    {{-- Export Modal --}}
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content"
                style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <form id="exportForm" method="GET">
                    {{-- Hidden inputs to preserve filters --}}
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="office" value="{{ request('office') }}">
                    <input type="hidden" name="position" value="{{ request('position') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="sex" value="{{ request('sex') }}">
                    <input type="hidden" name="vacant" value="{{ request('vacant') }}">
                    <input type="hidden" name="pwd" value="{{ request('pwd') }}">
                    <input type="hidden" name="ip" value="{{ request('ip') }}">
                    <input type="hidden" name="solo_parent" value="{{ request('solo_parent') }}">
                    <input type="hidden" name="abolished" value="{{ request('abolished') }}">

                    <div class="modal-header"
                        style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title" id="exportModalLabel" style="font-weight: 800; color: #0f2942;"><i
                                class="bi bi-download"></i> Export Data Options</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 24px;">
                        <p style="font-size: 14px; color: #475569; margin-bottom: 16px;">Select the columns you want to
                            include in your export:</p>

                        <div style="margin-bottom: 12px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="document.querySelectorAll('.export-cb').forEach(cb => cb.checked = true)"
                                style="font-size: 11px; font-weight: 600;">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="document.querySelectorAll('.export-cb').forEach(cb => cb.checked = false)"
                                style="font-size: 11px; font-weight: 600;">Deselect All</button>
                        </div>

                        <div class="row">
                            @php
                                $exportColumns = [
                                    'organizational_unit' => 'Organizational Unit',
                                    'item' => 'Item',
                                    'position_title' => 'Position Title',
                                    'salary_grade' => 'Salary Grade',
                                    'authorized_annual_salary' => 'Authorized Annual Salary',
                                    'actual_annual_salary' => 'Actual Annual Salary',
                                    'step' => 'Step',
                                    'area_code' => 'Area Code',
                                    'area_type' => 'Area Type',
                                    'level' => 'Level',
                                    'last_name' => 'Last Name',
                                    'first_name' => 'First Name',
                                    'middle_name' => 'Middle Name',
                                    'sex' => 'Sex',
                                    'religion' => 'Religion',
                                    'date_of_birth' => 'Date of Birth',
                                    'tin' => 'TIN',
                                    'date_original_appointment' => 'Date of Original Appointment',
                                    'date_last_promotion' => 'Date of Last Promotion/Appointment',
                                    'pwd' => 'PWD',
                                    'status' => 'Status',
                                    'civil_service_eligibility' => 'Civil Service Eligibility',
                                    'admin_charges' => 'Admin Charges',
                                    'nature_of_separation' => 'Termination',
                                ];
                            @endphp
                            @foreach($exportColumns as $key => $label)
                                <div class="col-md-4 col-sm-6" style="margin-bottom: 8px;">
                                    <div class="form-check">
                                        <input class="form-check-input export-cb" type="checkbox" name="columns[]"
                                            value="{{ $key }}" id="col_{{ $key }}" checked>
                                        <label class="form-check-label" for="col_{{ $key }}"
                                            style="font-size: 13px; color: #1e293b;">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 16px 24px;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 6px;">Cancel</button>
                        <button type="button" onclick="handleExport('{{ route('all-data.export.excel') }}', 'excel')"
                            class="btn btn-success"
                            style="font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 6px; background: #16a34a; border: none;">
                            <i class="bi bi-file-earmark-excel-fill"></i> Export to Excel
                        </button>
                        <button type="button" onclick="handleExport('{{ route('all-data.export.pdf') }}', 'pdf')"
                            class="btn btn-danger"
                            style="font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 6px; background: #dc2626; border: none;">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Export to PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        async function handleExport(url, type) {
            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();

            // Show loading alert
            Swal.fire({
                title: 'Generating ' + (type === 'pdf' ? 'PDF' : 'Excel') + '...',
                html: 'This may take a moment depending on the number of records. Please do not close the window.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Collect form data
            const form = document.getElementById('exportForm');
            const formData = new FormData(form);
            const queryParams = new URLSearchParams(formData).toString();

            try {
                const response = await fetch(`${url}?${queryParams}`, {
                    method: 'GET',
                    headers: {
                        'Accept': type === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    }
                });

                if (!response.ok) {
                    throw new Error('Server returned an error while generating the file.');
                }

                // Get filename from Content-Disposition header if available
                let filename = type === 'pdf' ? 'plantilla-all-data.pdf' : 'plantilla-all-data.xlsx';
                const disposition = response.headers.get('Content-Disposition');
                if (disposition && disposition.indexOf('filename=') !== -1) {
                    filename = disposition.split('filename=')[1].replace(/["']/g, '');
                }

                // Convert response to blob
                const blob = await response.blob();
                const downloadUrl = window.URL.createObjectURL(blob);

                // Create a temporary link to trigger download
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = downloadUrl;
                a.download = filename;
                document.body.appendChild(a);
                a.click();

                // Cleanup
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
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'There was an issue generating your file. The system might have run out of memory or timed out. ' + error.message,
                });
            }
        }
        function confirmDelete(id) {
            Swal.fire({
                title: 'Delete this record?',
                text: "This action cannot be undone.",
                icon: 'warning',
                iconColor: '#dc3545',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-delete-' + id).submit();
                }
            });
        }

        function confirmSyncSalaries() {
            Swal.fire({
                title: 'Synchronize Salaries?',
                text: "This will synchronize the salaries of all plantilla records with the latest rates in the salary grades table.",
                icon: 'question',
                iconColor: '#0d6efd',
                showCancelButton: true,
                confirmButtonColor: '#0f2942',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, sync salaries'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-sync-salaries').submit();
                }
            });
        }

        function confirmArchive(id) {
            Swal.fire({
                title: 'Archive this slot?',
                html: 'The entire plantilla slot (Item No.) will be moved to the <b>Archives</b>.<br>You can restore it later from the Archives page.',
                icon: 'info',
                iconColor: '#7c3aed',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, archive it'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-archive-' + id).submit();
                }
            });
        }

        // Bulk Selection Logic
        document.addEventListener('DOMContentLoaded', function () {
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
                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(cb => {
                        cb.checked = selectAll.checked;
                    });
                    updateBulkBar();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function () {
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
                toggleSelectBtn.addEventListener('click', function () {
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
                html: 'Selected records will be moved to the <b>Archives</b>.<br>You can restore them later from the Archives page.',
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