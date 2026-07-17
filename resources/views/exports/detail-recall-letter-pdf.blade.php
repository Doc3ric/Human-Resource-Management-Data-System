<!DOCTYPE html>
<html>
<head>
<style>
    @page { size: A4; margin: 0.5in 0.5in 0.1in 0.5in; }
    body { font-family: 'Times New Roman', serif; font-size: 10.5pt; padding: 0; line-height: 1.15; color: #000; }

    .date { margin-bottom: 15px; }

    .addressee { margin-bottom: 10px; line-height: 1.3; }
    .addressee b { font-size: 11pt; text-transform: uppercase; }

    .subject { margin-bottom: 10px; }
    .subject b { text-transform: uppercase; }

    .salutation { margin-bottom: 15px; }

    .body-text { text-align: justify; margin-bottom: 15px; text-indent: 0; }
    .body-text p { margin: 8px 0; text-align: justify; }

    .closing { margin-top: 15px; margin-bottom: 60px; }

    .signatory { line-height: 1.2; margin-bottom: 2px; }
    .signatory b { font-weight: bold; text-transform: uppercase; }

    .footer { position: fixed; bottom: 0.02in; left: 0; font-size: 8pt; font-weight: bold; }
</style>
</head>
<body>

@include('exports.partials.official-header')

<div class="date">{{ $dateIssued }}</div>

<div class="addressee">
    <b>The Head of Office</b><br>
    {{ $detailedUnitOffice }}
</div>

<div class="subject">
    Subject: <b>Recall of Detailed Employee &mdash; {{ $employeeName }}</b>
</div>

<div class="salutation">
    Sir/Madam:
</div>

<div class="body-text">
    <p>This is to formally recall <strong>{{ $employeeName }}</strong>, currently detailed to your
    office per Detail Order No. <strong>{{ $detailOrderNo }}</strong> dated {{ $dateOfOrder }}, the
    one (1) year maximum period of detail having lapsed on <strong>{{ $oneYearMark }}</strong>.</p>

    <p>{{ $employeeName }} is directed to report back to <strong>{{ $homeUnit }}</strong> effective
    immediately upon receipt of this letter.</p>

    <p>Thank you for your cooperation.</p>
</div>

<div class="closing">
    Very truly yours,
</div>

<div class="signatory">
    <b>{{ $signatoryName }}</b><br>
    {{ $signatoryPosition }}
</div>

<div class="footer">
    Detail Order No. {{ $detailOrderNo }}
</div>

</body>
</html>
