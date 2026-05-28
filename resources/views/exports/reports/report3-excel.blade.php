{{-- Report 3 Excel View: Newly Hired / Promoted / Demoted --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Office</th>
            <th>Employee Name</th>
            <th>Position Title</th>
            <th>Status / Category</th>
            <th>Nature of Appointment</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report3->groupBy('organizational_unit') as $office => $records)
        @foreach($records as $idx => $r)
        <tr>
            <td>{{ $idx + 1 }}</td>
            <td>{{ $office }}</td>
            <td>{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
            <td>{{ $r->position_title }}</td>
            <td>{{ $r->resolved_category ?? $r->employment_status }}</td>
            <td>{{ $r->nature_of_appointment ?: 'N/A' }}</td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>
