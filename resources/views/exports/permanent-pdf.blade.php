<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 7px; color: #000; }

    .report-header { padding: 8px 10px; margin-bottom: 4px; border-bottom: 2px solid #312e81; }
    .report-header h1 { font-size: 12px; font-weight: 800; color: #312e81; }
    .report-header p  { font-size: 7px; color: #444; margin-top: 2px; }

    .meta-table { width: 100%; margin-bottom: 6px; }
    .meta-table td { font-size: 7px; color: #333; padding: 0 10px 2px 0; }

    table.dt {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        page-break-inside: auto;
    }
    thead { display: table-header-group; }
    thead th {
        background: #312e81;
        color: #fff;
        padding: 4px 2px;
        font-size: 6px;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid #3730a3;
    }
    tbody td {
        padding: 2px 2px;
        font-size: 6px;
        border: 1px solid #ccc;
        color: #000;
        vertical-align: middle;
        overflow: hidden;
    }
    .r-even td { background: #eef2ff; }
    .col-no   { width: 2%;  text-align: center; }
    .col-unit { width: 13%; }
    .col-item { width: 5%; }
    .col-pos  { width: 11%; }
    .col-sg   { width: 3%;  text-align: center; }
    .col-stp  { width: 2%;  text-align: center; }
    .col-sal  { width: 7%;  text-align: right; }
    .col-nm   { width: 6%;  text-transform: uppercase; }
    .col-fn   { width: 6%; }
    .col-mn   { width: 5%; }
    .col-sx   { width: 2%;  text-align: center; }
    .col-dob  { width: 5%;  text-align: center; }
    .col-tin  { width: 5%; }
    .col-dt   { width: 5%;  text-align: center; }
    .col-eli  { width: 10%; }
    .col-st   { width: 5%;  text-align: center; font-weight: 700; }
    .text-g   { color: #666; }
</style>
</head>
<body>

<div class="report-header">
    <h1>HDMS- Human Resource Data Management System &mdash; REGULAR Employees Report</h1>
    <p>REGULAR / Co-Terminous / Elected &mdash; {{ now()->format('F d, Y') }}</p>
</div>

<table class="meta-table">
    <tr>
        <td>Generated: {{ now()->format('m/d/Y h:i A') }}</td>
        <td>Total Records: {{ number_format(count($records)) }}</td>
        @if(!empty(array_filter($filters ?? [])))<td>Filtered view</td>@endif
    </tr>
</table>

<table class="dt">
    <thead>
        <tr>
            <th class="col-no">#</th>
            @if(empty($columns) || in_array('office_department', $columns))<th class="col-unit">ORG UNIT</th>@endif
            @if(empty($columns) || in_array('item_no_new', $columns))<th class="col-item">ITEM</th>@endif
            @if(empty($columns) || in_array('position_title', $columns))<th class="col-pos">POSITION</th>@endif
            @if(empty($columns) || in_array('salary_grade', $columns))<th class="col-sg">SG</th>@endif
            @if(empty($columns) || in_array('step', $columns))<th class="col-stp">STP</th>@endif
            @if(empty($columns) || in_array('base_salary_amount', $columns))<th class="col-sal">MONTHLY SALARY</th>@endif
            @if(empty($columns) || in_array('last_name', $columns))<th class="col-nm">LAST NAME</th>@endif
            @if(empty($columns) || in_array('first_name', $columns))<th class="col-fn">FIRST NAME</th>@endif
            @if(empty($columns) || in_array('middle_name', $columns))<th class="col-mn">M.I.</th>@endif
            @if(empty($columns) || in_array('name_extension', $columns))<th class="col-mn">SUFFIX</th>@endif
            @if(empty($columns) || in_array('sex', $columns))<th class="col-sx">SEX</th>@endif
            @if(empty($columns) || in_array('date_of_birth', $columns))<th class="col-dob">DATE OF BIRTH</th>@endif
            @if(empty($columns) || in_array('tin', $columns))<th class="col-tin">TIN</th>@endif
            @if(empty($columns) || in_array('date_original_appointment', $columns))<th class="col-dt">ORIG APPT</th>@endif
            @if(empty($columns) || in_array('date_last_promotion', $columns))<th class="col-dt">LAST PROMO</th>@endif
            @if(empty($columns) || in_array('civil_service_eligibility', $columns))<th class="col-eli">CS ELIGIBILITY</th>@endif
            @if(empty($columns) || in_array('status', $columns))<th class="col-st">STATUS</th>@endif
        </tr>
    </thead>
    <tbody>
        @foreach($records as $i => $r)
        @php
            $s   = $r['employment_status'] ?? '';
            $vac = $r['is_vacant'] ?? false;
            $statusLabel = match(true) {
                in_array($s, ['P','Permanent'])                   => 'Permanent',
                in_array($s, ['CT','Co-Terminous','Coterminous']) => 'Co-Term.',
                in_array($s, ['E','Elected'])                     => 'Elected',
                (bool)$vac                                        => 'VACANT',
                default                                           => ($s ?: '—'),
            };
            $rc = ($i % 2 === 1) ? 'r-even' : '';
            $dob  = $r['date_of_birth']             ? \Carbon\Carbon::parse($r['date_of_birth'])->format('m/d/Y')             : '—';
            $doa  = $r['date_original_appointment'] ? \Carbon\Carbon::parse($r['date_original_appointment'])->format('m/d/Y') : '—';
            $dlp  = $r['date_last_promotion']       ? \Carbon\Carbon::parse($r['date_last_promotion'])->format('m/d/Y')       : '—';
        @endphp
        <tr class="{{ $rc }}">
            <td class="col-no text-g">{{ $i + 1 }}</td>
            @if(empty($columns) || in_array('office_department', $columns))<td class="col-unit">{{ $r['office_department'] }}</td>@endif
            @if(empty($columns) || in_array('item_no_new', $columns))<td class="col-item">{{ $r['item_no_new'] ?: '—' }}</td>@endif
            @if(empty($columns) || in_array('position_title', $columns))<td class="col-pos">{{ $r['position_title'] }}</td>@endif
            @if(empty($columns) || in_array('salary_grade', $columns))<td class="col-sg">{{ $r['salary_grade'] ? 'SG-'.$r['salary_grade'] : '—' }}</td>@endif
            @if(empty($columns) || in_array('step', $columns))<td class="col-stp">{{ $r['step'] ?: '—' }}</td>@endif
            @if(empty($columns) || in_array('base_salary_amount', $columns))<td class="col-sal">{{ $r['base_salary_amount'] ? number_format($r['base_salary_amount'],2) : '—' }}</td>@endif
            @if(empty($columns) || in_array('last_name', $columns))<td class="col-nm">{{ $vac ? 'VACANT' : strtoupper($r['last_name'] ?? '—') }}</td>@endif
            @if(empty($columns) || in_array('first_name', $columns))<td class="col-fn">{{ $r['first_name'] ?: '—' }}</td>@endif
            @if(empty($columns) || in_array('middle_name', $columns))<td class="col-mn">{{ $r['middle_name'] ?: '—' }}</td>@endif
            @if(empty($columns) || in_array('name_extension', $columns))<td class="col-mn">{{ $r['name_extension'] ?: '—' }}</td>@endif
            @if(empty($columns) || in_array('sex', $columns))<td class="col-sx">{{ $r['sex'] ?: '—' }}</td>@endif
            @if(empty($columns) || in_array('date_of_birth', $columns))<td class="col-dob">{{ $dob }}</td>@endif
            @if(empty($columns) || in_array('tin', $columns))<td class="col-tin">{{ $r['tin'] ?: '—' }}</td>@endif
            @if(empty($columns) || in_array('date_original_appointment', $columns))<td class="col-dt">{{ $doa }}</td>@endif
            @if(empty($columns) || in_array('date_last_promotion', $columns))<td class="col-dt">{{ $dlp }}</td>@endif
            @if(empty($columns) || in_array('civil_service_eligibility', $columns))<td class="col-eli">{{ $r['civil_service_eligibility'] ?: '—' }}</td>@endif
            @if(empty($columns) || in_array('status', $columns))<td class="col-st">{{ $statusLabel }}</td>@endif
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
