<x-dashboard-app>
<style>
.nosa-rpt-hero {
    background: linear-gradient(135deg, #881337 0%, #be123c 55%, #e11d48 100%);
    border-radius: 14px; padding: 26px 32px;
    position: relative; overflow: hidden; margin-bottom: 22px;
}
.nosa-rpt-hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 22px 22px;
}
.nosa-rpt-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.nosa-rpt-hero h1   { color: #fff; font-size: 24px; font-weight: 800; margin: 0; line-height: 1.2; }
.nosa-rpt-hero p    { color: rgba(255,255,255,.65); font-size: 13px; margin: 5px 0 0; }
.nosa-back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
    color: #fff; padding: 8px 16px; border-radius: 10px;
    font-size: 13px; font-weight: 600; text-decoration: none; transition: background .2s;
}
.nosa-back-btn:hover { background: rgba(255,255,255,.28); color: #fff; }

/* Schedule info banner */
.sched-banner {
    background: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px;
    padding: 16px 22px; margin-bottom: 18px;
    display: flex; gap: 18px; flex-wrap: wrap; align-items: center;
}
.sched-chip {
    background: #fff; border: 1px solid #fecdd3; border-radius: 8px; padding: 8px 14px;
}
.sched-chip-label { font-size: 10px; font-weight: 700; color: #be123c; text-transform: uppercase; letter-spacing: .5px; }
.sched-chip-val   { font-size: 14px; font-weight: 800; color: #881337; }

/* Office accordion */
.office-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;
    overflow: hidden; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.03);
}
.office-btn {
    width: 100%; border: none; background: none; cursor: pointer;
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; text-align: left; background: #f8fafc; transition: background .15s;
}
.office-btn:hover { background: #f1f5f9; }
.office-name  { font-weight: 700; color: #1e293b; font-size: 14px; }
.office-count { font-size: 12px; color: #64748b; margin-left: 8px; font-weight: normal; }
.chevron { color: #94a3b8; font-size: 14px; transition: transform .2s; }

/* NOSA Table */
.excel-wrapper { overflow-x: auto; padding: 20px; background: #fff; border-top: 1px solid #e5e7eb; }
.excel-title-block { text-align: center; margin-bottom: 14px; font-family: Arial, sans-serif; }
.excel-title-1 { font-size: 15px; font-weight: bold; letter-spacing: .5px; text-transform: uppercase; }
.excel-title-2 { font-size: 13px; font-weight: normal; text-transform: uppercase; margin-top: 3px; }
.excel-title-3 { font-size: 11px; color: #555; margin-top: 4px; }
.excel-dept    { font-family: Arial, sans-serif; font-size: 12px; margin-bottom: 6px; }
.excel-dept strong { text-transform: uppercase; }

.excel-table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; color: #000; }
.excel-table th, .excel-table td { border: 1px solid #000; padding: 5px 4px; vertical-align: middle; }
.excel-table th { text-align: center; font-weight: normal; background: #fff; }
.excel-table td { text-align: center; }
.excel-table td.tl  { text-align: left; padding-left: 6px; }
.excel-table td.tr  { text-align: right; padding-right: 6px; }
.excel-table { border: 2px solid #000; }
.excel-table thead { border-bottom: 2px solid #000; }
.excel-table tfoot { border-top: 2px solid #000; font-weight: bold; }
.col-num { font-size: 9px; color: #333; }
</style>

{{-- HERO --}}
<div class="nosa-rpt-hero">
    <div class="nosa-rpt-hero-inner">
        <div>
            @php $suffix = request('type') === 'casual' ? ' (Casual)' : ' (Permanent)'; @endphp
            <h1><i class="bi bi-file-earmark-medical-fill me-2"></i>NOSA — SSL Tranche Adjustment{{ $suffix }}</h1>
            <p>Notice of Salary Adjustment for affected employees based on the active salary schedule</p>
        </div>
        <a href="{{ route('step-increment.hub') }}" class="nosa-back-btn">
            <i class="bi bi-arrow-left"></i> Back to Hub
        </a>
    </div>
</div>

{{-- No active schedule warning --}}
@if(!$activeSchedule)
<div style="background:#fff1f2;border:2px dashed #fca5a5;border-radius:12px;padding:28px;margin-bottom:18px;text-align:center;">
    <i class="bi bi-exclamation-circle-fill" style="font-size:36px;color:#be123c;"></i>
    <h3 style="margin:10px 0 6px;color:#881337;font-size:17px;">No Active SSL Schedule Found</h3>
    <p style="color:#be123c;font-size:13px;margin:0 0 14px;">Please activate a Salary Schedule before generating NOSA reports.</p>
    <a href="{{ route('salary-schedules.index') }}" style="display:inline-flex;align-items:center;gap:6px;background:#be123c;color:#fff;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;">
        <i class="bi bi-gear-fill"></i> Manage Salary Schedules
    </a>
</div>
@else

{{-- Schedule Info Banner --}}
<div class="sched-banner">
    <div>
        <div class="sched-chip-label">Active SSL Schedule (New Rates)</div>
        <div style="font-size:17px;font-weight:900;color:#881337;">{{ $activeSchedule->name }}</div>
    </div>
    @if($activeSchedule->lbc_number)
    <div class="sched-chip">
        <div class="sched-chip-label">LBC #</div>
        <div class="sched-chip-val">{{ $activeSchedule->lbc_number }}</div>
    </div>
    @endif
    @if($activeSchedule->effective_date)
    <div class="sched-chip">
        <div class="sched-chip-label">Effective Date</div>
        <div class="sched-chip-val">{{ $activeSchedule->effective_date->format('F j, Y') }}</div>
    </div>
    @endif
    @if($previousSchedule)
    <div class="sched-chip" style="background:#f8fafc;border-color:#e5e7eb;">
        <div class="sched-chip-label" style="color:#64748b;">Previous Schedule (Baseline)</div>
        <div style="font-size:13px;font-weight:700;color:#475569;">{{ $previousSchedule->name }}</div>
    </div>
    @else
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 14px;font-size:12px;color:#92400e;">
        <i class="bi bi-exclamation-triangle-fill"></i> No previous schedule — using employee's recorded salary as baseline.
    </div>
    @endif
</div>

{{-- Filter Bar --}}
<div style="margin-bottom:22px;">
    <form method="GET" action="{{ route('step-increment.nosa') }}" style="display:flex;gap:10px;flex-wrap:wrap;">
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="🔍 Search name, item, position, office..."
               style="flex:1;min-width:200px;padding:10px 16px;border:1px solid #e5e7eb;border-radius:10px;font-size:13px;outline:none;">

        <select name="office" style="min-width:180px;padding:10px 16px;border:1px solid #e5e7eb;border-radius:10px;font-size:13px;outline:none;background:#fff;">
            <option value="">All Offices</option>
            @foreach($offices ?? [] as $officeName)
                <option value="{{ $officeName }}" {{ request('office') === $officeName ? 'selected' : '' }}>
                    {{ Str::limit($officeName, 35) }}
                </option>
            @endforeach
        </select>

        <select name="position" style="min-width:180px;padding:10px 16px;border:1px solid #e5e7eb;border-radius:10px;font-size:13px;outline:none;background:#fff;">
            <option value="">All Positions</option>
            @foreach($positions ?? [] as $pos)
                <option value="{{ $pos }}" {{ request('position') === $pos ? 'selected' : '' }}>
                    {{ Str::limit($pos, 35) }}
                </option>
            @endforeach
        </select>

        <button type="submit" style="background:#881337;color:#fff;border:none;padding:0 20px;border-radius:10px;font-weight:600;cursor:pointer;">
            Search
        </button>
        @if(request('search') || request('office') || request('position'))
        <a href="{{ route('step-increment.nosa', ['type' => $type]) }}"
           style="background:#f1f5f9;color:#475569;border:1px solid #e5e7eb;padding:0 20px;border-radius:10px;font-weight:600;text-decoration:none;display:flex;align-items:center;">
            Clear
        </a>
        @endif
    </form>
</div>

{{-- Office Accordion --}}
@forelse($grouped as $office => $records)
@php $oid = 'nosa-' . md5($office); @endphp
<div class="office-card">
    <div class="office-btn" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <button type="button" onclick="toggleNosaOffice('{{ $oid }}')"
                style="flex:1;display:flex;align-items:center;gap:8px;background:none;border:none;cursor:pointer;text-align:left;padding:0;">
            <i class="bi bi-building me-2" style="color:#be123c;"></i>
            <span class="office-name">{{ strtoupper($office) }}</span>
            <span class="office-count">({{ count($records) }} employees)</span>
        </button>
        <div style="display:flex;gap:8px;align-items:center;flex-shrink:0;">
            <a href="{{ route('step-increment.nosa.export-pdf-by-office', ['office' => $office, 'type' => $type]) }}"
               onclick="event.preventDefault(); handleReportExport(this.href, 'pdf')"
               style="display:inline-flex;align-items:center;gap:5px;background:#ef4444;color:#fff;padding:5px 12px;border-radius:7px;font-size:12px;font-weight:700;text-decoration:none;">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
            <i class="bi bi-chevron-down chevron" id="{{ $oid }}-chev"
               onclick="toggleNosaOffice('{{ $oid }}')"
               style="transform:rotate(-90deg);cursor:pointer;"></i>
        </div>
    </div>

    <div id="{{ $oid }}" style="display:none;">
        @php
            $totalPrev     = 0;
            $totalNew      = 0;
            $totalIncrease = 0;
        @endphp
        <div class="excel-wrapper">
            {{-- Title block --}}
            <div class="excel-title-block">
                <div class="excel-title-1">NOTICE OF SALARY ADJUSTMENT</div>
                <div class="excel-title-2">PROVINCE OF BUKIDNON — {{ strtoupper($type) }}</div>
                <div class="excel-title-3">
                    Pursuant to: <strong>{{ $activeSchedule->name }}</strong>
                    @if($activeSchedule->lbc_number)
                        &nbsp;|&nbsp; LBC # <strong>{{ $activeSchedule->lbc_number }}</strong>
                    @endif
                    @if($activeSchedule->effective_date)
                        &nbsp;|&nbsp; Effective: <strong>{{ $activeSchedule->effective_date->format('F j, Y') }}</strong>
                    @endif
                </div>
            </div>

            <div class="excel-dept">Department/Office : <strong>{{ $office }}</strong></div>

            <table class="excel-table">
                <thead>
                    <tr>
                        <th colspan="2" rowspan="3" style="min-width:70px; vertical-align:bottom; padding-bottom:4px;">
                            Item Number<br><br><br>
                        </th>
                        <th rowspan="3" style="min-width:180px;">Position Title</th>
                        <th rowspan="3" style="min-width:150px;">Name of<br>Incumbent</th>
                        <th colspan="2">Current Year Authorized<br>Rate/Annum {{ $previousSchedule && $previousSchedule->effective_date ? $previousSchedule->effective_date->format('Y') : now()->year }}</th>
                        <th colspan="2">Budget Year Proposed<br>Rate/Annum {{ $activeSchedule && $activeSchedule->effective_date ? $activeSchedule->effective_date->format('Y') : now()->year }}</th>
                        <th rowspan="3" style="min-width:90px;">Increase/<br>Decrease</th>
                        <th rowspan="3" style="min-width:70px;">Action</th>
                    </tr>
                    <tr>
                        <th style="min-width:55px;">SG/<br>Step</th>
                        <th style="min-width:110px;">
                            @if($previousSchedule)
                                @if($previousSchedule->lbc_number)<strong>{{ $previousSchedule->lbc_number }}</strong><br>@endif
                                {{ $previousSchedule->name }}<br>
                            @endif
                            Amount
                        </th>
                        <th style="min-width:55px;">SG/<br>Step</th>
                        <th style="min-width:110px;">
                            @if($activeSchedule->lbc_number)<strong>{{ $activeSchedule->lbc_number }}</strong><br>@endif
                            {{ $activeSchedule->name }}<br>
                            Amount
                        </th>
                    </tr>
                    <tr>
                        <th style="font-size:10px;">Old</th>
                        <th style="font-size:10px;">Old<br><span class="col-num">(1)</span></th>
                        <th style="font-size:10px;">New</th>
                        <th style="font-size:10px;">New<br><span class="col-num">(2)</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($records as $rec)
                @php
                    $sg   = $rec->salary_grade;
                    $step = $rec->step ?: 1;

                    $extractedNum = preg_replace('/[^0-9]/', '', $rec->item ?? '');
                    $itemNum = $extractedNum !== '' ? $extractedNum : $loop->iteration;

                    if ($rec->is_vacant) {
                        $prevAnnual = 0;
                        $newAnnual  = 0;
                    } else {
                        // Previous salary: from the previous (inactive) schedule
                        if ($previousSchedule) {
                            $prevMonthly = \App\Models\SalaryGrade::getRateForSchedule($previousSchedule->id, $sg, $step);
                            $prevAnnual  = $prevMonthly * 12;
                        } else {
                            // Fallback: use recorded actual annual salary
                            $prevAnnual = (float) ($rec->actual_annual_salary ?: 0);
                        }
                        // New salary: from the active (current) schedule
                        $newMonthly = \App\Models\SalaryGrade::getRateForSchedule($activeSchedule->id, $sg, $step);
                        $newAnnual  = $newMonthly * 12;
                    }

                    $increase = $newAnnual - $prevAnnual;
                    $totalPrev     += $prevAnnual;
                    $totalNew      += $newAnnual;
                    $totalIncrease += $increase;

                    // Incumbent name
                    if ($rec->is_vacant) {
                        $incumbent = '<span style="display:inline-block;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;letter-spacing:.5px;">VACANT</span>';
                    } else {
                        $last = ucfirst(strtolower($rec->last_name));
                        $first = ucfirst(strtolower($rec->first_name));
                        $mi = $rec->middle_name ? strtoupper(substr($rec->middle_name, 0, 1)) . '.' : '';
                        $incumbent = trim("$first $mi $last");
                    }

                    $rowStyle = $rec->is_vacant ? 'background:#f8fafc;font-style:italic;color:#64748b;' : '';
                @endphp
                <tr style="{{ $rowStyle }}">
                    <td>{{ $itemNum }}</td>
                    <td>{{ $itemNum }}</td>
                    <td class="tl" style="line-height:1.2;">{{ $rec->position_title }}</td>
                    <td class="tl">{!! $incumbent !!}</td>
                    <td>{{ $sg }}/{{ $step }}</td>
                    <td class="tr">{{ $prevAnnual > 0 ? number_format($prevAnnual, 2) : '-' }}</td>
                    <td>{{ $sg }}/{{ $step }}</td>
                    <td class="tr">{{ $newAnnual > 0 ? number_format($newAnnual, 2) : '-' }}</td>
                    <td class="tr" style="{{ $increase > 0 ? 'color:#16a34a;font-weight:700;' : ($increase < 0 ? 'color:#dc2626;font-weight:700;' : '') }}">
                        {{ $increase != 0 ? number_format($increase, 2) : '-' }}
                    </td>
                    <td>
                        @if(!$rec->is_vacant)
                        <a href="{{ route('step-increment.pdf.nosa', $rec->id) }}" target="_blank"
                           style="display:inline-flex;align-items:center;gap:4px;background:#be123c;color:#fff;padding:3px 9px;border-radius:5px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;">
                            <i class="bi bi-printer-fill"></i> NOSA
                        </a>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:center;font-weight:bold;">TOTAL</td>
                        <td></td>
                        <td class="tr">{{ number_format($totalPrev, 2) }}</td>
                        <td></td>
                        <td class="tr">{{ number_format($totalNew, 2) }}</td>
                        <td class="tr" style="{{ $totalIncrease > 0 ? 'color:#16a34a;' : ($totalIncrease < 0 ? 'color:#dc2626;' : '') }}">
                            {{ $totalIncrease != 0 ? number_format($totalIncrease, 2) : '-' }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>{{-- /excel-wrapper --}}
    </div>{{-- /accordion body --}}
</div>{{-- /office-card --}}

@empty
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;text-align:center;padding:48px;">
    <i class="bi bi-inbox" style="font-size:40px;color:#cbd5e1;"></i>
    <h3 style="margin-top:10px;font-size:16px;color:#475569;">No Records Found</h3>
</div>
@endforelse

@endif {{-- end activeSchedule --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleNosaOffice(id) {
    var body = document.getElementById(id);
    var chev = document.getElementById(id + '-chev');
    if (!body) return;
    var isOpen = body.style.display !== 'none';
    body.style.display   = isOpen ? 'none' : 'block';
    chev.style.transform = isOpen ? 'rotate(-90deg)' : 'rotate(0deg)';
}
document.addEventListener('DOMContentLoaded', function () {
    var first = document.querySelector('[id^="nosa-"].office-card [id^="nosa-"]');
    if (!first) {
        // open first accordion
        var btns = document.querySelectorAll('.office-btn button');
        if (btns.length) btns[0].click();
    }
});

async function handleReportExport(url, type) {
    if (typeof Swal === 'undefined') {
        window.location.href = url;
        return;
    }
    
    Swal.fire({
        title: 'Generating ' + (type === 'pdf' ? 'PDF' : 'Excel') + '...',
        html: 'This may take a moment depending on the number of records. Please do not close the window.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    
    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: { 'Accept': type === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }
        });

        if (!response.ok) throw new Error('Server returned an error while generating the file.');

        let filename = type === 'pdf' ? 'report.pdf' : 'report.xlsx';
        const disposition = response.headers.get('Content-Disposition');
        if (disposition && disposition.indexOf('filename=') !== -1) {
            filename = disposition.split('filename=')[1].replace(/["']/g, '');
        }

        const blob = await response.blob();
        const downloadUrl = window.URL.createObjectURL(blob);
        
        // Auto download the file first
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = downloadUrl;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        // Show success modal with View and Open options
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
                // Open in new tab
                window.open(downloadUrl, '_blank');
            } else if (result.isDenied) {
                // View File - Preview inside a large SweetAlert modal
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
</x-dashboard-app>
