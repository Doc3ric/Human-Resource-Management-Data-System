<!DOCTYPE html>
<html>
<head>
<style>
    @page { size: A4 landscape; margin: 0.5in; }
    body { font-family: 'DejaVu Sans', sans-serif; text-align: center; padding: 60px; line-height: 1.4; }
    .border { border: 6px double #1e3a5f; padding: 50px; }
    h1 { font-size: 14px; letter-spacing: 3px; color: #1e3a5f; }
    h2 { font-size: 26px; margin: 20px 0; }
    .name { font-size: 28px; font-weight: bold; margin: 20px 0; text-decoration: underline; }
    .body-text { font-size: 13px; line-height: 1.8; margin: 0 60px; }
    .signatures { margin-top: 60px; display: flex; justify-content: space-around; }
    .sig { border-top: 1px solid #000; padding-top: 6px; font-size: 11px; width: 200px; }
</style>
</head>
<body>
<div class="border">
    <h1>PROVINCIAL GOVERNMENT OF BUKIDNON — PHRMO</h1>
    <h2>{{ $certificateType === 'resource_speaker' ? 'Certificate of Appreciation' : 'Certificate of Attendance' }}</h2>
    <p class="body-text">This is to certify that</p>
    <div class="name">{{ $participantName }}</div>
    <p class="body-text">
        @if($certificateType === 'resource_speaker')
            is hereby recognized in appreciation for serving as Resource Speaker on "<b>{{ $topic }}</b>"
            held on {{ $dateRange }} at {{ $venue }}, conducted by {{ $institution }}.
        @else
            has satisfactorily attended "<b>{{ $trainingTitle }}</b>", a {{ $trainingType }} activity
            held on {{ $dateRange }} at {{ $venue }}, conducted by {{ $institution }},
            with a total of {{ $hours }} training hours.
        @endif
    </p>
    <div class="signatures">
        @foreach($signatories as $sig)
            <div class="sig">{{ $sig['name'] }}<br>{{ $sig['position'] }}</div>
        @endforeach
    </div>
    <p style="font-size:10px;color:#9ca3af;margin-top:30px;">Reference No: {{ $referenceNo }}</p>
</div>
</body>
</html>
