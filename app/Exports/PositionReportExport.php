<?php

namespace App\Exports;

use App\Models\PlantillaRecord;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PositionReportExport implements FromArray, ShouldAutoSize, WithTitle, WithEvents, WithDrawings, WithCustomStartCell
{
    use \App\Exports\Traits\HasPhrmoHeader;

    protected array $positions;

    public function __construct(array $positions)
    {
        $this->positions = $positions;
    }

    /** Returns only the raw data rows — NO counter column, NO headers.
     *  Columns: A=Name, B=Position, C=Scope, D=Permanent, E=Contractual, F=Date, G=Remarks
     */
    public function array(): array
    {
        $records = PlantillaRecord::whereIn('position_title', $this->positions)
            ->where('is_vacant', false)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $rows = [];
        foreach ($records as $r) {
            $isPermanent = in_array(
                strtolower(trim($r->employment_status ?? '')),
                ['p', 'permanent']
            );
            $mi = $r->middle_name
                ? strtoupper(substr($r->middle_name, 0, 1)) . '.'
                : '';
            $fullName = trim(
                strtoupper($r->last_name ?? '')
                . ', '
                . ($r->first_name ?? '')
                . ($mi ? ' ' . $mi : '')
            );

            $rows[] = [
                $fullName,                // A – Name of Personnel
                $r->position_title,       // B – Designation / Position
                '',                       // C – Scope of Work / Tasks
                $isPermanent ? '✓' : '',  // D – Permanent (4.1)
                '',                       // E – Contractual (4.2)
                ($r->date_original_appointment || $r->date_last_promotion)
                ? Carbon::parse($r->date_original_appointment ?? $r->date_last_promotion)->format('m/d/Y')
                : '',                 // F – Date of Appointment (4.3) — falls back to date_last_promotion
                '',                       // G – Remarks
            ];
        }

        // Always show at least 5 rows (blank padding)
        $count = count($rows);
        for ($i = $count; $i < 5; $i++) {
            $rows[] = ['', '', '', '', '', '', ''];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Position Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Count data rows that were written (at least 5)
                $dataCount = max(
                    5,
                    PlantillaRecord::whereIn('position_title', $this->positions)
                        ->where('is_vacant', false)
                        ->count()
                );

                // Insert 7 blank rows at the top for the report header
                // After insert: rows 1-7 are blank, data starts at row 8
                $sheet->insertNewRowBefore(1, 7);

                /* ── ROW 1: Form reference ─────────────────────────────── */
                $sheet->setCellValue('A1', 'MFMP 001-2023');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(10);

                /* ── ROW 3: Report title ───────────────────────────────── */
                if (count($this->positions) === 1) {
                    $posUpper = strtoupper($this->positions[0]);
                    $words = explode(' ', $posUpper);
                    $lastWord = end($words);
                    $suffix = str_ends_with($lastWord, 'S') ? '' : 'S';
                    $titleText = 'REPORT ON THE STATUS ON '
                        . implode(' ', $words)
                        . $suffix
                        . ' PERSONNEL';
                } else {
                    $titleText = 'REPORT ON THE STATUS ON VARIOUS POSITIONS PERSONNEL';
                }

                $sheet->mergeCells('A3:G3');
                $sheet->setCellValue('A3', $titleText);
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(22);

                /* ── ROW 4: Province ───────────────────────────────────── */
                $sheet->mergeCells('A4:G4');
                $sheet->setCellValue('A4', 'Province: Bukidnon');
                $sheet->getStyle('A4')->getFont()->setSize(11);

                /* ── ROW 6: Main column headers ────────────────────────── */
                // Merged header groups — row 6 spans Status of Appointment (D-F)
                $sheet->mergeCells('D6:F6');
                $sheet->setCellValue('A6', "Name of Personnel\n(01)");
                $sheet->setCellValue('B6', "Designation/Position\n(02)");
                $sheet->setCellValue('C6', "Scope of Work/Tasks and Responsibilities\n(03)");
                $sheet->setCellValue('D6', "Status of Appointment\n(04)");
                $sheet->setCellValue('G6', "Remarks\n(5)");

                /* ── ROW 7: Sub-headers ────────────────────────────────── */
                $sheet->setCellValue('D7', "Permanent\n(4.1)");
                $sheet->setCellValue('E7', "Contractual\n(4.2)");
                $sheet->setCellValue('F7', "Date of\nAppointment\n(4.3)");

                // Rows 6 & 7: A, B, C, G span both rows
                $sheet->mergeCells('A6:A7');
                $sheet->mergeCells('B6:B7');
                $sheet->mergeCells('C6:C7');
                $sheet->mergeCells('G6:G7');

                // Style for both header rows — NO background fill, just borders
                $noFillBorderCenter = [
                    'font' => ['bold' => true, 'size' => 9],
                    'fill' => [
                        'fillType' => Fill::FILL_NONE,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ];
                $sheet->getStyle('A6:G7')->applyFromArray($noFillBorderCenter);
                $sheet->getRowDimension(6)->setRowHeight(30);
                $sheet->getRowDimension(7)->setRowHeight(30);

                /* ── Data rows start at row 8 ──────────────────────────── */
                $firstDataRow = 8;
                $lastDataRow = $firstDataRow + $dataCount - 1;

                $sheet->getStyle("A{$firstDataRow}:G{$lastDataRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_NONE],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                // Name column (A): left-aligned
                $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Position column (B): left-aligned
                $sheet->getStyle("B{$firstDataRow}:B{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Permanent / Contractual / Date (D–F): centered
                $sheet->getStyle("D{$firstDataRow}:F{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Force all data cells to have no fill/color
                $sheet->getStyle("A{$firstDataRow}:G{$lastDataRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_NONE);

                /* ── Footer ─────────────────────────────────────────────── */
                $footerLabelRow = $lastDataRow + 2;
                $sheet->setCellValue("A{$footerLabelRow}", 'Prepared by:');
                $sheet->setCellValue("E{$footerLabelRow}", 'Attested by:');

                $sigRow = $footerLabelRow + 2;
                $sheet->mergeCells("A{$sigRow}:C{$sigRow}");
                $sheet->mergeCells("E{$sigRow}:G{$sigRow}");
                $sheet->setCellValue("A{$sigRow}", "LGU Human Resource Dev't. Officer/Personnel Officer");
                $sheet->setCellValue("E{$sigRow}", 'Governor, Bukidnon Province');
                $sheet->getStyle("A{$sigRow}:G{$sigRow}")->getFont()->setBold(true);
                // Add underline to signature cells
                $sheet->getStyle("A{$sigRow}:C{$sigRow}")
                    ->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("E{$sigRow}:G{$sigRow}")
                    ->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);

                $dateRow = $sigRow + 1;
                $sheet->setCellValue("A{$dateRow}", 'Date: ___________________________');
                $sheet->setCellValue("E{$dateRow}", 'Date: ___________________________');

                /* ── Instructions Box ──────────────────────────────────── */
                $instrStartRow = $dateRow + 3;

                // Outer box border
                $sheet->getStyle("A{$instrStartRow}:G" . ($instrStartRow + 6))->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Box title
                $sheet->mergeCells("A{$instrStartRow}:G{$instrStartRow}");
                $sheet->setCellValue("A{$instrStartRow}", 'Instructions in Filling-up the Form');
                $sheet->getStyle("A{$instrStartRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension($instrStartRow)->setRowHeight(20);

                // Instruction body content - Row 1
                $row = $instrStartRow + 1;
                $sheet->setCellValue("A{$row}", 'Column 1:');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->mergeCells("B{$row}:G{$row}");
                $sheet->setCellValue("B{$row}", 'Indicate the name of personnel hired by the LGU');

                // Row 2
                $row++;
                $sheet->setCellValue("A{$row}", 'Column 02:');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->mergeCells("B{$row}:G{$row}");
                $sheet->setCellValue("B{$row}", 'State the official designation as stipulated the contract of service (COS), or the position stated in the plantilla of personnel, if permanent');
                $sheet->getRowDimension($row)->setRowHeight(30);

                // Row 3
                $row++;
                $sheet->setCellValue("A{$row}", 'Column 03:');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->mergeCells("B{$row}:G{$row}");
                $sheet->setCellValue("B{$row}", 'Indicate the TOR if COS/duties and responsibilities if permanent');

                // Row 4
                $row++;
                $sheet->setCellValue("A{$row}", 'Column 04:');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->mergeCells("B{$row}:G{$row}");
                $sheet->setCellValue("B{$row}", 'Put a check on the status of appointment whether COS in (4.1) or for Permanent (4.2) and the date of appointment');
                $sheet->getRowDimension($row)->setRowHeight(30);

                // Row 5
                $row++;
                $sheet->setCellValue("A{$row}", 'Column 05:');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->mergeCells("B{$row}:G{$row}");
                $sheet->setCellValue("B{$row}", 'Indicate other relevant information as maybe important which are not captured in the columns provided in this form.');
                $sheet->getRowDimension($row)->setRowHeight(30);

                // Align all instruction text to top-left and wrap text
                $sheet->getStyle("B" . ($instrStartRow + 1) . ":G{$row}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ]
                ]);
                $sheet->getStyle("A" . ($instrStartRow + 1) . ":A{$row}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_TOP,
                    ]
                ]);

                /* ── Column widths ─────────────────────────────────────── */
                $sheet->getColumnDimension('A')->setWidth(28);  // Name
                $sheet->getColumnDimension('B')->setWidth(20);  // Position
                $sheet->getColumnDimension('C')->setWidth(34);  // Scope
                $sheet->getColumnDimension('D')->setWidth(12);  // Permanent
                $sheet->getColumnDimension('E')->setWidth(12);  // Contractual
                $sheet->getColumnDimension('F')->setWidth(16);  // Date
                $sheet->getColumnDimension('G')->setWidth(14);  // Remarks
            },
        ];
    }
}
