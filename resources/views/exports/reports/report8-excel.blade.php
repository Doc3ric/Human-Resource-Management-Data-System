{{-- Report 8 Excel View: Detailed Employees --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Employee Name</th>
            <th>Detailed Unit</th>
            <th>Date of Movement</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report8 as $idx => $order)
        <tr>
            <td>{{ $idx + 1 }}</td>
            <td>{{ strtoupper($order->plantillaRecord?->last_name ?? '') }}, {{ $order->plantillaRecord?->first_name ?? '' }}</td>
            <td>{{ $order->detailed_unit }}</td>
            <td>{{ $order->date_effective_start->format('m/d/Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
