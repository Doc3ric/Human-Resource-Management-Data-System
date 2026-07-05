{{-- Report 1 Excel View: Inventory by Office × Appointment Type --}}
<table>
    <thead>
        <tr>
            <th>OFFICE</th>
            <th>REGULAR</th>
            <th>Elected</th>
            <th>Co-Terminous</th>
            <th>Casual</th>
            <th>Job Order</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report1 as $office => $counts)
        <tr>
            <td>{{ $office }}</td>
            <td>{{ $counts['Permanent'] ?: 0 }}</td>
            <td>{{ $counts['Elected'] ?: 0 }}</td>
            <td>{{ $counts['Co-Terminous'] ?: 0 }}</td>
            <td>{{ $counts['Casual'] ?: 0 }}</td>
            <td>{{ $counts['Job Order'] ?: 0 }}</td>
            <td>{{ $counts['Total'] }}</td>
        </tr>
        @endforeach
        @if(count($report1) > 0)
        <tr>
            <td><strong>GRAND TOTAL</strong></td>
            <td>{{ array_sum(array_column($report1, 'Permanent')) }}</td>
            <td>{{ array_sum(array_column($report1, 'Elected')) }}</td>
            <td>{{ array_sum(array_column($report1, 'Co-Terminous')) }}</td>
            <td>{{ array_sum(array_column($report1, 'Casual')) }}</td>
            <td>{{ array_sum(array_column($report1, 'Job Order')) }}</td>
            <td>{{ array_sum(array_column($report1, 'Total')) }}</td>
        </tr>
        @endif
    </tbody>
</table>
