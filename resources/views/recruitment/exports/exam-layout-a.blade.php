<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Layout A - Examiner's Copy</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #111; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin: 0; font-weight: bold; }
        .header h2 { font-size: 14px; margin: 5px 0 0; color: #333; }
        .header h3 { font-size: 12px; margin: 5px 0 0; font-weight: normal; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        th, td { border: 1px solid #555; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #e2e8f0; font-weight: bold; }
        .group-header { background-color: #cbd5e1; font-weight: bold; padding: 8px; font-size: 12px; }
        .sub-group-header { background-color: #f1f5f9; font-weight: bold; font-size: 11px; font-style: italic; }
        .text-center { text-align: center; }
        .sig-box { border-bottom: 1px solid #000; display: inline-block; width: 100px; height: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Provincial Government of Bukidnon — PHRMO — TWG Applicant Profile</h1>
        <h2>Examination Roster (LAYOUT A — EXAMINER'S COPY)</h2>
        <h3>Classification: {{ $classification === 'pgb_jo' ? 'PGB Job Order' : 'External' }} | Date: {{ $date !== 'none' ? \Carbon\Carbon::parse($date)->format('F d, Y') : 'TBA' }} | Time: {{ $time !== 'none' ? $time : 'TBA' }} | Room: {{ $room !== 'none' ? $room : 'TBA' }}</h3>
    </div>

    @php
        // Grouping: Office -> Position Title -> Status
        $grouped = $applicants->groupBy('office')->map(function($officeGroup) {
            return $officeGroup->groupBy('position_applied')->map(function($posGroup) {
                return $posGroup->groupBy('pgb_status');
            });
        });
    @endphp

    @if($grouped->isEmpty())
        <p>No applicants found for this schedule.</p>
    @else
        @foreach($grouped as $office => $positions)
            <div class="group-header">Office: {{ $office ?: 'N/A' }}</div>
            
            @foreach($positions as $position => $statuses)
                @php
                    $firstApp = $statuses->flatten()->first();
                @endphp
                <div class="sub-group-header" style="border: 1px solid #555; border-bottom: none; padding: 5px;">
                    Position: {{ $position }} (Item: {{ $firstApp->item_no ?: 'N/A' }}, SG: {{ $firstApp->salary_grade_snapshot ?: 'N/A' }})
                </div>
                
                @foreach($statuses as $status => $statusApplicants)
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%;">Table No.</th>
                                <th style="width: 12%;">Date/Time</th>
                                <th style="width: 12%;">Applicant No.</th>
                                <th style="width: 20%;">Full Name</th>
                                <th style="width: 10%;">PGB Status</th>
                                <th style="width: 10%;">Contact No.</th>
                                <th style="width: 20%;">Address</th>
                                <th style="width: 11%;">Signature</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statusApplicants as $app)
                                <tr>
                                    <td class="text-center">{{ $app->table_number ?: 'N/A' }}</td>
                                    <td>{{ $app->exam_date_time }}</td>
                                    <td>{{ $app->reference_no ?: $app->ain ?: 'N/A' }}</td>
                                    <td>{{ strtoupper($app->last_name) }}, {{ $app->first_name }} {{ $app->middle_name }} {{ $app->name_extension }}</td>
                                    <td>{{ $app->is_pgb_employee ? 'PGB JO' : 'External' }}</td>
                                    <td>{{ $app->phone_number ?: 'N/A' }}</td>
                                    <td>{{ $app->address ?: 'N/A' }}</td>
                                    <td class="text-center"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endforeach
        @endforeach
    @endif
</body>
</html>
