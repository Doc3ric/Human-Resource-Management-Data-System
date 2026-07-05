{{-- Report 7 Excel View: Custom Status Report --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Employee Name</th>
            <th>Position Title</th>
            <th>Office / Unit</th>
            <th>Date Effective</th>
            <th>Status Detail</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report7 as $idx => $r)
        <tr>
            <td>{{ $idx + 1 }}</td>
            <td>{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
            <td>{{ $r->position_title }}</td>
            <td>{{ $r->office_department }}</td>
            <td>
                @if($r7_status === 'Newly Hired')
                    {{ $r->date_original_appointment ? \Carbon\Carbon::parse($r->date_original_appointment)->format('m/d/Y') : '—' }}
                @elseif($r7_status === 'Promoted')
                    {{ $r->date_last_promotion ? \Carbon\Carbon::parse($r->date_last_promotion)->format('m/d/Y') : '—' }}
                @elseif($r7_status === 'Retired' || $r7_status === 'Terminated')
                    {{ $r->date_separated ? \Carbon\Carbon::parse($r->date_separated)->format('m/d/Y') : '—' }}
                @else
                    —
                @endif
            </td>
            <td>
                @if($r7_status === 'Newly Hired' || $r7_status === 'Promoted')
                    {{ $r->nature_of_appointment ?: 'N/A' }}
                @else
                    {{ $r->nature_of_separation ?: 'N/A' }}
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
