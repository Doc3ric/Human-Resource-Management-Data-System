{{-- Report 2 Excel View: Inventory by Appointment Status with SEX & Age --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Status</th>
            <th>Employee Name</th>
            <th>Position Title</th>
            <th>SEX</th>
            <th>Age</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report2 as $cat => $items)
        @foreach($items as $idx => $item)
        @php $r = $item['record']; @endphp
        <tr>
            <td>{{ $idx + 1 }}</td>
            <td>{{ $cat }}</td>
            <td>{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
            <td>{{ $r->position_title }}</td>
            <td>{{ $r->sex ?: '—' }}</td>
            <td>{{ $item['age'] ?: '—' }}</td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>
