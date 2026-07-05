<!DOCTYPE html>
<html>
<head>
<style>
    @page { size: A4; margin: 0.5in 1in 0.5in 0.5in; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; padding: 40px; line-height: 1.5; }
    .title { text-align: center; font-weight: bold; text-decoration: underline; margin: 20px 0; font-size: 13px; }
    .body-text { line-height: 1.7; text-align: justify; }
    .facts { background: #f9fafb; border: 1px solid #e5e7eb; padding: 10px; margin: 14px 0; }
    .sig-block { margin-top: 50px; }
    .footer { margin-top: 40px; font-size: 9.5px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
</style>
</head>
<body>
@include('exports.partials.official-header')

<div class="title">{{ $letterTitle }}</div>

<p class="body-text">Date: {{ $dateIssued }}</p>
<p class="body-text">To: <b>Provincial Accounting / Payroll Office</b></p>

<p class="body-text">{{ $bodyText }}</p>

<div class="facts">
    <b>Documented Facts:</b>
    <ul>
        @foreach($facts as $label => $value)
            <li>{{ $label }}: {{ $value }}</li>
        @endforeach
    </ul>
</div>

<p class="body-text">
    @if($actionType === 'DROP')
        Please initiate the process to drop this employee from the active payroll immediately.
    @else
        Please deduct the equivalent computed salary for the LWOP/Undertime days specified above from the next available payroll.
    @endif
</p>

<div class="sig-block">
    <p>_______________________________<br>{{ $signatoryName }}<br>{{ $signatoryPosition }}</p>
</div>

<div class="footer">
    Reference: {{ $referenceNo }} — This is an official system-generated coordination letter.
</div>
</body>
</html>
