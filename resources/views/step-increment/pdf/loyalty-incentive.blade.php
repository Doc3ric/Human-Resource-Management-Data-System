<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Loyalty Incentive Certificate — {{ $employee->first_name }} {{ $employee->last_name }}</title>
    <style>
        /* ── Reset ─────────────────────────────────────────── */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Serif', Georgia, serif;
            font-size: 10pt;
            color: #1a1a1a;
            background: #fff;
        }

        /* ── Page wrapper ──────────────────────────────────── */
        .page {
            width: 100%;
            min-height: 200mm;
            position: relative;
            padding: 18px 28px 18px 28px;
            overflow: hidden;
        }

        .page-break {
            page-break-after: always;
        }

        /* ── Background image (full-page) ─────────────────── */
        .page-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 1.0;
            z-index: 0;
        }

        /* When a custom background is set, hide the CSS strips —
           the uploaded image already has its own decorative design */
        .has-bg .corner-tl,
        .has-bg .strip-tr,
        .has-bg .strip-bl,
        .has-bg .corner-br {
            display: none;
        }

        /* ── Decorative corner strips (gold & black diagonal) ── */
        /* Top-left gold triangle */
        .corner-tl {
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            border-top: 88px solid #b8912a;
            border-right: 88px solid transparent;
        }

        /* Top-right gold strip */
        .strip-tr {
            position: absolute;
            top: 0;
            right: 0;
            width: 55px;
            height: 100%;
            background: linear-gradient(180deg, #b8912a 0%, #f0c853 30%, #b8912a 60%, #1a1a1a 100%);
            opacity: .92;
        }

        /* Bottom-left black strip */
        .strip-bl {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 55px;
            height: 82%;
            background: linear-gradient(0deg, #1a1a1a 0%, #333 40%, #b8912a 100%);
            opacity: .88;
        }

        /* Bottom-right corner accent */
        .corner-br {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 0;
            height: 0;
            border-bottom: 80px solid #b8912a;
            border-left: 80px solid transparent;
        }

        /* ── Inner content (sits above decorations) ─────────── */
        .inner {
            position: relative;
            z-index: 5;
            margin: 0 62px;
            /* push content away from side strips */
            text-align: center;
        }

        /* ── Header ─────────────────────────────────────────── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .logo-cell {
            width: 190px;
            text-align: left;
            white-space: nowrap;
        }

        .logo-cell img {
            height: 65px;
            width: auto;
            vertical-align: middle;
        }

        .logo-right img {
            height: 65px;
            width: auto;
            vertical-align: middle;
        }

        .logo-right {
            width: 190px;
            text-align: right;
        }

        .header-center {
            text-align: center;
        }

        .header-republic {
            font-size: 8pt;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        .header-province {
            font-size: 9pt;
            font-weight: bold;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        .header-city {
            font-size: 8pt;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin-bottom: 3px;
        }

        .header-divider {
            border-top: 2px solid #1a1a1a;
            margin: 4px 0 3px;
        }

        .header-office {
            font-size: 8.5pt;
            font-weight: bold;
            letter-spacing: 1.2px;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1a1a1a;
            text-transform: uppercase;
        }

        /* ── Title block ─────────────────────────────────────── */
        .cert-title {
            font-size: 32pt;
            font-weight: 900;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #1a1a1a;
            margin-top: 8px;
            margin-bottom: 0px;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.1;
        }

        .cert-subtitle {
            font-size: 9pt;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin-bottom: 4px;
            color: #333;
        }

        /* Laurel wreath separator (text art) */
        .laurel {
            font-size: 12pt;
            color: #b8912a;
            margin: 2px 0 6px;
        }

        /* ── Employee name (italic script-like) ──────────────── */
        .emp-name {
            font-size: 24pt;
            font-style: italic;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 2px;
            font-family: 'DejaVu Serif', Georgia, serif;
        }

        .emp-position {
            font-size: 9pt;
            font-weight: bold;
            color: #1a1a1a;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin-bottom: 1px;
        }

        .emp-office {
            font-size: 9pt;
            color: #444;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin-bottom: 10px;
        }

        /* ── Body paragraph ─────────────────────────────────── */
        .cert-body {
            font-size: 9.5pt;
            text-align: justify;
            line-height: 1.65;
            font-family: 'DejaVu Serif', Georgia, serif;
            margin-bottom: 14px;
            padding: 0 10px;
        }

        .underline-fill {
            display: inline;
            border-bottom: 1px solid #333;
            padding: 0 6px;
            font-weight: bold;
        }

        .ref-line {
            font-size: 9pt;
            font-family: 'DejaVu Serif', Georgia, serif;
            margin-bottom: 8px;
        }

        /* ── Signatory ──────────────────────────────────────── */
        .signatory {
            text-align: center;
            padding-top: 120px;
        }

        .sig-name {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            letter-spacing: 0.5px;
        }

        .sig-title {
            font-size: 8.5pt;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #333;
        }

        /* ── Footer label ───────────────────────────────────── */
        .page-label {
            position: absolute;
            bottom: 8px;
            left: 70px;
            font-size: 8pt;
            font-weight: bold;
            color: #b8912a;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        /* ── Page 2 table ───────────────────────────────────── */
        .benefit-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 14px;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        .benefit-table th {
            background: #c8962c;
            color: #fff;
            font-size: 9pt;
            font-weight: bold;
            padding: 9px 10px;
            border: 1px solid #a07520;
            text-align: center;
        }

        .benefit-table td {
            font-size: 10pt;
            font-weight: bold;
            padding: 11px 10px;
            border: 1px solid #c8a450;
            text-align: center;
            background: #fdf6e3;
            color: #1a1a1a;
        }

        .cert-footer-text {
            font-size: 9pt;
            text-align: center;
            font-family: 'DejaVu Serif', Georgia, serif;
            line-height: 1.6;
            margin-bottom: 14px;
        }
    </style>
</head>

<body>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- PAGE 1 — PERSONAL FILE COPY --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="page page-break {{ !empty($backgroundPath) && file_exists($backgroundPath) ? 'has-bg' : '' }}">

        {{-- Background image (if set) --}}
        @if(!empty($backgroundPath) && file_exists($backgroundPath))
            <img src="{{ $backgroundPath }}" class="page-bg" alt="">
        @endif

        {{-- Corner / strip decorations --}}
        <div class="corner-tl"></div>
        <div class="strip-tr"></div>
        <div class="strip-bl"></div>
        <div class="corner-br"></div>

        <div class="inner">

            {{-- Header --}}
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        <img src="{{ public_path('img/phrmologo.png') }}" alt="PHRMO Logo">
                        &nbsp;
                        <img src="{{ public_path('img/logo.png') }}" alt="Province Seal">
                    </td>
                    <td class="header-center">
                        <div class="header-republic">Republic of the Philippines</div>
                        <div class="header-province">PROVINCE OF BUKIDNON</div>
                        <div class="header-city">Provincial Capitol, Malaybalay City</div>
                    </td>
                    <td class="logo-right">
                        @if(file_exists(public_path('img/bagong-pilipinas.png')))
                            <img src="{{ public_path('img/bagong-pilipinas.png') }}" alt="Bagong Pilipinas">
                        @endif
                    </td>
                </tr>
            </table>

            <div class="header-divider"></div>
            <div class="header-office">PROVINCIAL HUMAN RESOURCE MANAGEMENT OFFICE</div>
            <div style="height:6px;"></div>

            {{-- Certificate title --}}
            <div class="cert-title">LOYALTY INCENTIVE</div>
            <div style="height:4px;"></div>
            <div class="cert-subtitle">IS AWARDED TO</div>

            {{-- Laurel wreath -- }}
            <div class="laurel">&#10023; &#10023; &#10023;</div>

            {{-- Employee name --}}
            <div class="emp-name">
                {{ $employee->first_name }}
                @if($employee->middle_name) {{ substr($employee->middle_name, 0, 1) . '.' }} @endif
                {{ $employee->last_name }}
            </div>
            <div class="emp-position">{{ $employee->position_title }}</div>
            <div class="emp-office">{{ $employee->office_department }}</div>

            {{-- Body paragraph --}}
            <div class="cert-body">
                in grateful appreciation for your
                <span class="underline-fill">{{ $yearsOfService }}</span>
                years of continuous and satisfactory service to the
                Provincial Government of Bukidnon (PGB) from
                <span class="underline-fill">{{ $fromDate->format('F d, Y') }}</span>
                to
                <span class="underline-fill">{{ $toDate->format('F d, Y') }}</span>,
                with corresponding loyalty incentive in the amount of
                @if($amount > 0)
                    <span class="underline-fill">{{ $amountWords }}</span>
                @else
                    <span class="underline-fill">Not yet eligible</span>
                @endif
            </div>

            <div class="ref-line">as per CSC Memorandum Circular No. 6, s. 2002.</div>

            {{-- Signatory --}}
            <div class="signatory">
                <div class="sig-name">AIDA B. LOVERES</div>
                <div class="sig-title">P.G. Department Head</div>
                <div class="sig-title">PHRMO</div>
            </div>

        </div>{{-- /inner --}}

        <div class="page-label">***Personal File Copy</div>

    </div>{{-- /page 1 --}}


    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- PAGE 2 — PAYROLL / VOUCHER ATTACHMENT --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="page {{ !empty($backgroundPath) && file_exists($backgroundPath) ? 'has-bg' : '' }}">

        {{-- Background image (if set) --}}
        @if(!empty($backgroundPath) && file_exists($backgroundPath))
            <img src="{{ $backgroundPath }}" class="page-bg" alt="">
        @endif

        {{-- Corner / strip decorations --}}
        <div class="corner-tl"></div>
        <div class="strip-tr"></div>
        <div class="strip-bl"></div>
        <div class="corner-br"></div>

        <div class="inner">

            {{-- Header --}}
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        <img src="{{ public_path('img/phrmologo.png') }}" alt="PHRMO Logo">
                        &nbsp;
                        <img src="{{ public_path('img/logo.png') }}" alt="Province Seal">
                    </td>
                    <td class="header-center">
                        <div class="header-republic">Republic of the Philippines</div>
                        <div class="header-province">PROVINCE OF BUKIDNON</div>
                        <div class="header-city">Provincial Capitol, Malaybalay City</div>
                    </td>
                    <td class="logo-right">
                        @if(file_exists(public_path('img/bagong-pilipinas.png')))
                            <img src="{{ public_path('img/bagong-pilipinas.png') }}" alt="Bagong Pilipinas">
                        @endif
                    </td>
                </tr>
            </table>

            <div class="header-divider"></div>
            <div class="header-office">PROVINCIAL HUMAN RESOURCE MANAGEMENT OFFICE</div>
            <div style="height:6px;"></div>

            {{-- Certificate title --}}
            <div class="cert-title">LOYALTY INCENTIVE</div>


            {{-- Benefit summary table --}}
            <table class="benefit-table">
                <thead>
                    <tr>
                        <th>Name of Employee</th>
                        <th>1st Day of<br>Government Service</th>
                        <th>No. of Years</th>
                        <th>Amount of Benefit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            {{ $employee->first_name }}
                            @if($employee->middle_name) {{ substr($employee->middle_name, 0, 1) . '.' }} @endif
                            {{ $employee->last_name }}
                        </td>
                        <td>{{ $startDate->format('F d, Y') }}</td>
                        <td>{{ $yearsOfService }}</td>
                        <td>
                            @if($amount > 0)
                                {{ number_format($amount, 2) }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Footer paragraph --}}
            <div class="cert-footer-text">
                This certificate is issued based on CSC Memorandum Circular No. 6, s. 2002.<br>
                Given this month of
                <span class="underline-fill">{{ strtoupper($generatedMonth) }}</span>
                {{ $generatedYear }} at Malaybalay City, Bukidnon.
            </div>

            {{-- Signatory --}}
            <div class="signatory">
                <div class="sig-name">AIDA B. LOVERES</div>
                <div class="sig-title">P.G. Department Head</div>
                <div class="sig-title">PHRMO</div>
            </div>

        </div>{{-- /inner --}}

        <div class="page-label">***For payroll/voucher attachment</div>

    </div>{{-- /page 2 --}}

</body>

</html>