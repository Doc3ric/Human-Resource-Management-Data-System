<!DOCTYPE html>
<html>
<head>
<style>
    @page { size: A4; margin: 0.5in 0.5in 0.1in 0.5in; }
    body { font-family: 'Times New Roman', serif; font-size: 10.5pt; padding: 0; line-height: 1.15; color: #000; }

    /* Header now comes from exports.partials.official-header (scoped styles) */

    .date { margin-bottom: 15px; }

    .addressee { margin-bottom: 10px; line-height: 1.3; }
    .addressee b { font-size: 11pt; text-transform: uppercase; }

    .salutation { margin-bottom: 15px; }

    .body-text { text-align: justify; margin-bottom: 15px; text-indent: 0; }
    .body-text p { margin: 8px 0; text-align: justify; }

    .quote { margin: 15px 40px; text-align: justify; font-style: italic; font-size: 10.5pt; }

    /* Compact, single-line penalty rows — no wrapping — to save vertical
       space in the document. Narrow side margins + smaller font give the
       description column enough width to stay on one line. */
    .penalties { margin: 10px 10px; font-size: 10pt; }
    .penalties table { border-collapse: collapse; width: 100%; }
    .penalties td { padding: 2px 8px; vertical-align: top; white-space: nowrap; }

    /* 3.5 line-spaces (10.5pt body, line-height 1.15 ≈ 17px/line) after
       "Respectfully yours," for a physical signature above the printed
       signatory name/position. */
    .closing { margin-top: 15px; margin-bottom: 60px; }

    .signatory { line-height: 1.2; margin-bottom: 2px; }
    .signatory b { font-weight: bold; text-transform: uppercase; }

    /* At least 1 line-space between the signatory (PG Department Head /
       PHRM Officer) and the Received By block — the block itself stays
       compressed (small font, tight padding), just the gap before it
       needs room. */
    .signatory-gap { height: 17px; }

    .received-block { margin-top: 2px; border-top: 1px solid #ccc; padding-top: 2px; font-size: 8pt; }
    .received-block table { width: 100%; border-collapse: collapse; }
    .received-block td { padding: 0; vertical-align: top; }
    .received-line { display: inline-block; width: 160px; border-bottom: 1px solid #000; }

    .cc-block { margin-top: 0; font-size: 8.5pt; }

    .footer { position: fixed; bottom: 0.02in; left: 0; font-size: 8pt; font-weight: bold; }
</style>
</head>
<body>

@include('exports.partials.official-header')

<div class="date">{{ now()->format('d F Y') }}</div>

<div class="addressee">
    <b>{!! $customAddresseeName ?? (($prefix ?? '') . ' ' . $employeeName) !!}</b><br>
    {!! $customAddresseePosition ?? ($employeePosition ?? '') !!}<br>
    {!! $customAddresseeOffice ?? $employeeOffice !!}
</div>

<div class="salutation">
    {!! $customSalutation ?? ('Dear ' . ($prefix ?? '') . ' ' . ($lastName ?? explode(' ', $employeeName)[count(explode(' ', $employeeName)) - 1]) . ':') !!}
</div>

<div class="body-text">
    {!! $bodyText !!}
</div>

<div class="closing">
    Respectfully yours,
</div>

<div class="signatory">
    <b>{{ $signatoryName }}</b><br>
    {{ $signatoryPosition }}
</div>

<div class="signatory-gap"></div>

@if(isset($ccRecipients) && $ccRecipients)
<div class="cc-block">
    <strong>Copy Furnished:</strong><br>
    {!! $ccRecipients !!}
</div>
@endif

<div class="received-block">
    <div style="margin-bottom: 2px; font-weight: bold;">Received by:</div>
    <table>
        <tr>
            <td style="width: 50%;">
                Signature: <span class="received-line">&nbsp;</span>
            </td>
            <td>
                Date: <span class="received-line">&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td>
                Printed Name: <span class="received-line">&nbsp;</span>
            </td>
            <td></td>
        </tr>
    </table>
</div>

<div class="footer">
    Reference No. {{ $referenceNo }}
</div>

</body>
</html>
