<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Applicant Demographics &amp; Qualifications</title>
<style>
@page { margin: 28px 36px 36px; }

* { box-sizing: border-box; }

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    line-height: 1.35;
    color: #111;
}

/* ── Letterhead ── */
.lh-wrap  { text-align: center; margin-bottom: 4px; }
.lh-logos img { height: 60px; margin: 0 8px; vertical-align: middle; }
.lh-text  { font-size: 10px; }
.lh-text b { font-size: 11px; }

.report-title {
    background: #1f497d;
    color: #fff;
    text-align: center;
    padding: 5px 8px;
    font-size: 12px;
    font-weight: bold;
    letter-spacing: .4px;
    margin-bottom: 2px;
}
.report-subtitle {
    background: #dce6f1;
    border: 1px solid #7f9db9;
    text-align: center;
    padding: 3px 6px;
    font-size: 9px;
    margin-bottom: 6px;
}

/* ── Section labels ── */
.office-label {
    background: #1f497d;
    color: #fff;
    font-weight: bold;
    font-size: 10px;
    padding: 4px 8px;
    margin-top: 10px;
    margin-bottom: 0;
    page-break-inside: avoid;
}
.position-label {
    background: #dce6f1;
    border: 1px solid #7f9db9;
    font-weight: bold;
    font-size: 9.5px;
    padding: 3px 8px;
    margin-bottom: 4px;
    page-break-inside: avoid;
}

/* ── Applicant card ── */
.card {
    border: 1px solid #c0c0c0;
    margin-bottom: 8px;
    page-break-inside: avoid;
}
.card-header {
    background: #f2f7ff;
    border-bottom: 1px solid #c0c0c0;
    padding: 6px 8px;
    display: table;
    width: 100%;
}
.card-header-left  { display: table-cell; vertical-align: middle; }
.card-header-right { display: table-cell; width: 110px; text-align: right; vertical-align: middle; }
.applicant-name { font-size: 11px; font-weight: bold; color: #1f497d; }
.applicant-meta { font-size: 9px; color: #555; }
.ain-badge {
    display: inline-block;
    background: #1f497d;
    color: #fff;
    border-radius: 3px;
    padding: 1px 6px;
    font-size: 9px;
    font-weight: bold;
}

.card-body { padding: 5px 8px 6px; }

/* ── Two-column grid inside card ── */
.grid2 { display: table; width: 100%; border-collapse: collapse; }
.col50  { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
.col50:last-child { padding-right: 0; }

/* ── Section headings inside card ── */
.sec-title {
    font-size: 8.5px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #1f497d;
    border-bottom: 1px solid #dce6f1;
    padding-bottom: 2px;
    margin: 6px 0 3px;
}
.field-row { display: table; width: 100%; margin-bottom: 2px; }
.fl { display: table-cell; width: 38%; font-weight: bold; color: #444; padding-right: 4px; }
.fv { display: table-cell; width: 62%; color: #111; }

/* ── Qualifications mini-table ── */
.q-table { width: 100%; border-collapse: collapse; margin-top: 3px; }
.q-table th {
    background: #dce6f1;
    border: 1px solid #b8cce4;
    padding: 2px 4px;
    font-size: 8.5px;
    text-align: left;
}
.q-table td {
    border: 1px solid #d0d0d0;
    padding: 2px 4px;
    font-size: 9px;
    vertical-align: top;
}
.q-table tr:nth-child(even) td { background: #f9fbff; }

/* ── Divider ── */
.divider { border: none; border-top: 1px dashed #bbb; margin: 6px 0; }

/* ── No-data ── */
.no-data { text-align: center; color: #888; font-style: italic; padding: 20px; }

/* ── Year counter chips ── */
.year-chip {
    display: inline-block;
    border: 1px solid #b8cce4;
    border-radius: 3px;
    padding: 1px 5px;
    font-size: 8.5px;
    margin: 1px 2px 1px 0;
    background: #f0f4ff;
}
.year-label { font-size: 7.5px; color: #555; display: block; margin-bottom: 1px; }

/* ── Badge ── */
.badge-yes { background:#d4edda; color:#155724; border-radius:3px; padding:1px 5px; font-size:8.5px; }
.badge-no  { background:#f8f9fa; color:#888; border-radius:3px; padding:1px 5px; font-size:8.5px; }

/* ── Footer ── */
.sig-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.sig-table td { border: 1px solid #a5a5a5; padding: 5px; }

.pg-number:before { content: "Page " counter(page); }
</style>
</head>
<body>

{{-- ── LETTERHEAD ──────────────────────────────────────────────── --}}
<div class="lh-wrap">
    <div class="lh-logos">
        @if(file_exists(public_path('img/logo.png')))
            <img src="{{ public_path('img/logo.png') }}" alt="Seal">
        @endif
        @if(file_exists(public_path('img/phrmologo.jpg')))
            <img src="{{ public_path('img/phrmologo.jpg') }}" alt="PHRMO">
        @elseif(file_exists(public_path('img/phrmologo.png')))
            <img src="{{ public_path('img/phrmologo.png') }}" alt="PHRMO">
        @endif
        @if(file_exists(public_path('img/bagong-pilipinas.png')))
            <img src="{{ public_path('img/bagong-pilipinas.png') }}" alt="Bagong Pilipinas">
        @endif
    </div>
    <div class="lh-text">
        Republic of the Philippines<br>
        <b>PROVINCE OF BUKIDNON</b><br>
        Provincial Capitol
    </div>
</div>

<div class="report-title">
    PROVINCIAL HUMAN RESOURCE MANAGEMENT OFFICE<br>
    APPLICANT DEMOGRAPHICS &amp; QUALIFICATIONS REPORT
</div>
<div class="report-subtitle">
    @if(!empty($filters['start_date']) && !empty($filters['end_date']))
        Period: {{ \Carbon\Carbon::parse($filters['start_date'])->format('F j, Y') }}
        – {{ \Carbon\Carbon::parse($filters['end_date'])->format('F j, Y') }}
        &nbsp;|&nbsp;
    @endif
    @if(!empty($filters['office'])) Office: {{ $filters['office'] }} &nbsp;|&nbsp; @endif
    @if(!empty($filters['item_no'])) Item No.: {{ $filters['item_no'] }} &nbsp;|&nbsp; @endif
    @if(!empty($filters['position_applied'])) Position: {{ $filters['position_applied'] }} &nbsp;|&nbsp; @endif
    Generated: {{ now()->format('F j, Y h:i A') }}
</div>

{{-- ── APPLICANT CARDS ─────────────────────────────────────────── --}}
@forelse($groupedApplicants as $office => $positionGroups)

    <div class="office-label">
        <i>OFFICE / UNIT:</i> {{ strtoupper($office) }}
    </div>

    @foreach($positionGroups as $posKey => $applicants)
        @php
            [$itemNo, $posTitle] = explode('|', $posKey, 2);
        @endphp

        <div class="position-label">
            Position: {{ $posTitle }}
            @if($itemNo) &nbsp;·&nbsp; Item No.: {{ $itemNo }} @endif
            &nbsp;·&nbsp; {{ $applicants->count() }} Applicant(s)
        </div>

        @foreach($applicants as $a)
        @php $photoUrl = $a->photo_embed_url; @endphp
        <div class="card">

            {{-- Card Header --}}
            <div class="card-header">
                {{-- Photo column --}}
                <div style="display:table-cell; width:62px; vertical-align:middle; padding-right:8px;">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}"
                             style="width:54px; height:64px; object-fit:cover;
                                    border:1px solid #c0c0c0; border-radius:3px;"
                             alt="Photo">
                    @else
                        <div style="width:54px; height:64px; background:#dce6f1;
                                    border:1px solid #b8cce4; border-radius:3px;
                                    display:flex; align-items:center; justify-content:center;
                                    font-size:20px; font-weight:bold; color:#1f497d; text-align:center; line-height:64px;">
                            {{ strtoupper(substr($a->first_name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                {{-- Name / meta --}}
                <div class="card-header-left">
                    <div class="applicant-name">{{ $a->full_name }}</div>
                    <div class="applicant-meta">
                        Ref: {{ $a->reference_no }}
                        @if($a->applied_at) &nbsp;·&nbsp; Applied: {{ $a->applied_at->format('M d, Y') }} @endif
                    </div>
                    @if(!$photoUrl)
                    <div style="font-size:8px; color:#c0392b; margin-top:3px; font-style:italic;">
                        ⚠ No photo attached
                    </div>
                    @endif
                </div>
                <div class="card-header-right">
                    <span class="ain-badge">AIN: {{ $a->ain }}</span>
                </div>
            </div>

            <div class="card-body">
                <div class="grid2">

                    {{-- LEFT: DEMOGRAPHICS ────────────────────────────── --}}
                    <div class="col50">

                        <div class="sec-title">Personal Information</div>
                        <div class="field-row"><span class="fl">Sex:</span><span class="fv">{{ $a->sex ?: '—' }}</span></div>
                        <div class="field-row"><span class="fl">Date of Birth:</span><span class="fv">{{ $a->date_of_birth?->format('M d, Y') ?? '—' }}</span></div>
                        <div class="field-row"><span class="fl">Religion:</span><span class="fv">{{ $a->religion ?: '—' }}</span></div>
                        <div class="field-row"><span class="fl">IP Affiliation:</span><span class="fv">{{ $a->indigenous_people ?: '—' }}</span></div>
                        <div class="field-row"><span class="fl">PWD:</span><span class="fv">
                            @if($a->is_pwd) <span class="badge-yes">Yes</span> @else <span class="badge-no">No</span> @endif
                        </span></div>
                        <div class="field-row"><span class="fl">Address:</span><span class="fv">{{ $a->address }}</span></div>
                        <div class="field-row"><span class="fl">Phone:</span><span class="fv">{{ $a->phone_number }}</span></div>
                        <div class="field-row"><span class="fl">Email:</span><span class="fv">{{ $a->email_address }}</span></div>

                        {{-- PGB Employment --}}
                        @if($a->is_pgb_employee)
                        <div class="sec-title">Current PGB Employment</div>
                        <div class="field-row"><span class="fl">Status:</span><span class="fv">{{ $a->pgb_status ?: '—' }}</span></div>
                        <div class="field-row"><span class="fl">Length of Service:</span><span class="fv">{{ $a->length_of_service ?: '—' }}</span></div>
                        <div class="field-row"><span class="fl">Current Position:</span><span class="fv">{{ $a->current_position ?: '—' }}</span></div>
                        @if($a->years_permanent || $a->years_casual || $a->years_job_order || $a->years_coterminous)
                        <div style="margin-top:3px;">
                            @if($a->years_in_present_position)<span class="year-chip"><span class="year-label">Present Position</span>{{ $a->years_in_present_position }}</span>@endif
                            @if($a->years_permanent)<span class="year-chip"><span class="year-label">Permanent</span>{{ $a->years_permanent }}</span>@endif
                            @if($a->years_coterminous)<span class="year-chip"><span class="year-label">Coterminous</span>{{ $a->years_coterminous }}</span>@endif
                            @if($a->years_casual)<span class="year-chip"><span class="year-label">Casual</span>{{ $a->years_casual }}</span>@endif
                            @if($a->years_job_order)<span class="year-chip"><span class="year-label">Job Order</span>{{ $a->years_job_order }}</span>@endif
                        </div>
                        @endif
                        @endif

                        {{-- Non-PGB Employment --}}
                        @if($a->has_non_pgb_employment && ($a->np_employer || $a->np_designation))
                        <div class="sec-title">Non-PGB Work Experience</div>
                        <div class="field-row"><span class="fl">Employer:</span><span class="fv">{{ $a->np_employer ?: '—' }}</span></div>
                        <div class="field-row"><span class="fl">Designation:</span><span class="fv">{{ $a->np_designation ?: '—' }}</span></div>
                        <div class="field-row"><span class="fl">Period:</span><span class="fv">{{ $a->np_period ?: '—' }}</span></div>
                        <div class="field-row"><span class="fl">Status:</span><span class="fv">{{ $a->np_employment_status ?: '—' }}</span></div>
                        @endif

                    </div>

                    {{-- RIGHT: QUALIFICATIONS ─────────────────────────── --}}
                    <div class="col50">

                        <div class="sec-title">Education</div>
                        <div class="field-row"><span class="fl">Attainment:</span><span class="fv">{{ $a->highest_educational_attainment }}</span></div>
                        <div class="field-row"><span class="fl">Degree / Course:</span><span class="fv">{{ $a->degree ?: '—' }}</span></div>

                        <div class="sec-title">Civil Service Eligibility</div>
                        <div class="field-row"><span class="fl">Eligibility:</span><span class="fv">{{ $a->eligibility ?: '—' }}</span></div>

                        <div class="sec-title">Training &amp; Development</div>
                        <div class="field-row"><span class="fl">Training Hours:</span><span class="fv">{{ $a->training_hours ?: '—' }}</span></div>

                        @if($a->responsibilities)
                        <div class="sec-title">Key Responsibilities</div>
                        <div style="font-size:9px; color:#333; margin-bottom:3px;">{{ $a->responsibilities }}</div>
                        @endif

                        <div class="sec-title">Performance</div>
                        <div class="field-row"><span class="fl">Latest Rating:</span><span class="fv">{{ $a->performance_rating ?: '—' }}</span></div>

                        @if($a->award)
                        <div class="sec-title">Awards &amp; Recognition</div>
                        <div style="font-size:9px; color:#333; margin-bottom:3px;">{{ $a->award }}</div>
                        @endif

                        @if($a->competencies)
                        <div class="sec-title">Core Competencies</div>
                        <div style="font-size:9px; color:#333; margin-bottom:3px;">{{ $a->competencies }}</div>
                        @endif

                        {{-- Document links (text only — PDF can't click) --}}
                        @php
                            $docs = array_filter([
                                'TOR'          => $a->tor_url,
                                'Eligibility'  => $a->eligibility_url,
                                'Training'     => $a->training_url,
                                'Experience'   => $a->experience_url,
                                'Perf. Form'   => $a->performance_form_url,
                                'Award Cert.'  => $a->award_certificate_url,
                                'JAF'          => $a->jaf_url,
                            ]);
                        @endphp
                        @if(count($docs))
                        <div class="sec-title">Submitted Documents</div>
                        <table class="q-table">
                            <thead><tr><th>Document</th><th>Drive Link</th></tr></thead>
                            <tbody>
                                @foreach($docs as $label => $url)
                                <tr>
                                    <td style="width:30%;font-weight:bold;">{{ $label }}</td>
                                    <td style="word-break:break-all;font-size:7.5px;color:#1a56db;">{{ $url }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif

                    </div>
                </div>{{-- end grid2 --}}

                {{-- Declaration footer --}}
                @if($a->acknowledged)
                <div style="margin-top:4px;font-size:8px;color:#555;font-style:italic;border-top:1px dashed #ddd;padding-top:3px;">
                    ✔ Applicant has certified that all information provided is true and correct.
                </div>
                @endif

            </div>{{-- end card-body --}}
        </div>{{-- end card --}}
        @endforeach

    @endforeach

@empty
    <div class="no-data">No applicants found for the selected filters.</div>
@endforelse

{{-- ── SIGNATURE BLOCK ─────────────────────────────────────────── --}}
<table class="sig-table">
    <tr>
        <td style="width:40%;font-weight:bold;">Prepared by (HRMO-Appt. Section Head)</td>
        <td style="width:60%;color:#777;font-style:italic;">Signature / Date</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Reviewed by (HRMO IV / Division Head)</td>
        <td style="color:#777;font-style:italic;">Signature / Date</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Certified by (PHRMO Dept. Head)</td>
        <td style="color:#777;font-style:italic;">Signature / Date / Official Stamp</td>
    </tr>
</table>

</body>
</html>
