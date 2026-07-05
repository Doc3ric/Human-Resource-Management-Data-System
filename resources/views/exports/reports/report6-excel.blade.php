{{-- Report 6 Excel View: Employment Status per Office --}}
<table>
    <thead>
        <tr>
            <th>Office</th>
            <th>Employment Status</th>
            <th>Employee Name</th>
            <th>Position Title</th>
            <th>SEX</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report6 as $office => $catGroups)
        @foreach($catGroups as $cat => $records)
        @foreach($records as $idx => $r)
        <tr>
            <td>{{ $idx === 0 && $loop->parent->first ? $office : '' }}</td>
            <td>{{ $idx === 0 ? $cat : '' }}</td>
            <td>{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
            <td>{{ $r->position_title }}</td>
            <td>{{ $r->sex ?: '—' }}</td>
        </tr>
        @endforeach
        @endforeach
        @endforeach
    </tbody>
</table>
