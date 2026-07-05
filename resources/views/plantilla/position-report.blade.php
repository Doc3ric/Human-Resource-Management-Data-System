<x-dashboard-app>
    <style>
        /* ── Hero ───────────────────────────────────────────────────────────── */
        .pr-hero {
            background: linear-gradient(135deg, #1e3a5f 0%, #1a5276 55%, #0e6655 100%);
            border-radius: 14px;
            padding: 24px 32px;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .pr-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .08) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .pr-hero-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            width: 100%;
            flex-wrap: wrap;
        }

        .pr-hero h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            margin: 0;
        }

        .pr-hero p {
            color: rgba(255, 255, 255, .7);
            font-size: 12px;
            margin: 4px 0 0;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .25);
            color: #fff;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, .28);
            color: #fff;
        }

        /* ── Selector card ──────────────────────────────────────────────────── */
        .selector-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
        }

        .selector-card form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }

        .selector-card label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .selector-card select {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 13px;
            outline: none;
            min-width: 260px;
            flex: 1;
            transition: border .15s;
        }

        .selector-card select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1e3a5f;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-primary:hover {
            background: #1a5276;
        }

        /* ── Report preview card ────────────────────────────────────────────── */
        .report-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
            overflow: hidden;
        }

        .report-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* ── MFMP Form layout (preview) ─────────────────────────────────────── */
        .form-ref-label {
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            letter-spacing: .5px;
        }

        .report-title-h {
            text-align: center;
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            margin: 10px 0 4px;
        }

        .report-province {
            font-size: 12px;
            color: #374151;
            margin-bottom: 12px;
        }

        /* ── Main form table ────────────────────────────────────────────────── */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .form-table th,
        .form-table td {
            border: 1px solid #9ca3af;
            padding: 7px 10px;
            vertical-align: middle;
            text-align: center;
        }

        .form-table thead .th-main {
            background: #dbeafe;
            font-weight: 700;
            font-size: 11px;
            color: #1e3a5f;
        }

        .form-table thead .th-sub {
            background: #eff6ff;
            font-weight: 600;
            font-size: 11px;
            color: #1e3a5f;
        }

        .form-table td.name-col {
            text-align: left;
            font-weight: 600;
            color: #0f172a;
        }

        .form-table td.pos-col {
            text-align: left;
            color: #374151;
        }

        .form-table td.date-col {
            font-size: 11px;
            color: #374151;
        }

        .form-table td.perm-col {
            font-size: 16px;
            font-weight: 800;
            color: #1d4ed8;
        }

        .form-table tbody tr:hover td {
            background: #f0f9ff;
        }

        .form-table .empty-row td {
            height: 28px;
        }

        /* ── Footer signature area ──────────────────────────────────────────── */
        .sig-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 22px 20px 16px;
        }

        .sig-block .sig-label {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 18px;
        }

        .sig-block .sig-line {
            border-top: 1.5px solid #374151;
            padding-top: 4px;
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
        }

        .sig-block .sig-date {
            font-size: 11px;
            color: #6b7280;
            margin-top: 8px;
        }

        /* ── Instructions box ───────────────────────────────────────────────── */
        .instructions-box {
            border: 2px solid #374151;
            border-radius: 8px;
            padding: 14px 18px;
            margin: 16px 20px 20px;
            font-size: 11.5px;
        }

        .instructions-title {
            text-align: center;
            font-weight: 800;
            font-size: 12px;
            color: #0f172a;
            margin-bottom: 10px;
        }

        .instr-row {
            display: flex;
            gap: 8px;
            margin-bottom: 5px;
        }

        .instr-label {
            font-weight: 700;
            min-width: 75px;
            color: #374151;
        }

        .instr-text {
            color: #4b5563;
            line-height: 1.45;
        }

        /* ── Empty state ────────────────────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            display: block;
            margin-bottom: 14px;
            color: #d1d5db;
        }

        .empty-state p {
            font-size: 14px;
        }
    </style>

    {{-- ▌▌ HERO ▌▌ --}}
    <div class="pr-hero">
        <div class="pr-hero-inner">
            <div>
                <h1><i class="bi bi-file-text-fill me-2"></i>Position Personnel Report</h1>
                <p>MFMP 001-2023 · Report on the Status of Personnel by Position</p>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                @if(!empty($position))
                    <button type="submit" form="position-report-form" formaction="{{ route('plantilla.position-report.export.pdf') }}"
                        style="border:none;cursor:pointer;display:inline-flex;align-items:center;gap:5px;background:#dc2626;color:#fff;padding:8px 14px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </button>
                    <button type="submit" form="position-report-form" formaction="{{ route('plantilla.position-report.export.excel') }}"
                        style="border:none;cursor:pointer;display:inline-flex;align-items:center;gap:5px;background:#16a34a;color:#fff;padding:8px 14px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </button>
                @endif
                <a href="{{ route('plantilla.index') }}" class="back-btn">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>
    </div>

    {{-- ▌▌ POSITION SELECTOR ▌▌ --}}
    <div class="selector-card">
        <form method="GET" action="{{ route('plantilla.position-report') }}" id="position-report-form">
            <div style="flex:1;">
                <label>Select Position</label>
                <select name="position[]" multiple="multiple" id="position-select">
                    @foreach($positions as $pos)
                        <option value="{{ $pos }}" {{ in_array($pos, $position) ? 'selected' : '' }}>
                            {{ $pos }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i> Generate Report
            </button>
        </form>
    </div>

    {{-- ▌▌ REPORT PREVIEW (only shown when a position is selected) ▌▌ --}}
    @if(!empty($position))
        @php
            if (count($position) === 1) {
                $posUpper = strtoupper($position[0]);
                $words = explode(' ', $posUpper);
                $lastWord = end($words);
                $titleLbl = implode(' ', $words) . (str_ends_with($lastWord, 'S') ? '' : 'S');
                $reportFor = $position[0];
            } else {
                $titleLbl = 'VARIOUS POSITIONS';
                $reportFor = 'Various Positions (' . count($position) . ' selected)';
            }
        @endphp

        <div class="report-card">
            {{-- Card header with record count --}}
            <div class="report-card-header">
                <div style="font-size:14px;font-weight:700;color:#0f172a;">
                    <i class="bi bi-list-ul me-1"></i>
                    Report for: <span style="color:#1d4ed8;">{{ $reportFor }}</span>
                    &nbsp;·&nbsp;
                    <span style="font-size:13px;color:#6b7280;">{{ $records->count() }} record(s) found</span>
                </div>
            </div>

            <div style="padding: 16px 20px 0;">
                {{-- Form ref --}}
                <div class="form-ref-label">MFMP 001-2023</div>

                {{-- title --}}
                <div class="report-title-h">
                    REPORT ON THE STATUS ON {{ $titleLbl }} PERSONNEL
                </div>
                <div class="report-province">Province: Bukidnon</div>

                {{-- Main form table --}}
                <div style="overflow-x:auto;">
                    <table class="form-table">
                        <thead>
                            <tr>
                                <th class="th-main" rowspan="2" style="width:22%;">
                                    Name of Personnel<br><span style="font-weight:400;">(01)</span>
                                </th>
                                <th class="th-main" rowspan="2" style="width:16%;">
                                    Designation/Position<br><span style="font-weight:400;">(02)</span>
                                </th>
                                <th class="th-main" rowspan="2" style="width:24%;">
                                    Scope of Work/Tasks and<br>Responsibilities<br><span
                                        style="font-weight:400;">(03)</span>
                                </th>
                                <th class="th-main" colspan="3">
                                    Status of Appointment <span style="font-weight:400;">(04)</span>
                                </th>
                                <th class="th-main" rowspan="2" style="width:10%;">
                                    Remarks<br><span style="font-weight:400;">(5)</span>
                                </th>
                            </tr>
                            <tr>
                                <th class="th-sub" style="width:9%;">REGULAR<br><span
                                        style="font-weight:400;">(4.1)</span></th>
                                <th class="th-sub" style="width:9%;">Contractual<br><span
                                        style="font-weight:400;">(4.2)</span></th>
                                <th class="th-sub" style="width:16%;">Date of Appointment<br><span
                                        style="font-weight:400;">(4.3)</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $r)
                                @php
                                    $isPermanent = in_array(strtolower(trim($r->employment_status ?? '')), ['p', 'permanent']);
                                    $mi = $r->middle_name
                                        ? strtoupper(substr($r->middle_name, 0, 1)) . '.'
                                        : '';
                                    $fullName = trim(
                                        strtoupper($r->last_name ?? '')
                                        . ', '
                                        . ($r->first_name ?? '')
                                        . ($mi ? ' ' . $mi : '')
                                    );
                                    $dateRaw  = $r->date_original_appointment ?? $r->date_last_promotion;
                                    $dateAppt = $dateRaw
                                        ? \Carbon\Carbon::parse($dateRaw)->format('m/d/Y')
                                        : '';
                                @endphp
                                <tr>
                                    <td class="name-col">{{ $fullName }}</td>
                                    <td class="pos-col">{{ $r->position_title }}</td>
                                    <td></td>
                                    <td class="perm-col">{{ $isPermanent ? '✔' : '' }}</td>
                                    <td></td>
                                    <td class="date-col">{{ $dateAppt }}</td>
                                    <td></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="padding:40px;color:#9ca3af;text-align:center;font-style:italic;">
                                        No filled records found for this position.
                                    </td>
                                </tr>
                            @endforelse
                            {{-- Always show at least 5 rows --}}
                            @for($blank = $records->count(); $blank < 5; $blank++)
                                <tr class="empty-row">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Signature section --}}
            <div class="sig-row">
                <div class="sig-block">
                    <div class="sig-label">Prepared by:</div>
                    <div class="sig-line">LGU Human Resource Dev't. Officer/Personnel Officer</div>
                    <div class="sig-date">Date: ___________________________</div>
                </div>
                <div class="sig-block">
                    <div class="sig-label">Attested by:</div>
                    <div class="sig-line">Governor, Bukidnon Province</div>
                    <div class="sig-date">Date: ___________________________</div>
                </div>
            </div>

            {{-- Instructions box --}}
            <div class="instructions-box">
                <div class="instructions-title">Instructions in Filling-up the Form</div>
                <div class="instr-row">
                    <span class="instr-label">Column 1:</span>
                    <span class="instr-text">Indicate the name of personnel hired by the LGU</span>
                </div>
                <div class="instr-row">
                    <span class="instr-label">Column 02:</span>
                    <span class="instr-text">State the official designation as stipulated the contract of service (COS), or
                        the position stated in the plantilla of personnel, if permanent</span>
                </div>
                <div class="instr-row">
                    <span class="instr-label">Column 03:</span>
                    <span class="instr-text">Indicate the TOR if COS/duties and responsibilities if permanent</span>
                </div>
                <div class="instr-row">
                    <span class="instr-label">Column 04:</span>
                    <span class="instr-text">Put a check on the status of appointment whether COS in (4.1) or for Permanent
                        (4.2) and the date of appointment</span>
                </div>
                <div class="instr-row">
                    <span class="instr-label">Column 05:</span>
                    <span class="instr-text">Indicate other relevant information as maybe important which are not captured
                        in the columns provided in this form.</span>
                </div>
            </div>
        </div>

    @else
        {{-- Empty state when no position selected yet --}}
        <div class="report-card">
            <div class="empty-state">
                <i class="bi bi-file-text"></i>
                <p style="font-size:16px;font-weight:700;color:#374151;margin-bottom:6px;">Select a Position to Generate
                    Report</p>
                <p>Choose a position from the dropdown above and click <strong>Generate Report</strong><br>
                    to view the MFMP 001-2023 status report for that position.</p>
            </div>
        </div>
    @endif

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new TomSelect('#position-select', {
                plugins: ['remove_button'],
                placeholder: '— Choose a position —',
                maxOptions: 100,
                hideSelected: true
            });
        });
    </script>
</x-dashboard-app>