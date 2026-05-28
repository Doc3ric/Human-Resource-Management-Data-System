<x-dashboard-app>
    <style>
        /* â”€â”€ Step Increment Page â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .si-hero {
            background: linear-gradient(135deg, #312e81 0%, #4338ca 55%, #6d28d9 100%);
            border-radius: 14px;
            padding: 28px 32px;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .si-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .07) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .si-hero-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .si-hero h1 {
            color: #fff;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            line-height: 1.2;
        }

        .si-hero p {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin: 5px 0 0;
        }

        .si-hero-btn {
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

        .si-hero-btn:hover {
            background: rgba(255, 255, 255, .28);
            color: #fff;
        }

        /* Stat cards */
        .si-stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .si-stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px 20px;
            color: #1e293b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
            text-decoration: none;
            display: block;
            transition: all .2s ease;
        }

        .si-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }

        .si-stat-num {
            font-size: 30px;
            font-weight: 800;
            line-height: 1;
        }

        .si-stat-label {
            font-size: 12px;
            color: #64748b;
            margin-top: 6px;
            font-weight: 600;
        }

        /* Tabs */
        .si-tab-bar {
            display: flex;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 18px;
            gap: 4px;
        }

        .si-tab {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            text-decoration: none;
            color: #6b7280;
            transition: all .15s;
            border-radius: 8px 8px 0 0;
        }

        .si-tab:hover {
            color: #374151;
            background: #f9fafb;
        }

        .si-tab.active-due {
            border-color: #ef4444;
            color: #dc2626;
            background: #fef2f2;
        }

        .si-tab.active-upcoming {
            border-color: #f59e0b;
            color: #d97706;
            background: #fffbeb;
        }

        .si-tab.active-loyalty {
            border-color: #b45309;
            color: #92400e;
            background: #fef3c7;
        }

        .si-tab.active-nosi {
            border-color: #0ea5e9;
            color: #0369a1;
            background: #f0f9ff;
        }

        .si-tab.active-nolp {
            border-color: #8b5cf6;
            color: #6d28d9;
            background: #f5f3ff;
        }

        .si-badge-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1px 8px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 800;
            color: #fff;
        }

        /* Table wrapper */
        .si-table-wrap {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .04);
        }

        .si-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .si-table thead tr {
            background: linear-gradient(135deg, #312e81 0%, #4338ca 100%);
        }

        .si-table th {
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: rgba(255, 255, 255, .72);
            padding: 12px 16px;
            white-space: nowrap;
            border-bottom: 2px solid rgba(255, 255, 255, .1);
        }

        .si-table th.tc {
            text-align: center;
        }

        .si-table tbody tr {
            transition: background .12s;
        }

        .si-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .si-table tbody tr:hover td {
            background: #eef2ff !important;
        }

        .si-table td {
            padding: 11px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            color: #374151;
        }

        .si-table td.tc {
            text-align: center;
        }

        .si-table tbody tr:last-child td {
            border-bottom: 0;
        }

        /* Employee cell */
        .si-emp-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 13px;
            letter-spacing: -.1px;
        }

        .si-emp-unit {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* Position cell */
        .si-pos-title {
            font-weight: 600;
            color: #312e81;
            font-size: 13px;
        }

        .si-pos-item {
            font-family: ui-monospace, monospace;
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* SG badge */
        .si-sg-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #312e81, #4338ca);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(67, 56, 202, .3);
        }

        /* Step progression arrow */
        .si-step-flow {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 700;
        }

        .si-step-cur {
            background: #e0e7ff;
            color: #4338ca;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .si-step-arrow {
            color: #a5b4fc;
            font-size: 13px;
        }

        .si-step-next {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            padding: 3px 10px;
            border-radius: 99px;
        }

        /* Salary */
        .si-salary {
            font-family: ui-monospace, monospace;
            color: #374151;
            font-size: 13px;
        }

        /* Due date */
        .si-due-overdue {
            color: #334155;
            font-weight: 600;
            font-size: 13px;
        }

        .si-due-overdue .si-ago {
            display: none;
        }

        .si-due-upcoming {
            color: #334155;
            font-weight: 600;
            font-size: 13px;
        }

        /* Process button */
        .si-process-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff;
            border: none;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s;
            box-shadow: 0 2px 8px rgba(22, 163, 74, .3);
        }

        .si-process-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(22, 163, 74, .4);
        }

        /* Empty state */
        .si-empty {
            text-align: center;
            padding: 60px 20px;
        }

        .si-empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #6366f1;
            box-shadow: 0 4px 14px rgba(99, 102, 241, .18);
        }
    </style>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div
            style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div
            style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- â–Œâ–Œ STAT CARDS â–Œâ–Œ --}}
    <div class="si-stat-grid" style="grid-template-columns: repeat(5, 1fr);">
        <!-- Overdue removed -->
        <a href="{{ route('step-increment.index', ['tab' => 'due']) }}" class="si-stat-card">
            <div class="si-stat-num" style="color: #ea580c;">{{ number_format($stats['due']) }}</div>
            <div class="si-stat-label"><i class="bi bi-check2-circle" style="color: #ea580c;"></i> Due This Mth</div>
        </a>
        <a href="{{ route('step-increment.index', ['tab' => 'upcoming_nolp']) }}" class="si-stat-card">
            <div class="si-stat-num" style="color: #d97706;">{{ number_format($stats['upcoming']) }}</div>
            <div class="si-stat-label"><i class="bi bi-clock" style="color: #d97706;"></i> Due in 6 Mths</div>
        </a>
        <a href="#" onclick="return false;" class="si-stat-card" style="cursor: default;" title="Max Step employees">
            <div class="si-stat-num" style="color: #059669;">{{ number_format($stats['maxStep']) }}</div>
            <div class="si-stat-label"><i class="bi bi-trophy-fill" style="color: #059669;"></i> Max Step (8)</div>
        </a>
        <a href="{{ route('step-increment.index', ['tab' => 'magna_carta']) }}" class="si-stat-card">
            <div class="si-stat-num" style="color: #e11d48;">{{ number_format($stats['magna_carta']) }}</div>
            <div class="si-stat-label"><i class="bi bi-heart-pulse-fill" style="color: #e11d48;"></i> Magna Carta</div>
        </a>
    </div>

    {{-- â–Œâ–Œ TABS â–Œâ–Œ --}}
    @php $activeTab = $tab ?? 'overdue'; @endphp
    <div class="si-tab-bar" style="flex-wrap:wrap;">
        <a href="{{ route('step-increment.office-report', ['type' => 'permanent', 'mode' => 'annual']) }}" class="si-tab"
            style="color:#0f766e;background:#f0fdfa;border-color:#0d9488;">
            <i class="bi bi-file-earmark-spreadsheet-fill"></i> Annual Plantilla Basis
        </a>
        <a href="{{ route('step-increment.office-report', ['type' => 'permanent', 'mode' => 'nosi']) }}" class="si-tab"
            style="color:#059669;background:#ecfdf5;border-color:#10b981;">
            <i class="bi bi-file-earmark-diff-fill"></i> NOSI/NOLP Plantilla Basis
        </a>
        <a href="{{ route('step-increment.index', ['tab' => 'due']) }}"
            class="si-tab {{ $activeTab === 'due' ? 'active-due' : '' }}"
            style="{{ $activeTab === 'due' ? 'border-color:#ea580c;color:#ea580c;background:#fff7ed;' : '' }}">
            <i class="bi bi-alarm"></i> Due This Month
            @if($stats['due'])
                <span class="si-badge-pill" style="background:#ea580c;">{{ $stats['due'] }}</span>
            @endif
        </a>
        <a href="{{ route('step-increment.index', ['tab' => 'nosi']) }}"
            class="si-tab {{ $activeTab === 'nosi' ? 'active-nosi' : '' }}">
            <i class="bi bi-file-earmark-text-fill"></i> NOSI
        </a>
        <a href="{{ route('step-increment.index', ['tab' => 'nolp']) }}"
            class="si-tab {{ $activeTab === 'nolp' ? 'active-nolp' : '' }}">
            <i class="bi bi-file-medical-fill"></i> NOLP
        </a>
        <a href="{{ route('step-increment.index', ['tab' => 'upcoming_nolp']) }}"
            class="si-tab {{ in_array($activeTab, ['upcoming_nolp', 'upcoming_nosi']) ? 'active-upcoming' : '' }}">
            <i class="bi bi-calendar-event"></i> Upcoming
            @if($stats['upcoming'])
                <span class="si-badge-pill" style="background:#d97706;">{{ $stats['upcoming'] }}</span>
            @endif
        </a>
        <a href="{{ route('step-increment.index', ['tab' => 'magna_carta']) }}"
            class="si-tab {{ $activeTab === 'magna_carta' ? 'active-due' : '' }}"
            style="{{ $activeTab === 'magna_carta' ? 'border-color:#e11d48;color:#e11d48;background:#fff1f2;' : '' }}">
            <i class="bi bi-heart-pulse-fill" style="color:#e11d48;"></i> Magna Carta
            @if($stats['magna_carta'])
                <span class="si-badge-pill" style="background:#e11d48;">{{ $stats['magna_carta'] }}</span>
            @endif
        </a>
        <a href="{{ route('step-increment.index', ['tab' => 'history']) }}"
            class="si-tab {{ $activeTab === 'history' ? 'active-loyalty' : '' }}">
            <i class="bi bi-clock-history"></i> Increment History
        </a>
    </div>

    {{-- â–Œâ–Œ TABLE â–Œâ–Œ --}}
    @php
        $records = match ($activeTab) {
            'due' => $due,
            'upcoming_nolp' => $upcomingNolp,
            'upcoming_nosi' => $upcomingNosi,
            'magna_carta' => $magnaCartaDue,
            'nosi' => $overdue->merge($due),
            'nolp' => $overdue->merge($due),
            'history' => $histories,
            default => $overdue,
        };
    @endphp
    <div class="si-table-wrap">
        @if($activeTab === 'due')
            <div
                style="padding: 16px; background: #fff; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 13px; color: #475569;">
                    <i class="bi bi-alarm" style="color:#ea580c;"></i>
                    Employees whose step increment is <strong>due this month</strong> ({{ now()->format('F Y') }}).
                </div>
            </div>
        @elseif(in_array($activeTab, ['upcoming_nolp', 'upcoming_nosi']))
            {{-- Sub-tab bar for Upcoming --}}
            <div
                style="padding:14px 16px;background:#fff;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span style="font-size:13px;color:#475569;">
                    <i class="bi bi-calendar-event" style="color:#d97706;"></i>
                    Employees due for step increment in the <strong>next 6 months</strong>.
                </span>
                <div style="margin-left:auto;display:flex;gap:8px;">
                    <a href="{{ route('step-increment.index', ['tab' => 'upcoming_nolp']) }}"
                        style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;{{ $activeTab === 'upcoming_nolp' ? 'background:linear-gradient(135deg,#6d28d9,#8b5cf6);color:#fff;box-shadow:0 2px 8px rgba(109,40,217,.25);' : 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;' }}">
                        <i class="bi bi-hospital-fill"></i> NOLP (Hospital)
                        @if($stats['upcoming_nolp'])
                            <span
                                style="background:rgba(255,255,255,.3);padding:1px 7px;border-radius:99px;">{{ $stats['upcoming_nolp'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('step-increment.index', ['tab' => 'upcoming_nosi']) }}"
                        style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;{{ $activeTab === 'upcoming_nosi' ? 'background:linear-gradient(135deg,#0369a1,#0ea5e9);color:#fff;box-shadow:0 2px 8px rgba(3,105,161,.25);' : 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;' }}">
                        <i class="bi bi-person-workspace"></i> NOSI (Non-Hospital)
                        @if($stats['upcoming_nosi'])
                            <span
                                style="background:rgba(255,255,255,.3);padding:1px 7px;border-radius:99px;">{{ $stats['upcoming_nosi'] }}</span>
                        @endif
                    </a>
                </div>
            </div>
        @elseif($activeTab === 'nosi')
            <div style="padding: 16px; background: #fff; border-bottom: 1px solid #e5e7eb;">
                <div style="font-size: 13px; color: #475569;">
                    <i class="bi bi-file-earmark-text-fill" style="color:#0369a1;"></i>
                    Generate <strong>Notice of Step Increment (NOSI)</strong> certificates for eligible employees.
                </div>
            </div>
        @elseif($activeTab === 'nolp')
            <div style="padding: 16px; background: #fff; border-bottom: 1px solid #e5e7eb;">
                <div style="font-size: 13px; color: #475569;">
                    <i class="bi bi-file-medical-fill" style="color:#6d28d9;"></i>
                    Generate <strong>Notice of Longevity Pay (NOLP)</strong> certificates for hospital/medical personnel.
                </div>
            </div>
        @elseif($activeTab === 'history')
            <div
                style="padding:14px 16px;background:#fff;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                <div style="font-size:13px;color:#475569;">
                    <i class="bi bi-clock-history" style="color:#4338ca;"></i>
                    Complete log of all <strong>NOSI</strong>, <strong>NOLP</strong>, and <strong>NOSA</strong> issuances.
                </div>
                <form method="GET" action="{{ route('step-increment.index') }}"
                    style="display:flex;align-items:center;gap:8px;">
                    <input type="hidden" name="tab" value="history">
                    <input type="text" name="history_search" value="{{ $historySearch ?? '' }}"
                        placeholder="Search by name or item..."
                        style="border:1px solid #cbd5e1;border-radius:8px;padding:7px 12px;font-size:12px;width:220px;outline:none;">
                    <button type="submit"
                        style="background:#3b82f6;color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer;"><i
                            class="bi bi-search"></i></button>
                    @if($historySearch)
                        <a href="{{ route('step-increment.index', ['tab' => 'history']) }}"
                            style="color:#ef4444;font-size:12px;text-decoration:none;"><i class="bi bi-x-circle-fill"></i></a>
                    @endif
                </form>
            </div>
        @else
            <div style="padding: 16px; background: #fff; border-bottom: 1px solid #e5e7eb;">
                <div style="font-size: 13px; color: #475569;">
                    Showing employees turning due for a step increment within the next 6 months.
                </div>
            </div>
        @endif

        <div class="overflow-x-auto">
            @if($activeTab === 'upcoming_nolp' || $activeTab === 'upcoming_nosi')
                {{-- â”€â”€ UPCOMING SUB-TAB TABLE
                â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                --}}
                <table class="si-table">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Employee</th>
                            <th style="min-width:160px;">Position</th>
                            <th class="tc" style="width:56px;">SG</th>
                            <th class="tc" style="width:90px;">Step</th>
                            <th class="tc" style="width:130px;">Annual Salary</th>
                            <th class="tc" style="width:140px;">Due Date</th>
                            <th class="tc" style="width:100px;">Notice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            @php $dueDate = $record->next_step_due_date; @endphp
                            <tr>
                                <td>
                                    <div class="si-emp-name">{{ $record->full_name }}</div>
                                    <div class="si-emp-unit"><i class="bi bi-building"
                                            style="color:{{ $activeTab === 'upcoming_nolp' ? '#8b5cf6' : '#0ea5e9' }};"></i>
                                        {{ $record->organizational_unit }}</div>
                                </td>
                                <td>
                                    <div class="si-pos-title">{{ $record->position_title }}</div>
                                    <div class="si-pos-item">{{ $record->item }}</div>
                                </td>
                                <td class="tc"><span class="si-sg-badge">{{ $record->salary_grade }}</span></td>
                                <td class="tc">
                                    <span
                                        style="display:inline-flex;align-items:center;justify-content:center;background:#f1f5f9;border:1px solid #e2e8f0;color:#475569;font-size:12px;font-weight:700;padding:4px 12px;border-radius:8px;">Step
                                        {{ $record->step }}</span>
                                </td>
                                <td class="tc"><span
                                        class="si-salary">&#8369;{{ number_format($record->actual_annual_salary, 2) }}</span>
                                </td>
                                <td class="tc">
                                    @if($dueDate)
                                        <div class="si-due-upcoming">
                                            <i class="bi bi-calendar2-check me-1" style="font-size:12px;"></i>
                                            {{ $dueDate->format('M d, Y') }}
                                        </div>
                                        <div style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $dueDate->diffForHumans() }}
                                        </div>
                                    @else â€”
                                    @endif
                                </td>
                                <td class="tc">
                                    @if($activeTab === 'upcoming_nolp')
                                        <a href="{{ route('step-increment.pdf.nolp', $record) }}" target="_blank"
                                            style="display:inline-flex;align-items:center;gap:4px;background:linear-gradient(135deg,#6d28d9,#8b5cf6);color:#fff;padding:6px 12px;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;">
                                            <i class="bi bi-printer-fill"></i> NOLP
                                        </a>
                                    @else
                                        <a href="{{ route('step-increment.pdf.nosi', $record) }}" target="_blank"
                                            style="display:inline-flex;align-items:center;gap:4px;background:linear-gradient(135deg,#0369a1,#0ea5e9);color:#fff;padding:6px 12px;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;">
                                            <i class="bi bi-printer-fill"></i> NOSI
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="si-empty">
                                        <div class="si-empty-icon"><i class="bi bi-calendar-check"></i></div>
                                        <div style="font-size:15px;font-weight:700;color:#374151;margin-bottom:6px;">No upcoming
                                            increments</div>
                                        <div style="font-size:13px;color:#94a3b8;">No
                                            {{ $activeTab === 'upcoming_nolp' ? 'hospital/NOLP' : 'non-hospital/NOSI' }} employees
                                            are due within the next 6 months.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($activeTab === 'history')
                {{-- â”€â”€ INCREMENT HISTORY TABLE
                â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                --}}
                <table class="si-table">
                    <thead>
                        <tr>
                            <th style="width:120px;">Effective Date</th>
                            <th>Employee</th>
                            <th class="tc" style="width:80px;">Type</th>
                            <th class="tc" style="width:130px;">Salary Grade</th>
                            <th class="tc" style="width:130px;">Step</th>
                            <th style="width:200px;text-align:right;">Annual Salary Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $log)
                            <tr>
                                <td style="font-weight:600;color:#1e293b;">
                                    {{ $log->effective_date ? $log->effective_date->format('M d, Y') : 'â€”' }}</td>
                                <td>
                                    @if($log->plantillaRecord)
                                        <div style="font-weight:700;color:#0f172a;font-size:13px;">
                                            {{ $log->plantillaRecord->full_name }}</div>
                                        <div style="font-size:11px;color:#64748b;margin-top:2px;">Item:
                                            {{ $log->plantillaRecord->item }} | {{ $log->plantillaRecord->position_title }}</div>
                                    @else
                                        <span style="color:#ef4444;font-size:12px;"><i class="bi bi-exclamation-triangle"></i>
                                            Record Removed</span>
                                    @endif
                                </td>
                                <td class="tc">
                                    <span class="history-type-{{ $log->type }}">{{ $log->type }}</span>
                                </td>
                                <td class="tc" style="font-family:ui-monospace,monospace;">
                                    @if($log->previous_salary_grade == $log->new_salary_grade)
                                        <span style="color:#475569;">SG {{ $log->new_salary_grade }}</span>
                                    @else
                                        <span style="color:#64748b;">SG {{ $log->previous_salary_grade }}</span>
                                        <i class="bi bi-chevron-right" style="color:#94a3b8;margin:0 4px;"></i>
                                        <span style="color:#16a34a;font-weight:700;">SG {{ $log->new_salary_grade }}</span>
                                    @endif
                                </td>
                                <td class="tc" style="font-family:ui-monospace,monospace;">
                                    @if($log->previous_step == $log->new_step)
                                        <span style="color:#475569;">Step {{ $log->new_step }}</span>
                                    @else
                                        <span style="color:#64748b;">Step {{ $log->previous_step }}</span>
                                        <i class="bi bi-chevron-right" style="color:#94a3b8;margin:0 4px;"></i>
                                        <span style="color:#16a34a;font-weight:700;">Step {{ $log->new_step }}</span>
                                    @endif
                                </td>
                                <td style="text-align:right;font-family:ui-monospace,monospace;">
                                    <span
                                        style="color:#64748b;font-size:12px;">&#8369;{{ number_format($log->previous_annual_salary, 2) }}</span>
                                    <i class="bi bi-chevron-right" style="color:#94a3b8;margin:0 4px;"></i>
                                    <span
                                        style="color:#16a34a;font-weight:700;">&#8369;{{ number_format($log->new_annual_salary, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="si-empty">
                                        <div class="si-empty-icon"><i class="bi bi-clock-history"></i></div>
                                        <div style="font-size:15px;font-weight:700;color:#374151;margin-bottom:6px;">No history
                                            found</div>
                                        <div style="font-size:13px;color:#94a3b8;">No step increment history records exist yet.
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($activeTab === 'loyalty')
                {{-- â”€â”€ LOYALTY INCENTIVE TABLE
                â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                --}}
                <table class="si-table">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Employee</th>
                            <th style="min-width:160px;">Position</th>
                            <th class="tc" style="width:56px;">SG</th>
                            <th class="tc" style="width:130px;">Govt. Service Start</th>
                            <th class="tc" style="width:70px;">Years</th>
                            <th class="tc" style="width:130px;">Milestone</th>
                            <th class="tc" style="width:120px;">Amount</th>
                            <th class="tc" style="width:180px;">Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            @php
                                $startDt = $record->date_original_appointment;
                                $yearsNum = $startDt ? (int) $startDt->diffInYears(now()) : 0;
                                if ($yearsNum === 10) {
                                    $milestoneYrs = 10;
                                    $loyaltyAmt = 10000;
                                } elseif ($yearsNum > 10) {
                                    $blocks = (int) floor(($yearsNum - 10) / 5);
                                    $milestoneYrs = 10 + ($blocks * 5);
                                    $loyaltyAmt = 5000;
                                } else {
                                    $milestoneYrs = $yearsNum;
                                    $loyaltyAmt = 0;
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="si-emp-name">{{ $record->full_name }}</div>
                                    <div class="si-emp-unit"><i class="bi bi-building" style="color:#c4b5fd;"></i>
                                        {{ $record->organizational_unit }}</div>
                                </td>
                                <td>
                                    <div class="si-pos-title">{{ $record->position_title }}</div>
                                    <div class="si-pos-item">{{ $record->item }}</div>
                                </td>
                                <td class="tc"><span class="si-sg-badge">{{ $record->salary_grade }}</span></td>
                                <td class="tc"><span
                                        style="font-size:12px;color:#374151;white-space:nowrap;">{{ $startDt ? $startDt->format('M d, Y') : 'â€”' }}</span>
                                </td>
                                <td class="tc">
                                    <span
                                        style="display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#b45309,#d97706);color:#fff;font-size:12px;font-weight:800;width:34px;height:34px;border-radius:8px;">{{ $yearsNum }}</span>
                                </td>
                                <td class="tc">
                                    @if($milestoneYrs === 10)
                                        <span
                                            style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:800;background:#f59e0b;color:#fff;padding:4px 12px;border-radius:99px;"><i
                                                class="bi bi-star-fill"></i>10 Years</span>
                                    @else
                                        <span
                                            style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:800;background:#b45309;color:#fff;padding:4px 12px;border-radius:99px;"><i
                                                class="bi bi-award-fill"></i>{{ $milestoneYrs }} Years</span>
                                    @endif
                                </td>
                                <td class="tc">
                                    <span
                                        style="font-family:ui-monospace,monospace;font-size:13px;font-weight:800;color:{{ $loyaltyAmt >= 10000 ? '#d97706' : '#b45309' }};">&#8369;{{ number_format($loyaltyAmt, 2) }}</span>
                                    <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                                        {{ $loyaltyAmt >= 10000 ? 'First 10 yrs' : '+5 yr block' }}</div>
                                </td>
                                <td class="tc" style="white-space:nowrap;">
                                    <a href="{{ route('step-increment.pdf.loyalty-incentive', $record) }}" target="_blank"
                                        style="display:inline-flex;align-items:center;gap:5px;background:linear-gradient(135deg,#b45309,#d97706,#f59e0b);color:#fff;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;">
                                        <i class="bi bi-printer-fill"></i> Print
                                    </a>
                                    <button type="button"
                                        onclick="openDismissModal({{ $record->id }}, '{{ addslashes($record->full_name) }}')"
                                        style="display:inline-flex;align-items:center;gap:5px;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;padding:7px 12px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;margin-left:5px;">
                                        <i class="bi bi-trash3-fill"></i> Remove
                                    </button>
                                    <form id="dismiss-form-{{ $record->id }}" method="POST"
                                        action="{{ route('step-increment.dismiss-loyalty', $record) }}" style="display:none;">
                                        @csrf</form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="si-empty">
                                        <div class="si-empty-icon"><i class="bi bi-award"></i></div>
                                        <div style="font-size:16px;font-weight:700;color:#92400e;margin-bottom:6px;">No
                                            employees due</div>
                                        <div style="font-size:13px;color:#94a3b8;">No employees have reached a loyalty incentive
                                            milestone (10, 15, 20... years).</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($activeTab === 'nosi')
                {{-- â”€â”€ NOSI TABLE
                â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                --}}
                <table class="si-table">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Employee</th>
                            <th style="min-width:160px;">Position</th>
                            <th class="tc" style="width:56px;">SG</th>
                            <th class="tc" style="width:130px;">Step Progress</th>
                            <th class="tc" style="width:130px;">Annual Salary</th>
                            <th class="tc" style="width:130px;">Due Date</th>
                            <th class="tc" style="width:110px;">NOSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $nosiRecords = $overdue->merge($due)->filter(fn($r) => !$r->is_hospital_personnel); @endphp
                        @forelse($nosiRecords as $record)
                            @php
                                $dueType = $record->due_type;
                                $stepIncrease = ($dueType === 'nolp' || $dueType === 'both') ? 2 : 1;
                                $newStep = min(8, $record->step + $stepIncrease);
                                $dueDate = $record->next_step_due_date;
                            @endphp
                            <tr>
                                <td>
                                    <div class="si-emp-name">{{ $record->full_name }}</div>
                                    <div class="si-emp-unit"><i class="bi bi-building" style="color:#c4b5fd;"></i>
                                        {{ $record->organizational_unit }}</div>
                                </td>
                                <td>
                                    <div class="si-pos-title">{{ $record->position_title }}</div>
                                    <div class="si-pos-item">{{ $record->item }}</div>
                                </td>
                                <td class="tc"><span class="si-sg-badge">{{ $record->salary_grade }}</span></td>
                                <td class="tc">
                                    <div class="si-step-flow">
                                        <span class="si-step-cur">Step {{ $record->step }}</span>
                                        <span class="si-step-arrow"><i class="bi bi-arrow-right"></i></span>
                                        <span class="si-step-next">Step {{ $newStep }}</span>
                                    </div>
                                </td>
                                <td class="tc"><span
                                        class="si-salary">&#8369;{{ number_format($record->actual_annual_salary, 2) }}</span>
                                </td>
                                <td class="tc">
                                    @if($dueDate)
                                        @if($dueDate->isPast())
                                            <div class="si-due-overdue">
                                                <i class="bi bi-exclamation-circle-fill me-1" style="font-size:12px;"></i>
                                                {{ $dueDate->format('M d, Y') }}
                                                <span class="si-ago">{{ $dueDate->diffForHumans() }}</span>
                                            </div>
                                        @else
                                            <div class="si-due-upcoming">
                                                <i class="bi bi-calendar2-check me-1" style="font-size:12px;"></i>
                                                {{ $dueDate->format('M d, Y') }}
                                            </div>
                                        @endif
                                    @else
                                        <span style="color:#94a3b8;">â€”</span>
                                    @endif
                                </td>
                                <td class="tc">
                                    <a href="{{ route('step-increment.pdf.nosi', $record) }}" target="_blank"
                                        style="display:inline-flex;align-items:center;gap:5px;white-space:nowrap;background:linear-gradient(135deg,#0369a1,#0ea5e9);color:#fff;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;box-shadow:0 2px 8px rgba(3,105,161,.25);">
                                        <i class="bi bi-printer-fill"></i> Print NOSI
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="si-empty">
                                        <div class="si-empty-icon"><i class="bi bi-file-earmark-text"></i></div>
                                        <div style="font-size:16px;font-weight:700;color:#0369a1;margin-bottom:6px;">No records
                                            found</div>
                                        <div style="font-size:13px;color:#94a3b8;">No employees are currently due or overdue for
                                            a step increment.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($activeTab === 'nolp')
                {{-- â”€â”€ NOLP TABLE
                â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                --}}
                <table class="si-table">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Employee</th>
                            <th style="min-width:160px;">Position</th>
                            <th class="tc" style="width:56px;">SG</th>
                            <th class="tc" style="width:130px;">Step Progress</th>
                            <th class="tc" style="width:130px;">Annual Salary</th>
                            <th class="tc" style="width:130px;">Due Date</th>
                            <th class="tc" style="width:110px;">NOLP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $nolpRecords = $overdue->merge($due)->filter(fn($r) => $r->is_hospital_personnel); @endphp
                        @forelse($nolpRecords as $record)
                            @php
                                $dueType = $record->due_type;
                                $stepIncrease = ($dueType === 'nolp' || $dueType === 'both') ? 2 : 1;
                                $newStep = min(8, $record->step + $stepIncrease);
                                $dueDate = $record->next_step_due_date;
                            @endphp
                            <tr>
                                <td>
                                    <div class="si-emp-name">{{ $record->full_name }}</div>
                                    <div class="si-emp-unit"><i class="bi bi-building" style="color:#c4b5fd;"></i>
                                        {{ $record->organizational_unit }}</div>
                                </td>
                                <td>
                                    <div class="si-pos-title">{{ $record->position_title }}</div>
                                    <div class="si-pos-item">{{ $record->item }}</div>
                                </td>
                                <td class="tc"><span class="si-sg-badge">{{ $record->salary_grade }}</span></td>
                                <td class="tc">
                                    <div class="si-step-flow">
                                        <span class="si-step-cur">Step {{ $record->step }}</span>
                                        <span class="si-step-arrow"><i class="bi bi-arrow-right"></i></span>
                                        <span class="si-step-next">Step {{ $newStep }}</span>
                                    </div>
                                </td>
                                <td class="tc"><span
                                        class="si-salary">&#8369;{{ number_format($record->actual_annual_salary, 2) }}</span>
                                </td>
                                <td class="tc">
                                    @if($dueDate)
                                        @if($dueDate->isPast())
                                            <div class="si-due-overdue">
                                                <i class="bi bi-exclamation-circle-fill me-1" style="font-size:12px;"></i>
                                                {{ $dueDate->format('M d, Y') }}
                                                <span class="si-ago">{{ $dueDate->diffForHumans() }}</span>
                                            </div>
                                        @else
                                            <div class="si-due-upcoming">
                                                <i class="bi bi-calendar2-check me-1" style="font-size:12px;"></i>
                                                {{ $dueDate->format('M d, Y') }}
                                            </div>
                                        @endif
                                    @else
                                        <span style="color:#94a3b8;">â€”</span>
                                    @endif
                                </td>
                                <td class="tc">
                                    <a href="{{ route('step-increment.pdf.nolp', $record) }}" target="_blank"
                                        style="display:inline-flex;align-items:center;gap:5px;white-space:nowrap;background:linear-gradient(135deg,#6d28d9,#8b5cf6);color:#fff;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;box-shadow:0 2px 8px rgba(109,40,217,.25);">
                                        <i class="bi bi-printer-fill"></i> Print NOLP
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="si-empty">
                                        <div class="si-empty-icon"><i class="bi bi-file-medical"></i></div>
                                        <div style="font-size:16px;font-weight:700;color:#6d28d9;margin-bottom:6px;">No hospital
                                            personnel due</div>
                                        <div style="font-size:13px;color:#94a3b8;">No hospital/medical personnel are currently
                                            due or overdue for a longevity pay increment.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($activeTab === 'magna_carta')
                {{-- â”€â”€ MAGNA CARTA NOSA TABLE
                â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                --}}
                <table class="si-table">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Employee</th>
                            <th style="min-width:160px;">Position</th>
                            <th class="tc" style="width:56px;">SG</th>
                            <th class="tc" style="width:130px;">Step Progress</th>
                            <th class="tc" style="width:130px;">Annual Salary</th>
                            <th class="tc" style="width:130px;">65th Birthday</th>
                            <th class="tc" style="width:110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($magnaCartaDue as $record)
                            @php
                                $newSg = min(33, $record->salary_grade + 1);
                                $newStep = $record->step;
                                $bday = $record->date_of_birth->copy()->addYears(65);
                            @endphp
                            <tr>
                                <td>
                                    <div class="si-emp-name">{{ $record->full_name }}</div>
                                    <div class="si-emp-unit"><i class="bi bi-building" style="color:#f43f5e;"></i>
                                        {{ $record->organizational_unit }}</div>
                                </td>
                                <td>
                                    <div class="si-pos-title">{{ $record->position_title }}</div>
                                    <div class="si-pos-item">{{ $record->item }}</div>
                                </td>
                                <td class="tc">
                                    <div class="si-step-flow">
                                        <span class="si-step-cur" style="background:#ffe4e6;color:#e11d48;">SG
                                            {{ $record->salary_grade }}</span>
                                        <span class="si-step-arrow"><i class="bi bi-arrow-right"></i></span>
                                        <span class="si-step-next"
                                            style="background:linear-gradient(135deg,#e11d48,#be123c);">SG {{ $newSg }}</span>
                                    </div>
                                </td>
                                <td class="tc">
                                    <span class="si-sg-badge"
                                        style="background:#f1f5f9;color:#475569;box-shadow:none;border:1px solid #cbd5e1;">Step
                                        {{ $record->step }}</span>
                                </td>
                                <td class="tc"><span
                                        class="si-salary">&#8369;{{ number_format($record->actual_annual_salary, 2) }}</span>
                                </td>
                                <td class="tc">
                                    <div class="si-due-upcoming" style="color:#e11d48;">
                                        <i class="bi bi-calendar-event me-1" style="font-size:12px;"></i>
                                        {{ $bday->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="tc">
                                    @if($record->salary_grade < 33)
                                        <form method="POST" action="{{ route('step-increment.process-magna-carta', $record) }}"
                                            onsubmit="return confirm('Process Magna Carta NOSA? This will increase their Salary Grade by 1.');">
                                            @csrf
                                            <button type="submit" class="si-process-btn"
                                                style="background:linear-gradient(135deg,#e11d48,#be123c);box-shadow:0 2px 8px rgba(225,29,72,.3);">
                                                <i class="bi bi-heart-pulse-fill"></i> Add 1 SG
                                            </button>
                                        </form>
                                    @else
                                        <span style="font-size:11px;color:#94a3b8;"><i class="bi bi-x-circle me-1"></i> Max
                                            SG</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="si-empty">
                                        <div class="si-empty-icon" style="background:#ffe4e6;color:#e11d48;"><i
                                                class="bi bi-heart-pulse"></i></div>
                                        <div style="font-size:16px;font-weight:700;color:#e11d48;margin-bottom:6px;">No records
                                            found</div>
                                        <div style="font-size:13px;color:#94a3b8;">No Health Workers are currently 3 months away
                                            from retirement.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            @else
                {{-- â”€â”€ STEP INCREMENT TABLE (existing)
                â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
                <table class="si-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Position</th>
                            <th class="tc" style="width:56px;">SG</th>
                            <th class="tc" style="width:130px;">Step Progress</th>
                            <th class="tc" style="width:130px;">Annual Salary</th>
                            <th class="tc" style="width:130px;">Due Date</th>
                            <th class="tc" style="width:100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                {{-- Employee --}}
                                <td>
                                    <div class="si-emp-name">{{ $record->full_name }}</div>
                                    <div class="si-emp-unit">
                                        <i class="bi bi-building" style="color:#c4b5fd;"></i>
                                        {{ $record->organizational_unit }}
                                    </div>
                                </td>

                                {{-- Position --}}
                                <td>
                                    <div class="si-pos-title">{{ $record->position_title }}</div>
                                    <div class="si-pos-item">{{ $record->item }}</div>
                                </td>

                                {{-- SG --}}
                                <td class="tc">
                                    <span class="si-sg-badge">{{ $record->salary_grade }}</span>
                                </td>

                                {{-- Step Progress --}}
                                <td class="tc">
                                    @php
                                        $dueType = $record->due_type;
                                        $stepIncrease = ($dueType === 'nolp' || $dueType === 'both') ? 2 : 1;
                                        $newStep = min(8, $record->step + $stepIncrease);
                                    @endphp
                                    <div class="si-step-flow">
                                        <span class="si-step-cur">Step {{ $record->step }}</span>
                                        <span class="si-step-arrow"><i class="bi bi-arrow-right"></i></span>
                                        <span class="si-step-next">Step {{ $newStep }}</span>
                                    </div>
                                </td>

                                {{-- Salary --}}
                                <td class="tc">
                                    <span class="si-salary">&#8369;{{ number_format($record->actual_annual_salary, 2) }}</span>
                                </td>

                                {{-- Due Date --}}
                                <td class="tc">
                                    @php $dueDate = $record->next_step_due_date; @endphp
                                    @if($dueDate)
                                        @if($dueDate->isPast())
                                            <div class="si-due-overdue">
                                                <i class="bi bi-exclamation-circle-fill me-1" style="font-size:12px;"></i>
                                                {{ $dueDate->format('M d, Y') }}
                                                <span class="si-ago">{{ $dueDate->diffForHumans() }}</span>
                                            </div>
                                        @else
                                            <div class="si-due-upcoming">
                                                <i class="bi bi-calendar2-check me-1" style="font-size:12px;"></i>
                                                {{ $dueDate->format('M d, Y') }}
                                            </div>
                                        @endif
                                        <div style="margin-top: 4px;">
                                            @if($dueType === 'both')
                                                <span
                                                    style="font-size:10px; font-weight:800; background:#f59e0b; color:#fff; padding:2px 6px; border-radius:4px;">NOSI
                                                    & NOLP (+2 Steps)</span>
                                            @elseif($dueType === 'nolp')
                                                <span
                                                    style="font-size:10px; font-weight:800; background:#8b5cf6; color:#fff; padding:2px 6px; border-radius:4px;">NOLP
                                                    (5 Yrs)</span>
                                            @elseif($dueType === 'nosi')
                                                <span
                                                    style="font-size:10px; font-weight:800; background:#0ea5e9; color:#fff; padding:2px 6px; border-radius:4px;">NOSI
                                                    (3 Yrs)</span>
                                            @endif
                                        </div>
                                    @else
                                        <span style="color:#94a3b8;">â€”</span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="tc">
                                    @if($record->step < 8)
                                        {{-- Hidden form submitted by modal --}}
                                        <form id="form-process-{{ $record->id }}" method="POST"
                                            action="{{ route('step-increment.process', $record) }}" style="display:none;">
                                            @csrf
                                        </form>
                                        <button type="button" class="si-process-btn" onclick="openProcessModal({
                                            formId: 'form-process-{{ $record->id }}',
                                            name: '{{ addslashes($record->full_name) }}',
                                            unit: '{{ addslashes($record->organizational_unit) }}',
                                            position: '{{ addslashes($record->position_title) }}',
                                            sg: {{ $record->salary_grade }},
                                            stepFrom: {{ $record->step }},
                                            stepTo: {{ $newStep }},
                                            dueType: '{{ $dueType }}',
                                            salary: '&#8369;{{ number_format($record->actual_annual_salary, 2) }}',
                                            dueDate: '{{ $dueDate ? $dueDate->format("M d, Y") : "â€”" }}'
                                        })">
                                            <i class="bi bi-lightning-fill"></i> Process
                                        </button>
                                    @else
                                        <span
                                            style="display:inline-flex;align-items:center;gap:4px;background:#f0fdf4;color:#16a34a;font-size:11px;font-weight:700;padding:5px 10px;border-radius:99px;border:1px solid #bbf7d0;">
                                            <i class="bi bi-trophy-fill"></i> Max Step
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="si-empty">
                                        <div class="si-empty-icon"><i class="bi bi-check2-circle"></i></div>
                                        <div style="font-size:16px;font-weight:700;color:#312e81;margin-bottom:6px;">
                                            All clear!
                                        </div>
                                        <div style="font-size:13px;color:#94a3b8;">
                                            No employees
                                            {{ $activeTab === 'due' ? 'are currently due for increment' : 'have upcoming increments in the next 6 months' }}.
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Pagination --}}
        @if($records instanceof \Illuminate\Pagination\AbstractPaginator && $records->hasPages())
            <div style="padding:14px 20px;border-top:1px solid #f1f5f9;">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    {{-- â–Œâ–Œ HIDDEN FORM FOR PROCESS ALL â–Œâ–Œ --}}
    <form id="form-process-all" action="{{ route('step-increment.process-all') }}" method="POST" style="display:none;">
        @csrf
    </form>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let _currentFormId = null;

        function openProcessModal(data) {
            _currentFormId = data.formId;

            const typeColors = {
                nosi: '#0ea5e9',
                nolp: '#8b5cf6',
                both: '#f59e0b',
            };

            // Construct HTML for the SweetAlert content
            const htmlContent = `
        <div style="text-align: left; margin-top: 10px;">
            <div style="display:flex; align-items:center; gap:14px; margin-bottom:20px;">
                <div style="width:48px; height:48px; border-radius:12px; background:linear-gradient(135deg,#e0e7ff,#c7d2fe); display:flex; align-items:center; justify-content:center; font-size:20px; color:#4338ca; flex-shrink:0;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <div style="font-weight:800; font-size:15px; color:#0f172a;">${data.name}</div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px;">${data.unit}</div>
                    <div style="font-size:12px; color:#64748b; margin-top:1px;">${data.position}</div>
                </div>
            </div>
            
            <div style="background:#f8fafc; border-radius:12px; padding:16px; display:flex; align-items:center; justify-content:center; gap:16px; margin-bottom:20px;">
                <div style="text-align:center;">
                    <div style="font-size:11px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px;">Current Step</div>
                    <div style="font-size:32px; font-weight:900; color:#4338ca;">${data.stepFrom}</div>
                </div>
                <div style="color:#c4b5fd; font-size:28px;"><i class="bi bi-arrow-right-circle-fill"></i></div>
                <div style="text-align:center;">
                    <div style="font-size:11px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px;">New Step</div>
                    <div style="font-size:32px; font-weight:900; color:#16a34a;">${data.stepTo}</div>
                </div>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:20px;">
                <div style="background:#f0fdf4; border-radius:10px; padding:12px;">
                    <div style="font-size:10px; color:#94a3b8; font-weight:700; text-transform:uppercase; letter-spacing:.5px;">Annual Salary</div>
                    <div style="font-size:13px; font-weight:800; color:#166534; margin-top:4px; font-family:ui-monospace,monospace;">${data.salary}</div>
                </div>
                <div style="background:#eff6ff; border-radius:10px; padding:12px;">
                    <div style="font-size:10px; color:#94a3b8; font-weight:700; text-transform:uppercase; letter-spacing:.5px;">Due Date</div>
                    <div style="font-size:13px; font-weight:700; color:#1d4ed8; margin-top:4px;">${data.dueDate}</div>
                </div>
            </div>
            
            <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:12px 14px; font-size:13px; color:#c2410c; display:flex; gap:8px; align-items:flex-start;">
                <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0; margin-top:2px;"></i>
                <span>This will update the employee's step and salary record. This <strong>cannot be undone</strong> without manual correction.</span>
            </div>
        </div>
    `;

            Swal.fire({
                title: 'Step Increment Processing',
                html: htmlContent,
                icon: 'question',
                iconColor: typeColors[data.dueType] || '#312e81',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-lightning-charge-fill me-1"></i> Confirm & Process',
                cancelButtonText: 'Cancel',
                width: 500
            }).then((result) => {
                if (result.isConfirmed) {
                    submitProcessForm();
                } else {
                    _currentFormId = null;
                }
            });
        }

        function submitProcessForm() {
            if (_currentFormId) document.getElementById(_currentFormId).submit();
        }

        function openProcessAllModal(count) {
            const htmlContent = `
        <div style="text-align: center; margin-top: 10px;">
            <div style="background:#f8fafc; border-radius:12px; padding:20px; margin-bottom:20px;">
                <div style="font-size:56px; font-weight:900; color:#4338ca; line-height:1;">${count}</div>
                <div style="font-size:14px; color:#64748b; font-weight:600; margin-top:8px;">employees will be processed</div>
                
                <div style="margin-top:16px; display:flex; justify-content:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:12px; font-weight:700; background:#0ea5e9; color:#fff; padding:4px 12px; border-radius:99px;">NOSI +1 Step</span>
                    <span style="font-size:12px; font-weight:700; background:#8b5cf6; color:#fff; padding:4px 12px; border-radius:99px;">NOLP +2 Steps</span>
                </div>
            </div>
            
            <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:14px; font-size:13px; color:#991b1b; display:flex; gap:10px; align-items:flex-start; text-align: left;">
                <i class="bi bi-shield-exclamation" style="flex-shrink:0; margin-top:2px; font-size:16px;"></i>
                <span>This will automatically process <strong>all eligible employees</strong> according to their NOSI or NOLP type. Steps and salaries will be updated immediately. This action <strong>cannot be undone</strong>.</span>
            </div>
        </div>
    `;

            Swal.fire({
                title: 'Bulk Processing All Due',
                html: htmlContent,
                icon: 'warning',
                iconColor: '#4338ca',
                showCancelButton: true,
                confirmButtonColor: '#4338ca',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-lightning-charge-fill me-1"></i> Confirm Bulk Process',
                cancelButtonText: 'Cancel',
                width: 500
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-process-all').submit();
                }
            });
        }

        function openDismissModal(recordId, employeeName) {
            Swal.fire({
                title: 'Remove from Loyalty Incentive List?',
                html: `
            <div style="text-align:center; margin-top:10px;">
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:16px 20px; margin-bottom:16px;">
                    <div style="font-size:15px; font-weight:800; color:#1e293b; margin-bottom:4px;">${employeeName}</div>
                    <div style="font-size:13px; color:#64748b;">will be removed from the Loyalty Incentive list.</div>
                </div>
                <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:12px; font-size:13px; color:#9a3412; display:flex; gap:10px; align-items:flex-start; text-align:left;">
                    <i class="bi bi-info-circle-fill" style="flex-shrink:0; margin-top:2px;"></i>
                    <span>This only removes the employee from this view. Their record is <strong>not deleted</strong>. You can restore them later if needed.</span>
                </div>
            </div>`,
                icon: 'warning',
                iconColor: '#dc2626',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Yes, Remove',
                cancelButtonText: 'Cancel',
                width: 480
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('dismiss-form-' + recordId).submit();
                }
            });
        }
    </script>
</x-dashboard-app>