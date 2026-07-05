{{-- Report 5 Excel View: Separated / Terminated Employees --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Employee Name</th>
            <th>Position Title</th>
            <th>Office / Unit</th>
            <th>Date Effectivity</th>
            <th>Nature of Separation</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report5 as $idx => $r)
        <tr>
            <td>{{ $idx + 1 }}</td>
            <td>{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
            <td>{{ $r->position_title }}</td>
            <td>{{ $r->office_department }}</td>
            <td>{{ $r->date_separated ? \Carbon\Carbon::parse($r->date_separated)->format('m/d/Y') : '—' }}</td>
            <td>{{ $r->nature_of_separation ?: 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
