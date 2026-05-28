<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Retirement Report</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; margin: 20px; }
    h1   { font-size: 15px; font-weight: 700; margin: 0 0 2px; }
    .sub { font-size: 10px; color: #6b7280; margin-bottom: 14px; }
    h2   { font-size: 12px; font-weight: 700; margin: 14px 0 6px; border-left: 3px solid #1e3a5f; padding-left: 8px; color: #1e3a5f; }
    table { width: 100%; border-collapse: collapse; }
    thead th {
        background: #1e3a5f; color: #fff; text-align: left;
        padding: 7px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: .4px;
    }
    tbody td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
    tbody tr:nth-child(even) td { background: #f9fafb; }
    .name { font-weight: 700; text-transform: uppercase; }
    .item { font-family: monospace; color: #6b7280; font-size: 9px; }
    .age-o { background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; font-weight: 700; }
    .age-n { background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; font-weight: 700; }
    .age-op{ background: #fef9c3; color: #d97706; padding: 2px 6px; border-radius: 4px; font-weight: 700; }
    .footer { margin-top: 16px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    .empty { text-align: center; color: #9ca3af; padding: 14px; }
    .ann   { font-size: 8px; color: #6b7280; max-width: 220px; word-wrap: break-word; }
</style>
</head>
<body>
<h1>Retirement Automation Report</h1>
<div class="sub">As of {{ now()->format('F d, Y') }}</div>

<h2>{{ $title }} &mdash; {{ $list->count() }} record(s)</h2>

@if($tab === 'history')
{{-- History columns --}}
<table>
    <thead>
        <tr>
            <th style="width:25px;">#</th>
            <th style="width:80px;">Item No.</th>
            <th>Office / Unit</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Position Title</th>
            <th style="width:65px;">SG-Step</th>
            <th style="width:80px;">Date Retired</th>
        </tr>
    </thead>
    <tbody>
        @forelse($list as $i => $r)
        @php
            $annotation = $r->comment_annotation ?? '';
            $formerLast = '';  $formerFirst = '';
            if (preg_match('/Former employee:\s*([^.]+)\./i', $annotation, $m)) {
                $namePart = trim($m[1]);
                if (str_contains($namePart, ',')) {
                    [$ln, $fn] = explode(',', $namePart, 2);
                    $formerLast  = strtoupper(trim($ln));
                    $formerFirst = trim($fn);
                } else {
                    $formerLast = strtoupper(trim($namePart));
                }
            }
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="item">{{ $r->item }}</td>
            <td>{{ $r->organizational_unit }}</td>
            <td class="name">{{ $formerLast ?: '—' }}</td>
            <td>{{ $formerFirst ?: '—' }}</td>
            <td>{{ $r->position_title }}</td>
            <td>SG-{{ $r->salary_grade }}/{{ $r->step }}</td>
            <td>{{ $r->retired_at?->format('m/d/Y') }}</td>
        </tr>
        @empty
        <tr><td colspan="8" class="empty">No retirement history records found.</td></tr>
        @endforelse
    </tbody>
</table>

@else
{{-- Overdue / Near / Optional columns --}}
<table>
    <thead>
        <tr>
            <th style="width:25px;">#</th>
            <th>Name / Item</th>
            <th style="width:55px;">Age</th>
            <th style="width:85px;">Date of Birth</th>
            @if($tab === 'near')
            <th style="width:90px;">Retirement Date</th>
            @endif
            <th>Position Title</th>
            <th>Office / Unit</th>
            <th style="width:60px;">SG-Step</th>
        </tr>
    </thead>
    <tbody>
        @forelse($list as $i => $r)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>
                <span class="name">{{ $r->last_name }}, {{ $r->first_name }}</span><br>
                <span class="item">{{ $r->item }}</span>
            </td>
            <td>
                @if($tab === 'overdue')
                    <span class="age-o">{{ $r->age }} yrs</span>
                @elseif($tab === 'near')
                    <span class="age-n">{{ $r->age }} yrs</span>
                @else
                    <span class="age-op">{{ $r->age }} yrs</span>
                @endif
            </td>
            <td>{{ $r->date_of_birth?->format('m/d/Y') }}</td>
            @if($tab === 'near')
            <td>{{ $r->retirement_date?->format('m/d/Y') }}</td>
            @endif
            <td>{{ $r->position_title }}</td>
            <td>{{ $r->organizational_unit }}</td>
            <td>SG-{{ $r->salary_grade }}/{{ $r->step }}</td>
        </tr>
        @empty
        <tr><td colspan="{{ $tab === 'near' ? 8 : 7 }}" class="empty">No records found.</td></tr>
        @endforelse
    </tbody>
</table>
@endif

<div class="footer">Generated by HDMS- Human Resource Data Management System &nbsp;·&nbsp; {{ now()->format('Y-m-d H:i') }}</div>
</body>
</html>
