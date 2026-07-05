<x-dashboard-app>
<style>
/* ── Formal Report Page Styles ────────────────────────────────────────── */
.report-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #1a5276 55%, #0d6efd 100%);
    border-radius: 14px; padding: 26px 32px;
    position: relative; overflow: hidden; margin-bottom: 22px;
}
.report-hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 22px 22px;
}
.report-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.report-hero h1   { color: #fff; font-size: 24px; font-weight: 800; margin: 0; line-height: 1.2; }
.report-hero p    { color: rgba(255,255,255,.65); font-size: 13px; margin: 5px 0 0; }
.report-back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
    color: #fff; padding: 8px 16px; border-radius: 10px;
    font-size: 13px; font-weight: 600; text-decoration: none;
    transition: background .2s;
}
.report-back-btn:hover { background: rgba(255,255,255,.28); color: #fff; }

/* Accordion controls */
.office-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,.03);
}
.office-btn {
    width: 100%; border: none; background: none; cursor: pointer;
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; text-align: left; background: #f8fafc;
    transition: background .15s;
}
.office-btn:hover { background: #f1f5f9; }
.office-name { font-weight: 700; color: #1e293b; font-size: 14px; }
.office-count { font-size: 12px; color: #64748b; margin-left: 8px; font-weight: normal; }
.chevron { color: #94a3b8; font-size: 14px; transition: transform .2s; }

/* Excel Table Exact Mimic */
.excel-wrapper {
    overflow-x: auto;
    padding: 20px;
    background: #fff;
    border-top: 1px solid #e5e7eb;
}
.excel-title-block {
    text-align: center;
    margin-bottom: 20px;
    font-family: Arial, sans-serif;
}
.excel-title-1 { font-size: 16px; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; }
.excel-title-2 { font-size: 16px; font-weight: normal; text-transform: uppercase; margin-top: 4px; }

.excel-dept {
    font-family: Arial, sans-serif; font-size: 12px; margin-bottom: 4px;
}
.excel-dept strong { text-transform: uppercase; }

.excel-table {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    font-size: 11px;
    color: #000;
}
.excel-table th, .excel-table td {
    border: 1px solid #000;
    padding: 6px 4px;
    vertical-align: middle;
}
.excel-table th {
    text-align: center;
    font-weight: normal;
    background-color: #fff; /* White bg like excel */
}
.excel-table td { text-align: center; }
.excel-table td.text-left { text-align: left; padding-left: 6px; }
.excel-table td.text-right { text-align: right; padding-right: 6px; }

/* Thicker borders for outer outline and headers */
.excel-table { border: 2px solid #000; }
.excel-table thead { border-bottom: 2px solid #000; }
.excel-table tfoot { border-top: 2px solid #000; font-weight: bold; }
.col-num { font-size: 9px; color: #333; }

</style>

<div class="report-hero">
    <div class="report-hero-inner">
        <div>
            @php
                $titleSuffix = request('type') === 'casual' ? ' (Casual)' : (request('type') === 'permanent' ? ' (Permanent)' : '');
                $reportTitle = request('mode') === 'nosi' ? 'NOSI/NOLP Plantilla Basis' : 'Office Salary Plantilla Report';
            @endphp
            <h1><i class="bi bi-file-earmark-spreadsheet me-2"></i>{{ $reportTitle }}{{ $titleSuffix }}</h1>
            <p>{{ request('mode') === 'nosi' ? 'Shows prorated step increment budget increases for the proposed year.' : 'Formal output mimicking the Plantilla of Personnel documentation without step increases.' }}</p>
        </div>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <a href="{{ route('step-increment.office-report.export-excel', ['type' => request('type'), 'mode' => request('mode')]) }}" onclick="event.preventDefault(); handleReportExport(this.href, 'excel')" class="report-back-btn" style="background: #10b981; border-color: #059669;">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <a href="{{ route('step-increment.office-report.export-pdf', ['type' => request('type'), 'mode' => request('mode')]) }}" onclick="event.preventDefault(); handleReportExport(this.href, 'pdf')" class="report-back-btn" style="background: #ef4444; border-color: #dc2626;">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="{{ route('step-increment.index') }}" class="report-back-btn">
                <i class="bi bi-arrow-left"></i> Back to Step Increment
            </a>
        </div>
    </div>
</div>

<div style="margin-bottom: 22px;">
    <form method="GET" action="{{ route('step-increment.office-report') }}" style="display:flex; gap:10px; flex-wrap: wrap;">
        <input type="hidden" name="type" value="{{ request('type') }}">
        <input type="hidden" name="mode" value="{{ request('mode') }}">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Search name, item, position, office..." style="flex:1; min-width: 200px; padding: 10px 16px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; outline:none; transition: border .2s;">
        
        <select name="office" style="min-width: 180px; padding: 10px 16px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; outline:none; background: #fff;">
            <option value="">All Offices</option>
            @foreach($offices ?? [] as $officeName)
                <option value="{{ $officeName }}" {{ request('office') === $officeName ? 'selected' : '' }}>
                    {{ Str::limit($officeName, 35) }}
                </option>
            @endforeach
        </select>

        <select name="position" style="min-width: 180px; padding: 10px 16px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; outline:none; background: #fff;">
            <option value="">All Positions</option>
            @foreach($positions ?? [] as $pos)
                <option value="{{ $pos }}" {{ request('position') === $pos ? 'selected' : '' }}>
                    {{ Str::limit($pos, 35) }}
                </option>
            @endforeach
        </select>

        <button type="submit" style="background: #1e3a5f; color: #fff; border: none; padding: 0 20px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: background .2s;">Search</button>
        @if(request('search') || request('office') || request('position'))
        <a href="{{ route('step-increment.office-report', ['type' => request('type'), 'mode' => request('mode')]) }}" style="background: #f1f5f9; color: #475569; border: 1px solid #e5e7eb; padding: 0 20px; border-radius: 10px; font-weight: 600; text-decoration: none; display: flex; align-items: center;">Clear</a>
        @endif
    </form>
</div>

@forelse($grouped as $office => $records)
@php
    $oid = 'off-' . md5($office);
    $permanentRecords = collect($records)->filter(fn($r) => !in_array(strtolower($r->employment_status ?? ''), ['casual', 'c']));
    $casualRecords = collect($records)->filter(fn($r) => in_array(strtolower($r->employment_status ?? ''), ['casual', 'c']));
@endphp
<div class="office-card">
    <div class="office-btn" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <button type="button" onclick="toggleOffice('{{ $oid }}')" style="flex:1; display:flex; align-items:center; gap:8px; background:none; border:none; cursor:pointer; text-align:left; padding:0;">
            <i class="bi bi-building me-2" style="color:#0d6efd;"></i>
            <span class="office-name">{{ strtoupper($office) }}</span>
            <span class="office-count">({{ count($records) }} items)</span>
        </button>
        <div style="display:flex; gap:8px; align-items:center; flex-shrink:0;">
            <a href="{{ route('step-increment.office-report.export-excel-by-office', ['office' => $office, 'type' => request('type'), 'mode' => request('mode')]) }}"
               onclick="event.preventDefault(); handleReportExport(this.href, 'excel')"
               style="display:inline-flex; align-items:center; gap:5px; background:#10b981; color:#fff; padding:5px 12px; border-radius:7px; font-size:12px; font-weight:700; text-decoration:none;">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            <a href="{{ route('step-increment.office-report.export-pdf-by-office', ['office' => $office, 'type' => request('type'), 'mode' => request('mode')]) }}"
               onclick="event.preventDefault(); handleReportExport(this.href, 'pdf')"
               style="display:inline-flex; align-items:center; gap:5px; background:#ef4444; color:#fff; padding:5px 12px; border-radius:7px; font-size:12px; font-weight:700; text-decoration:none;">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
            <i class="bi bi-chevron-down chevron" id="{{ $oid }}-chev" onclick="toggleOffice('{{ $oid }}')" style="transform: rotate(-90deg); cursor:pointer;"></i>
        </div>
    </div>

    <div id="{{ $oid }}" style="display: none;">
        @foreach(['Permanent' => $permanentRecords, 'Casual' => $casualRecords] as $type => $typeRecords)
        @if($typeRecords->count() > 0)
        @php
            $totalCurrent = 0;
            $totalProposed = 0;
            $totalIncrease = 0;
        @endphp
        <div class="excel-wrapper" style="{{ $loop->iteration > 1 ? 'border-top: 4px solid #e5e7eb;' : '' }}">
            <div class="excel-title-block">
                @if(request('mode') === 'nosi')
                    <div class="excel-title-1">NOSI/NOLP PLANTILLA BASIS CY {{ now()->year + 1 }}</div>
                @else
                    <div class="excel-title-1">PLANTILLA OF PERSONNEL CY {{ now()->year + 1 }}</div>
                @endif
                <div class="excel-title-2">PROVINCE OF BUKIDNON - {{ strtoupper($type) }}</div>
            </div>

            <div class="excel-dept">
                Department/Office : <strong>{{ $office }}</strong>
            </div>

            <table class="excel-table">
                <thead>
                    <tr>
                        <th colspan="2">Item Number</th>
                        <th rowspan="2" style="min-width: 180px;">Position Title</th>
                        <th rowspan="2" style="min-width: 150px;">Name of<br>Incumbent</th>
                        <th colspan="2">Current Year Authorized<br>Rate/Annum {{ now()->year }}</th>
                        <th colspan="2">Budget Year Proposed<br>Rate/Annum {{ now()->year + 1 }}</th>
                        <th rowspan="2">Increase/<br>Decrease</th>
                    </tr>
                    <tr>
                        <th style="min-width: 40px; font-size: 10px;">Old</th>
                        <th style="min-width: 40px; font-size: 10px;">New</th>
                        <th>SG/<br>Step</th>
                        <th style="min-width: 90px;">Amount</th>
                        <th>SG/<br>Step</th>
                        <th style="min-width: 90px;">Step Increment<br>Amount</th>
                    </tr>
                    <tr>
                        <th><span class="col-num">(1)</span></th>
                        <th><span class="col-num">(2)</span></th>
                        <th><span class="col-num">(3)</span></th>
                        <th><span class="col-num">(4)</span></th>
                        <th><span class="col-num">(5)</span></th>
                        <th><span class="col-num">(6)</span></th>
                        <th><span class="col-num">(7)</span></th>
                        <th><span class="col-num">(8)</span></th>
                        <th><span class="col-num">(9)</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($typeRecords as $index => $rec)
                    @php
                        $extractedNum = preg_replace('/[^0-9]/', '', $rec->item_no_new ?? '');
                        $itemNum = $extractedNum !== '' ? $extractedNum : $loop->iteration;

                        // Current rates
                        $curSg = $rec->salary_grade;
                        $curStep = $rec->step ?: 1;
                        // Use authorized annual salary or actual, since the report focuses on actual rates normally, but let's calculate based on actual.
                        // For vacant positions, actual salary might be 0, so we should fetch from SalaryGrade matrix.
                        if ($rec->is_vacant) {
                            $curMonthly = \App\Models\SalaryGrade::getRate($curSg, 1);
                            $curStep = 1;
                        } else {
                            $curMonthly = \App\Models\SalaryGrade::getRate($curSg, $curStep);
                        }
                        $curAnnual = $curMonthly * 12;

                        // Proposed rates & Prorated increase
                        $propStep = $curStep;
                        $propAnnual = $curAnnual;
                        $increase = 0;
                        $increaseNote = '';
                        
                        $due = $rec->next_step_due_date;
                        $budgetYear = now()->year + 1;
                        $mode = request('mode', 'annual');
                        
                        if ($mode === 'nosi' && !$rec->is_vacant && $due && $curStep < 8) {
                            $dueType = $rec->due_type;
                            $stepIncreaseNum = ($dueType === 'nolp' || $dueType === 'both') ? 2 : 1;
                            $newStep = min(8, $curStep + $stepIncreaseNum);
                            
                            if ($due->year < $budgetYear) {
                                // Full increase
                                $propStep = $newStep;
                                $propAnnual = \App\Models\SalaryGrade::getRate($curSg, $propStep) * 12;
                                $increase = $propAnnual - $curAnnual;
                            } elseif ($due->year == $budgetYear) {
                                // Prorated increase
                                $propStep = $newStep;
                                $propAnnual = \App\Models\SalaryGrade::getRate($curSg, $propStep) * 12;
                                
                                $monthlyDiff = ($propAnnual - $curAnnual) / 12;
                                $activeMonths = 12 - $due->month + 1;
                                $increase = $monthlyDiff * $activeMonths;
                                $increaseNote = "<br><span style='font-size:9px;color:#64748b;'>(" . $due->format('M') . " - Dec)</span>";
                            }
                        }

                        $totalCurrent += $curAnnual;
                        $totalProposed += $propAnnual;
                        $totalIncrease += $increase;

                        // Format incumbent name
                        $incumbent = 'Vacant';
                        if (!$rec->is_vacant) {
                            $last = ucfirst(strtolower($rec->last_name));
                            $first = ucfirst(strtolower($rec->first_name));
                            $mi = $rec->middle_name ? strtoupper(substr($rec->middle_name, 0, 1)) . '.' : '';
                            $incumbent = trim("$first $mi $last");
                        } else {
                            $incumbent = '<span style="display:inline-block;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;font-style:normal;letter-spacing:0.5px;">VACANT</span>';
                        }
                    @endphp
                    @php
                        $rowStyle = $rec->is_vacant ? 'background-color: #f8fafc; font-style: italic; color: #64748b;' : '';
                    @endphp
                    <tr style="{{ $rowStyle }}">
                        <td>{{ $itemNum }}</td>
                        <td>{{ $itemNum }}</td>
                        <td class="text-left" style="line-height: 1.2;">{{ $rec->position_title }}</td>
                        <td class="text-left">{!! $incumbent !!}</td>
                        <td>{{ $curSg }}/{{ $curStep }}</td>
                        <td class="text-right">{{ $curAnnual > 0 ? number_format($curAnnual, 2) : '-' }}</td>
                        <td>{{ $curSg }}/{{ $propStep }}</td>
                        <td class="text-right">{{ $propAnnual > 0 ? number_format($propAnnual, 2) : '-' }}</td>
                        <td class="text-right">{!! $increase > 0 ? number_format($increase, 2) . $increaseNote : '-' !!}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-center">TOTAL ({{ strtoupper($type) }})</td>
                        <td></td>
                        <td class="text-right">{{ number_format($totalCurrent, 2) }}</td>
                        <td></td>
                        <td class="text-right">{{ number_format($totalProposed, 2) }}</td>
                        <td class="text-right">{{ $totalIncrease > 0 ? number_format($totalIncrease, 2) : '-' }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
        @endforeach
    </div>
</div>
@empty
<div class="report-hero" style="background: #fff; border: 1px solid #e5e7eb; color: #333 text-align: center;">
    <div style="padding: 40px; text-align: center;">
        <i class="bi bi-inbox" style="font-size: 40px; color: #cbd5e1;"></i>
        <h3 style="margin-top: 10px; font-size: 16px; color: #475569;">No Records Found</h3>
    </div>
</div>
@endforelse

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleOffice(id) {
    var body = document.getElementById(id);
    var chev = document.getElementById(id + '-chev');
    if (!body) return;
    var isOpen = body.style.display !== 'none';
    body.style.display   = isOpen ? 'none' : 'block';
    chev.style.transform = isOpen ? 'rotate(-90deg)' : 'rotate(0deg)';
}
// Automatically open the first one for UX
document.addEventListener('DOMContentLoaded', function() {
    var firstOfficeBtn = document.querySelector('.office-btn');
    if (firstOfficeBtn) {
        firstOfficeBtn.click();
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
                    // Excel can't be easily previewed in an iframe, so just open it
                    window.open(downloadUrl, '_blank');
                }
            } else {
                // Revoke URL if they just close
                window.URL.revokeObjectURL(downloadUrl);
            }
        });
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Export Failed', text: 'There was an issue generating your file. ' + error.message });
    }
}
</script>
</x-dashboard-app>
