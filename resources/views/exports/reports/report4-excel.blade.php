{{-- Report 4 Excel View: List of Retirees --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Employee Name</th>
            <th>Position Title</th>
            <th>Office / Unit</th>
            <th>Date Retired</th>
            <th>Years in Service</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report4 as $idx => $item)
        @php $r = $item['record']; @endphp
        <tr>
            <td>{{ $idx + 1 }}</td>
            <td>{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
            <td>{{ $r->position_title }}</td>
            <td>{{ $r->organizational_unit }}</td>
            <td>{{ $r->date_separated ? \Carbon\Carbon::parse($r->date_separated)->format('m/d/Y') : '—' }}</td>
            <td>{{ $item['years_in_service'] !== null ? $item['years_in_service'] . ' yrs' : '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
