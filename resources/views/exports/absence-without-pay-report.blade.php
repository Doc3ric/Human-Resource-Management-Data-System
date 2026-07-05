<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: "Times New Roman", Times, serif; font-size: 10pt; color: #000; line-height: 1.5; }

    .page { padding: 20mm; position: relative; }
    
    /* Decorative double border around the content area */
    .border-frame {
        border: 2px solid #000;
        padding: 5px;
    }
    .inner-frame {
        border: 1px solid #000;
        padding: 15mm 15mm;
    }

    
    .report-title {
        text-align: center; font-size: 14pt; font-weight: bold;
        text-transform: uppercase; margin-top: 10px; margin-bottom: 25px;
        letter-spacing: 1px; text-decoration: underline;
    }
    
    .intro-text { font-size: 10pt; margin-bottom: 20px; text-align: justify; }

    /* Main table */
    table.main-table {
        width: 100%; border-collapse: collapse; font-size: 9pt;
        margin-bottom: 40px; border: 1.5px solid #000;
    }
    table.main-table th, table.main-table td {
        border: 1px solid #000; padding: 6px 8px;
        vertical-align: middle; text-align: center;
    }
    /* Header rows */
    table.main-table th {
        font-weight: bold; font-size: 8.5pt; background-color: #f3f4f6;
        text-transform: uppercase;
    }
    
    table.main-table td.text-left { text-align: left; }

    /* Footer section */
    .footer-section {
        display: table; width: 100%; margin-top: 50px;
    }
    .footer-left  { display: table-cell; width: 50%; vertical-align: top; }
    .footer-right { display: table-cell; width: 50%; vertical-align: top; text-align: right; }

    .footer-label { font-size: 10pt; margin-bottom: 30px; font-style: italic; }
    .footer-sig-line {
        border-bottom: 1.5px solid #000; font-weight: bold;
        font-size: 10pt; padding-bottom: 4px; margin-bottom: 6px; display: inline-block; min-width: 250px;
        text-align: center; text-transform: uppercase;
    }
    .footer-pos {
        font-size: 9pt; text-align: center; display: block; width: 100%;
    }
</style>
</head>
<body>
<div class="page border-frame">
    <div class="inner-frame">
        @include('exports.partials.official-header')

        <div class="report-title">CERTIFICATE OF ABSENCE WITHOUT PAY</div>
        
        <div class="intro-text">
            <strong>TO THE PROVINCIAL ACCOUNTANT'S OFFICE:</strong><br><br>
            This is to formally certify and report the 
            @if(count($leaveTypes) > 0)
                <strong>{{ implode(' / ', $leaveTypes) }}</strong>
            @else
                <strong>Leave of Absence / Undertime / Tardy / Unauthorized Absence</strong>
            @endif
            without pay for the month of <strong>{{ date('F Y') }}</strong> for the following personnel:
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th rowspan="2">NAME OF PERSONNEL</th>
                    <th rowspan="2">POSITION</th>
                    <th rowspan="2">OFFICE</th>
                    <th colspan="2">EARNED LEAVE CREDITS</th>
                    <th colspan="4">DAYS W/OUT PAY</th>
                    <th colspan="3">UNDERTIME/TARDY W/OUT PAY</th>
                </tr>
                <tr>
                    <th>VL</th>
                    <th>SL</th>
                    <th>VL</th>
                    <th>SL</th>
                    <th>Total</th>
                    <th>Dates</th>
                    <th>HRS</th>
                    <th>MINS</th>
                    <th>Dates</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td class="text-left" style="font-weight:bold;">
                            {{ strtoupper($entry->plantillaRecord->last_name ?? '') }}, {{ strtoupper($entry->plantillaRecord->first_name ?? '') }}
                        </td>
                        <td class="text-left">{{ $entry->plantillaRecord->position_title ?? '—' }}</td>
                        <td class="text-left">{{ $entry->plantillaRecord->office_department ?? '—' }}</td>
                        
                        <!-- Leave Credits -->
                        <td>{{ isset($entry->details['earned_vl']) ? number_format($entry->details['earned_vl'], 3) : '—' }}</td>
                        <td>{{ isset($entry->details['earned_sl']) ? number_format($entry->details['earned_sl'], 3) : '—' }}</td>
                        
                        <!-- Days Without Pay -->
                        <td>{{ $entry->details['days_without_pay_vl'] ?? '—' }}</td>
                        <td>{{ $entry->details['days_without_pay_sl'] ?? '—' }}</td>
                        <td style="font-weight:bold;">{{ $entry->details['days_without_pay_total'] ?? '—' }}</td>
                        <td style="font-size:8pt;">{{ $entry->details['days_without_pay_inclusive_dates'] ?? '—' }}</td>
                        
                        <!-- Undertime / Tardy -->
                        <td>{{ $entry->details['undertime_tardy_hours'] ?? '—' }}</td>
                        <td>{{ $entry->details['undertime_tardy_mins'] ?? '—' }}</td>
                        <td style="font-size:8pt;">{{ $entry->details['undertime_tardy_inclusive_dates'] ?? '—' }}</td>
                    </tr>
                @endforeach
                @if($entries->isEmpty())
                    <tr>
                        <td colspan="12" style="padding: 20px;">No records found.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="footer-section">
            <div class="footer-left">
                <div class="footer-label">Prepared by:</div>
                @php
                    $preparedByName = 'HR MANAGEMENT ASSISTANT';
                    $preparedByPosition = 'HR Management Assistant';
                    $authUser = auth()->user();
                    if ($authUser) {
                        $plantilla = \App\Models\PlantillaRecord::whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$authUser->name])
                            ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) = ?", [$authUser->name])
                            ->first();
                        if ($plantilla) {
                            $preparedByName = strtoupper($plantilla->first_name . ' ' . ($plantilla->middle_name ? $plantilla->middle_name . ' ' : '') . $plantilla->last_name);
                            $preparedByPosition = $plantilla->position_title;
                        } else {
                            $preparedByName = strtoupper($authUser->name);
                            $preparedByPosition = 'HR Management Assistant';
                        }
                    }
                @endphp
                <div style="display: inline-block;">
                    <div class="footer-sig-line">
                        {{ $preparedByName }}
                    </div>
                    <div class="footer-pos">
                        {{ $preparedByPosition }}
                    </div>
                </div>
            </div>
            <div class="footer-right">
                <div class="footer-label" style="text-align: left; display: inline-block; width: 250px;">Noted by:</div><br>
                <div style="display: inline-block;">
                    <div class="footer-sig-line">
                        {{ $notedByName ?: 'JUAN DELA CRUZ' }}
                    </div>
                    <div class="footer-pos">
                        {{ $notedByPosition ?: 'PG Department Head / PHRM Officer' }}
                    </div>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px; font-size: 8pt; color: #666;">
            This is a system-generated official record.
        </div>
    </div>
</div>
</body>
</html>
